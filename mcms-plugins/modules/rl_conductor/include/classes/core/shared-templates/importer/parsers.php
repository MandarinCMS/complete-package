<?php
if ( ! defined( 'BASED_TREE_URI' ) ) {
	die( '-1' );
}

/**
 * MandarinCMS eXtended RSS file parser implementations
 *
 * @package MandarinCMS
 * @subpackage Importer
 */

/**
 * MandarinCMS Importer class for managing parsing of WXR files.
 */
class Vc_WXR_Parser {
	function parse( $file ) {
		// Attempt to use proper XML parsers first
		if ( extension_loaded( 'simplexml' ) ) {
			$parser = new Vc_WXR_Parser_SimpleXML;
			$result = $parser->parse( $file );

			// If SimpleXML succeeds or this is an invalid WXR file then return the results
			if ( ! is_mcms_error( $result ) || 'SimpleXML_parse_error' != $result->get_error_code() ) {
				return $result;
			}
		} else if ( extension_loaded( 'xml' ) ) {
			$parser = new Vc_WXR_Parser_XML;
			$result = $parser->parse( $file );

			// If XMLParser succeeds or this is an invalid WXR file then return the results
			if ( ! is_mcms_error( $result ) || 'XML_parse_error' != $result->get_error_code() ) {
				return $result;
			}
		}

		// We have a malformed XML file, so display the error and fallthrough to regex
		if ( isset( $result ) && defined( 'IMPORT_DEBUG' ) && IMPORT_DEBUG ) {
			echo '<pre>';
			if ( 'SimpleXML_parse_error' == $result->get_error_code() ) {
				foreach ( $result->get_error_data() as $error ) {
					echo $error->line . ':' . $error->column . ' ' . esc_html( $error->message ) . "\n";
				}
			} else if ( 'XML_parse_error' == $result->get_error_code() ) {
				$error = $result->get_error_data();
				echo $error[0] . ':' . $error[1] . ' ' . esc_html( $error[2] );
			}
			echo '</pre>';
			echo '<p><strong>' . __( 'There was an error when reading this WXR file', 'mandarincms-importer' ) . '</strong><br />';
			echo __( 'Details are shown above. The importer will now try again with a different parser...', 'mandarincms-importer' ) . '</p>';
		}

		// use regular expressions if nothing else available or this is bad XML
		$parser = new Vc_WXR_Parser_Regex;

		return $parser->parse( $file );
	}
}

/**
 * WXR Parser that makes use of the SimpleXML PHP extension.
 */
class Vc_WXR_Parser_SimpleXML {
	function parse( $file ) {
		$authors = $posts = $categories = $tags = $terms = array();

		$internal_errors = libxml_use_internal_errors( true );

		$dom = new DOMDocument;
		$old_value = null;
		if ( function_exists( 'libxml_disable_entity_loader' ) ) {
			$old_value = libxml_disable_entity_loader( true );
		}
		$success = $dom->loadXML( file_get_contents( $file ) );
		if ( ! is_null( $old_value ) ) {
			libxml_disable_entity_loader( $old_value );
		}

		if ( ! $success || isset( $dom->doctype ) ) {
			return new MCMS_Error( 'SimpleXML_parse_error', __( 'There was an error when reading this WXR file', 'mandarincms-importer' ), libxml_get_errors() );
		}

		$xml = simplexml_import_dom( $dom );
		unset( $dom );

		// halt if loading produces an error
		if ( ! $xml ) {
			return new MCMS_Error( 'SimpleXML_parse_error', __( 'There was an error when reading this WXR file', 'mandarincms-importer' ), libxml_get_errors() );
		}

		$wxr_version = $xml->xpath( '/rss/channel/mcms:wxr_version' );
		if ( ! $wxr_version ) {
			return new MCMS_Error( 'WXR_parse_error', __( 'This does not appear to be a WXR file, missing/invalid WXR version number', 'mandarincms-importer' ) );
		}

		$wxr_version = (string) trim( $wxr_version[0] );
		// confirm that we are dealing with the correct file format
		if ( ! preg_match( '/^\d+\.\d+$/', $wxr_version ) ) {
			return new MCMS_Error( 'WXR_parse_error', __( 'This does not appear to be a WXR file, missing/invalid WXR version number', 'mandarincms-importer' ) );
		}

		$base_url = $xml->xpath( '/rss/channel/mcms:base_site_url' );
		$base_url = (string) trim( $base_url[0] );

		$namespaces = $xml->getDocNamespaces();
		if ( ! isset( $namespaces['mcms'] ) ) {
			$namespaces['mcms'] = 'http://mandarincms.com/export/1.1/';
		}
		if ( ! isset( $namespaces['excerpt'] ) ) {
			$namespaces['excerpt'] = 'http://mandarincms.com/export/1.1/excerpt/';
		}

		// grab authors
		foreach ( $xml->xpath( '/rss/channel/mcms:author' ) as $author_arr ) {
			$a = $author_arr->children( $namespaces['mcms'] );
			$login = (string) $a->author_login;
			$authors[ $login ] = array(
				'author_id' => (int) $a->author_id,
				'author_login' => $login,
				'author_email' => (string) $a->author_email,
				'author_display_name' => (string) $a->author_display_name,
				'author_first_name' => (string) $a->author_first_name,
				'author_last_name' => (string) $a->author_last_name,
			);
		}

		// grab cats, tags and terms
		foreach ( $xml->xpath( '/rss/channel/mcms:category' ) as $term_arr ) {
			$t = $term_arr->children( $namespaces['mcms'] );
			$category = array(
				'term_id' => (int) $t->term_id,
				'category_nicename' => (string) $t->category_nicename,
				'category_parent' => (string) $t->category_parent,
				'cat_name' => (string) $t->cat_name,
				'category_description' => (string) $t->category_description,
			);

			foreach ( $t->termmeta as $meta ) {
				$category['termmeta'][] = array(
					'key' => (string) $meta->meta_key,
					'value' => (string) $meta->meta_value,
				);
			}

			$categories[] = $category;
		}

		foreach ( $xml->xpath( '/rss/channel/mcms:tag' ) as $term_arr ) {
			$t = $term_arr->children( $namespaces['mcms'] );
			$tag = array(
				'term_id' => (int) $t->term_id,
				'tag_slug' => (string) $t->tag_slug,
				'tag_name' => (string) $t->tag_name,
				'tag_description' => (string) $t->tag_description,
			);

			foreach ( $t->termmeta as $meta ) {
				$tag['termmeta'][] = array(
					'key' => (string) $meta->meta_key,
					'value' => (string) $meta->meta_value,
				);
			}

			$tags[] = $tag;
		}

		foreach ( $xml->xpath( '/rss/channel/mcms:term' ) as $term_arr ) {
			$t = $term_arr->children( $namespaces['mcms'] );
			$term = array(
				'term_id' => (int) $t->term_id,
				'term_taxonomy' => (string) $t->term_taxonomy,
				'slug' => (string) $t->term_slug,
				'term_parent' => (string) $t->term_parent,
				'term_name' => (string) $t->term_name,
				'term_description' => (string) $t->term_description,
			);

			foreach ( $t->termmeta as $meta ) {
				$term['termmeta'][] = array(
					'key' => (string) $meta->meta_key,
					'value' => (string) $meta->meta_value,
				);
			}

			$terms[] = $term;
		}

		// grab posts
		foreach ( $xml->channel->item as $item ) {
			$post = array(
				'post_title' => (string) $item->title,
				'guid' => (string) $item->guid,
			);

			$dc = $item->children( 'http://purl.org/dc/elements/1.1/' );
			$post['post_author'] = (string) $dc->creator;

			$content = $item->children( 'http://purl.org/rss/1.0/modules/content/' );
			$excerpt = $item->children( $namespaces['excerpt'] );
			$post['post_content'] = (string) $content->encoded;
			$post['post_excerpt'] = (string) $excerpt->encoded;

			$mcms = $item->children( $namespaces['mcms'] );
			$post['post_id'] = (int) $mcms->post_id;
			$post['post_date'] = (string) $mcms->post_date;
			$post['post_date_gmt'] = (string) $mcms->post_date_gmt;
			$post['comment_status'] = (string) $mcms->comment_status;
			$post['ping_status'] = (string) $mcms->ping_status;
			$post['post_name'] = (string) $mcms->post_name;
			$post['status'] = (string) $mcms->status;
			$post['post_parent'] = (int) $mcms->post_parent;
			$post['menu_order'] = (int) $mcms->menu_order;
			$post['post_type'] = (string) $mcms->post_type;
			$post['post_password'] = (string) $mcms->post_password;
			$post['is_sticky'] = (int) $mcms->is_sticky;

			if ( isset( $mcms->attachment_url ) ) {
				$post['attachment_url'] = (string) $mcms->attachment_url;
			}

			foreach ( $item->category as $c ) {
				$att = $c->attributes();
				if ( isset( $att['nicename'] ) ) {
					$post['terms'][] = array(
						'name' => (string) $c,
						'slug' => (string) $att['nicename'],
						'domain' => (string) $att['domain'],
					);
				}
			}

			foreach ( $mcms->postmeta as $meta ) {
				$post['postmeta'][] = array(
					'key' => (string) $meta->meta_key,
					'value' => (string) $meta->meta_value,
				);
			}

			foreach ( $mcms->comment as $comment ) {
				$meta = array();
				if ( isset( $comment->commentmeta ) ) {
					foreach ( $comment->commentmeta as $m ) {
						$meta[] = array(
							'key' => (string) $m->meta_key,
							'value' => (string) $m->meta_value,
						);
					}
				}

				$post['comments'][] = array(
					'comment_id' => (int) $comment->comment_id,
					'comment_author' => (string) $comment->comment_author,
					'comment_author_email' => (string) $comment->comment_author_email,
					'comment_author_IP' => (string) $comment->comment_author_IP,
					'comment_author_url' => (string) $comment->comment_author_url,
					'comment_date' => (string) $comment->comment_date,
					'comment_date_gmt' => (string) $comment->comment_date_gmt,
					'comment_content' => (string) $comment->comment_content,
					'comment_approved' => (string) $comment->comment_approved,
					'comment_type' => (string) $comment->comment_type,
					'comment_parent' => (string) $comment->comment_parent,
					'comment_user_id' => (int) $comment->comment_user_id,
					'commentmeta' => $meta,
				);
			}

			$posts[] = $post;
		}

		return array(
			'authors' => $authors,
			'posts' => $posts,
			'categories' => $categories,
			'tags' => $tags,
			'terms' => $terms,
			'base_url' => $base_url,
			'version' => $wxr_version,
		);
	}
}

/**
 * WXR Parser that makes use of the XML Parser PHP extension.
 */
class Vc_WXR_Parser_XML {
	var $mcms_tags = array(
		'mcms:post_id',
		'mcms:post_date',
		'mcms:post_date_gmt',
		'mcms:comment_status',
		'mcms:ping_status',
		'mcms:attachment_url',
		'mcms:status',
		'mcms:post_name',
		'mcms:post_parent',
		'mcms:menu_order',
		'mcms:post_type',
		'mcms:post_password',
		'mcms:is_sticky',
		'mcms:term_id',
		'mcms:category_nicename',
		'mcms:category_parent',
		'mcms:cat_name',
		'mcms:category_description',
		'mcms:tag_slug',
		'mcms:tag_name',
		'mcms:tag_description',
		'mcms:term_taxonomy',
		'mcms:term_parent',
		'mcms:term_name',
		'mcms:term_description',
		'mcms:author_id',
		'mcms:author_login',
		'mcms:author_email',
		'mcms:author_display_name',
		'mcms:author_first_name',
		'mcms:author_last_name',
	);
	var $mcms_sub_tags = array(
		'mcms:comment_id',
		'mcms:comment_author',
		'mcms:comment_author_email',
		'mcms:comment_author_url',
		'mcms:comment_author_IP',
		'mcms:comment_date',
		'mcms:comment_date_gmt',
		'mcms:comment_content',
		'mcms:comment_approved',
		'mcms:comment_type',
		'mcms:comment_parent',
		'mcms:comment_user_id',
	);

	function parse( $file ) {
		$this->wxr_version = $this->in_post = $this->cdata = $this->data = $this->sub_data = $this->in_tag = $this->in_sub_tag = false;
		$this->authors = $this->posts = $this->term = $this->category = $this->tag = array();

		$xml = xml_parser_create( 'UTF-8' );
		xml_parser_set_option( $xml, XML_OPTION_SKIP_WHITE, 1 );
		xml_parser_set_option( $xml, XML_OPTION_CASE_FOLDING, 0 );
		xml_set_object( $xml, $this );
		xml_set_character_data_handler( $xml, 'cdata' );
		xml_set_element_handler( $xml, 'tag_open', 'tag_close' );

		if ( ! xml_parse( $xml, file_get_contents( $file ), true ) ) {
			$current_line = xml_get_current_line_number( $xml );
			$current_column = xml_get_current_column_number( $xml );
			$error_code = xml_get_error_code( $xml );
			$error_string = xml_error_string( $error_code );

			return new MCMS_Error( 'XML_parse_error', 'There was an error when reading this WXR file', array(
				$current_line,
				$current_column,
				$error_string,
			) );
		}
		xml_parser_free( $xml );

		if ( ! preg_match( '/^\d+\.\d+$/', $this->wxr_version ) ) {
			return new MCMS_Error( 'WXR_parse_error', __( 'This does not appear to be a WXR file, missing/invalid WXR version number', 'mandarincms-importer' ) );
		}

		return array(
			'authors' => $this->authors,
			'posts' => $this->posts,
			'categories' => $this->category,
			'tags' => $this->tag,
			'terms' => $this->term,
			'base_url' => $this->base_url,
			'version' => $this->wxr_version,
		);
	}

	function tag_open( $parse, $tag, $attr ) {
		if ( in_array( $tag, $this->mcms_tags ) ) {
			$this->in_tag = substr( $tag, 3 );

			return;
		}

		if ( in_array( $tag, $this->mcms_sub_tags ) ) {
			$this->in_sub_tag = substr( $tag, 3 );

			return;
		}

		switch ( $tag ) {
			case 'category':
				if ( isset( $attr['domain'], $attr['nicename'] ) ) {
					$this->sub_data['domain'] = $attr['domain'];
					$this->sub_data['slug'] = $attr['nicename'];
				}
				break;
			case 'item':
				$this->in_post = true;
			case 'title':
				if ( $this->in_post ) {
					$this->in_tag = 'post_title';
				}
				break;
			case 'guid':
				$this->in_tag = 'guid';
				break;
			case 'dc:creator':
				$this->in_tag = 'post_author';
				break;
			case 'content:encoded':
				$this->in_tag = 'post_content';
				break;
			case 'excerpt:encoded':
				$this->in_tag = 'post_excerpt';
				break;

			case 'mcms:term_slug':
				$this->in_tag = 'slug';
				break;
			case 'mcms:meta_key':
				$this->in_sub_tag = 'key';
				break;
			case 'mcms:meta_value':
				$this->in_sub_tag = 'value';
				break;
		}
	}

	function cdata( $parser, $cdata ) {
		if ( ! trim( $cdata ) ) {
			return;
		}

		if ( false !== $this->in_tag || false !== $this->in_sub_tag ) {
			$this->cdata .= $cdata;
		} else {
			$this->cdata .= trim( $cdata );
		}
	}

	function tag_close( $parser, $tag ) {
		switch ( $tag ) {
			case 'mcms:comment':
				unset( $this->sub_data['key'], $this->sub_data['value'] ); // remove meta sub_data
				if ( ! empty( $this->sub_data ) ) {
					$this->data['comments'][] = $this->sub_data;
				}
				$this->sub_data = false;
				break;
			case 'mcms:commentmeta':
				$this->sub_data['commentmeta'][] = array(
					'key' => $this->sub_data['key'],
					'value' => $this->sub_data['value'],
				);
				break;
			case 'category':
				if ( ! empty( $this->sub_data ) ) {
					$this->sub_data['name'] = $this->cdata;
					$this->data['terms'][] = $this->sub_data;
				}
				$this->sub_data = false;
				break;
			case 'mcms:postmeta':
				if ( ! empty( $this->sub_data ) ) {
					$this->data['postmeta'][] = $this->sub_data;
				}
				$this->sub_data = false;
				break;
			case 'item':
				$this->posts[] = $this->data;
				$this->data = false;
				break;
			case 'mcms:category':
			case 'mcms:tag':
			case 'mcms:term':
				$n = substr( $tag, 3 );
				array_push( $this->$n, $this->data );
				$this->data = false;
				break;
			case 'mcms:author':
				if ( ! empty( $this->data['author_login'] ) ) {
					$this->authors[ $this->data['author_login'] ] = $this->data;
				}
				$this->data = false;
				break;
			case 'mcms:base_site_url':
				$this->base_url = $this->cdata;
				break;
			case 'mcms:wxr_version':
				$this->wxr_version = $this->cdata;
				break;

			default:
				if ( $this->in_sub_tag ) {
					$this->sub_data[ $this->in_sub_tag ] = ! empty( $this->cdata ) ? $this->cdata : '';
					$this->in_sub_tag = false;
				} else if ( $this->in_tag ) {
					$this->data[ $this->in_tag ] = ! empty( $this->cdata ) ? $this->cdata : '';
					$this->in_tag = false;
				}
		}

		$this->cdata = false;
	}
}

/**
 * WXR Parser that uses regular expressions. Fallback for installs without an XML parser.
 */
class Vc_WXR_Parser_Regex {
	var $authors = array();
	var $posts = array();
	var $categories = array();
	var $tags = array();
	var $terms = array();
	var $base_url = '';

	function __construct() {
		$this->has_gzip = is_callable( 'gzopen' );
	}

	function parse( $file ) {
		$wxr_version = $in_post = false;

		$fp = $this->fopen( $file, 'r' );
		if ( $fp ) {
			while ( ! $this->feof( $fp ) ) {
				$importline = rtrim( $this->fgets( $fp ) );

				if ( ! $wxr_version && preg_match( '|<mcms:wxr_version>(\d+\.\d+)</mcms:wxr_version>|', $importline, $version ) ) {
					$wxr_version = $version[1];
				}

				if ( false !== strpos( $importline, '<mcms:base_site_url>' ) ) {
					preg_match( '|<mcms:base_site_url>(.*?)</mcms:base_site_url>|is', $importline, $url );
					$this->base_url = $url[1];
					continue;
				}
				if ( false !== strpos( $importline, '<mcms:category>' ) ) {
					preg_match( '|<mcms:category>(.*?)</mcms:category>|is', $importline, $category );
					$this->categories[] = $this->process_category( $category[1] );
					continue;
				}
				if ( false !== strpos( $importline, '<mcms:tag>' ) ) {
					preg_match( '|<mcms:tag>(.*?)</mcms:tag>|is', $importline, $tag );
					$this->tags[] = $this->process_tag( $tag[1] );
					continue;
				}
				if ( false !== strpos( $importline, '<mcms:term>' ) ) {
					preg_match( '|<mcms:term>(.*?)</mcms:term>|is', $importline, $term );
					$this->terms[] = $this->process_term( $term[1] );
					continue;
				}
				if ( false !== strpos( $importline, '<mcms:author>' ) ) {
					preg_match( '|<mcms:author>(.*?)</mcms:author>|is', $importline, $author );
					$a = $this->process_author( $author[1] );
					$this->authors[ $a['author_login'] ] = $a;
					continue;
				}
				if ( false !== strpos( $importline, '<item>' ) ) {
					$post = '';
					$in_post = true;
					continue;
				}
				if ( false !== strpos( $importline, '</item>' ) ) {
					$in_post = false;
					$this->posts[] = $this->process_post( $post );
					continue;
				}
				if ( $in_post ) {
					$post .= $importline . "\n";
				}
			}

			$this->fclose( $fp );
		}

		if ( ! $wxr_version ) {
			return new MCMS_Error( 'WXR_parse_error', __( 'This does not appear to be a WXR file, missing/invalid WXR version number', 'mandarincms-importer' ) );
		}

		return array(
			'authors' => $this->authors,
			'posts' => $this->posts,
			'categories' => $this->categories,
			'tags' => $this->tags,
			'terms' => $this->terms,
			'base_url' => $this->base_url,
			'version' => $wxr_version,
		);
	}

	function get_tag( $string, $tag ) {
		preg_match( "|<$tag.*?>(.*?)</$tag>|is", $string, $return );
		if ( isset( $return[1] ) ) {
			if ( substr( $return[1], 0, 9 ) == '<![CDATA[' ) {
				if ( strpos( $return[1], ']]]]><![CDATA[>' ) !== false ) {
					preg_match_all( '|<!\[CDATA\[(.*?)\]\]>|s', $return[1], $matches );
					$return = '';
					foreach ( $matches[1] as $match ) {
						$return .= $match;
					}
				} else {
					$return = preg_replace( '|^<!\[CDATA\[(.*)\]\]>$|s', '$1', $return[1] );
				}
			} else {
				$return = $return[1];
			}
		} else {
			$return = '';
		}

		return $return;
	}

	function process_category( $c ) {
		return array(
			'term_id' => $this->get_tag( $c, 'mcms:term_id' ),
			'cat_name' => $this->get_tag( $c, 'mcms:cat_name' ),
			'category_nicename' => $this->get_tag( $c, 'mcms:category_nicename' ),
			'category_parent' => $this->get_tag( $c, 'mcms:category_parent' ),
			'category_description' => $this->get_tag( $c, 'mcms:category_description' ),
		);
	}

	function process_tag( $t ) {
		return array(
			'term_id' => $this->get_tag( $t, 'mcms:term_id' ),
			'tag_name' => $this->get_tag( $t, 'mcms:tag_name' ),
			'tag_slug' => $this->get_tag( $t, 'mcms:tag_slug' ),
			'tag_description' => $this->get_tag( $t, 'mcms:tag_description' ),
		);
	}

	function process_term( $t ) {
		return array(
			'term_id' => $this->get_tag( $t, 'mcms:term_id' ),
			'term_taxonomy' => $this->get_tag( $t, 'mcms:term_taxonomy' ),
			'slug' => $this->get_tag( $t, 'mcms:term_slug' ),
			'term_parent' => $this->get_tag( $t, 'mcms:term_parent' ),
			'term_name' => $this->get_tag( $t, 'mcms:term_name' ),
			'term_description' => $this->get_tag( $t, 'mcms:term_description' ),
		);
	}

	function process_author( $a ) {
		return array(
			'author_id' => $this->get_tag( $a, 'mcms:author_id' ),
			'author_login' => $this->get_tag( $a, 'mcms:author_login' ),
			'author_email' => $this->get_tag( $a, 'mcms:author_email' ),
			'author_display_name' => $this->get_tag( $a, 'mcms:author_display_name' ),
			'author_first_name' => $this->get_tag( $a, 'mcms:author_first_name' ),
			'author_last_name' => $this->get_tag( $a, 'mcms:author_last_name' ),
		);
	}

	function process_post( $post ) {
		$post_id = $this->get_tag( $post, 'mcms:post_id' );
		$post_title = $this->get_tag( $post, 'title' );
		$post_date = $this->get_tag( $post, 'mcms:post_date' );
		$post_date_gmt = $this->get_tag( $post, 'mcms:post_date_gmt' );
		$comment_status = $this->get_tag( $post, 'mcms:comment_status' );
		$ping_status = $this->get_tag( $post, 'mcms:ping_status' );
		$status = $this->get_tag( $post, 'mcms:status' );
		$post_name = $this->get_tag( $post, 'mcms:post_name' );
		$post_parent = $this->get_tag( $post, 'mcms:post_parent' );
		$menu_order = $this->get_tag( $post, 'mcms:menu_order' );
		$post_type = $this->get_tag( $post, 'mcms:post_type' );
		$post_password = $this->get_tag( $post, 'mcms:post_password' );
		$is_sticky = $this->get_tag( $post, 'mcms:is_sticky' );
		$guid = $this->get_tag( $post, 'guid' );
		$post_author = $this->get_tag( $post, 'dc:creator' );

		$post_excerpt = $this->get_tag( $post, 'excerpt:encoded' );
		$post_excerpt = preg_replace_callback( '|<(/?[A-Z]+)|', array(
			&$this,
			'_normalize_tag',
		), $post_excerpt );
		$post_excerpt = str_replace( '<br>', '<br />', $post_excerpt );
		$post_excerpt = str_replace( '<hr>', '<hr />', $post_excerpt );

		$post_content = $this->get_tag( $post, 'content:encoded' );
		$post_content = preg_replace_callback( '|<(/?[A-Z]+)|', array(
			&$this,
			'_normalize_tag',
		), $post_content );
		$post_content = str_replace( '<br>', '<br />', $post_content );
		$post_content = str_replace( '<hr>', '<hr />', $post_content );

		$postdata = compact( 'post_id', 'post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_excerpt', 'post_title', 'status', 'post_name', 'comment_status', 'ping_status', 'guid', 'post_parent', 'menu_order', 'post_type', 'post_password', 'is_sticky' );

		$attachment_url = $this->get_tag( $post, 'mcms:attachment_url' );
		if ( $attachment_url ) {
			$postdata['attachment_url'] = $attachment_url;
		}

		preg_match_all( '|<category domain="([^"]+?)" nicename="([^"]+?)">(.+?)</category>|is', $post, $terms, PREG_SET_ORDER );
		foreach ( $terms as $t ) {
			$post_terms[] = array(
				'slug' => $t[2],
				'domain' => $t[1],
				'name' => str_replace( array(
					'<![CDATA[',
					']]>',
				), '', $t[3] ),
			);
		}
		if ( ! empty( $post_terms ) ) {
			$postdata['terms'] = $post_terms;
		}

		preg_match_all( '|<mcms:comment>(.+?)</mcms:comment>|is', $post, $comments );
		$comments = $comments[1];
		if ( $comments ) {
			foreach ( $comments as $comment ) {
				preg_match_all( '|<mcms:commentmeta>(.+?)</mcms:commentmeta>|is', $comment, $commentmeta );
				$commentmeta = $commentmeta[1];
				$c_meta = array();
				foreach ( $commentmeta as $m ) {
					$c_meta[] = array(
						'key' => $this->get_tag( $m, 'mcms:meta_key' ),
						'value' => $this->get_tag( $m, 'mcms:meta_value' ),
					);
				}

				$post_comments[] = array(
					'comment_id' => $this->get_tag( $comment, 'mcms:comment_id' ),
					'comment_author' => $this->get_tag( $comment, 'mcms:comment_author' ),
					'comment_author_email' => $this->get_tag( $comment, 'mcms:comment_author_email' ),
					'comment_author_IP' => $this->get_tag( $comment, 'mcms:comment_author_IP' ),
					'comment_author_url' => $this->get_tag( $comment, 'mcms:comment_author_url' ),
					'comment_date' => $this->get_tag( $comment, 'mcms:comment_date' ),
					'comment_date_gmt' => $this->get_tag( $comment, 'mcms:comment_date_gmt' ),
					'comment_content' => $this->get_tag( $comment, 'mcms:comment_content' ),
					'comment_approved' => $this->get_tag( $comment, 'mcms:comment_approved' ),
					'comment_type' => $this->get_tag( $comment, 'mcms:comment_type' ),
					'comment_parent' => $this->get_tag( $comment, 'mcms:comment_parent' ),
					'comment_user_id' => $this->get_tag( $comment, 'mcms:comment_user_id' ),
					'commentmeta' => $c_meta,
				);
			}
		}
		if ( ! empty( $post_comments ) ) {
			$postdata['comments'] = $post_comments;
		}

		preg_match_all( '|<mcms:postmeta>(.+?)</mcms:postmeta>|is', $post, $postmeta );
		$postmeta = $postmeta[1];
		if ( $postmeta ) {
			foreach ( $postmeta as $p ) {
				$post_postmeta[] = array(
					'key' => $this->get_tag( $p, 'mcms:meta_key' ),
					'value' => $this->get_tag( $p, 'mcms:meta_value' ),
				);
			}
		}
		if ( ! empty( $post_postmeta ) ) {
			$postdata['postmeta'] = $post_postmeta;
		}

		return $postdata;
	}

	function _normalize_tag( $matches ) {
		return '<' . strtolower( $matches[1] );
	}

	function fopen( $filename, $mode = 'r' ) {
		if ( $this->has_gzip ) {
			return gzopen( $filename, $mode );
		}

		return fopen( $filename, $mode );
	}

	function feof( $fp ) {
		if ( $this->has_gzip ) {
			return gzeof( $fp );
		}

		return feof( $fp );
	}

	function fgets( $fp, $len = 8192 ) {
		if ( $this->has_gzip ) {
			return gzgets( $fp, $len );
		}

		return fgets( $fp, $len );
	}

	function fclose( $fp ) {
		if ( $this->has_gzip ) {
			return gzclose( $fp );
		}

		return fclose( $fp );
	}
}
