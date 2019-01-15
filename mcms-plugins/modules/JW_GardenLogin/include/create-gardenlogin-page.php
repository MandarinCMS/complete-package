<?php
/**
* Create GardenLogin Page.
*
* @package   GardenLogin
* @author    JIIWorks
* @since     1.1.3
*/

// Exit if accessed directly.
if ( ! defined( 'BASED_TREE_URI' ) ) {
  exit;
}

/**
*
*/
class GardenLogin_Page_Create {

  function __construct()
  {
    $this->_init();
    $this->_hooks();
  }

  function _hooks(){
    add_action( 'mcmsmu_new_blog', array( $this, 'gardenlogin_new_site_created' ), 10, 6 );
  }

  public function _init(){
    global $mcmsdb;

    if ( ! current_user_can( 'activate_modules' ) ) {
      return;
    }

    if ( is_multisite() ) {

      foreach ( $mcmsdb->get_col( "SELECT blog_id FROM $mcmsdb->blogs LIMIT 100" ) as $blog_id ) {
        switch_to_blog( $blog_id );
        $this->gardenlogin_run_install();
        restore_current_blog();
      }

    } else {
      $this->gardenlogin_run_install();
    }
  }

  /**
  * Run the GardenLogin install process
  *
  * @return void
  */
  public function gardenlogin_run_install() {

    /* translators: 1: Name of this module */
    $post_content = sprintf( __( '<p>This page is used by %1$s to preview the login page in the Customizer.</p>', 'gardenlogin' ), 'GardenLogin' );

    $pages = apply_filters(
      'gardenlogin_create_pages', array(
        'gardenlogin' => array(
          'name'    => _x( 'gardenlogin', 'Page slug', 'gardenlogin' ),
          'title'   => _x( 'GardenLogin', 'Page title', 'gardenlogin' ),
          'content' => $post_content,
        ),
      )
    );

    foreach ( $pages as $key => $page ) {
      $this->gardenlogin_create_page( esc_sql( $page['name'] ), 'gardenlogin_page', $page['title'], $page['content'] );
    }
  }

  /**
  * Create a page and store the ID in an option.
  *
  * @param mixed  $slug Slug for the new page.
  * @param string $option Option name to store the page's ID.
  * @param string $page_title (default: '') Title for the new page.
  * @param string $page_content (default: '') Content for the new page.
  * @return int   page ID
  */
  public function gardenlogin_create_page( $slug, $option = '', $page_title = '', $page_content = '' ) {
    global $mcmsdb;

    // Set up options.
    $options = array();

    // Pull options from MCMS.
    $gardenlogin_setting = get_option( 'gardenlogin_setting', array() );
    $option_value  = array_key_exists( 'gardenlogin_page', $gardenlogin_setting ) ? $gardenlogin_setting['gardenlogin_page'] : false;

    if ( $option_value > 0 && ( $page_object = get_post( $option_value ) ) ) {
      if ( 'page' === $page_object->post_type && ! in_array( $page_object->post_status, array( 'pending', 'trash', 'future', 'auto-draft' ), true ) ) {
        // Valid page is already in place.
        return $page_object->ID;
      }
    }

    // Search for an existing page with the specified page slug.
    $gardenlogin_page_found = $mcmsdb->get_var( $mcmsdb->prepare( "SELECT ID FROM $mcmsdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' )  AND post_name = %s LIMIT 1;", $slug ) );

    $gardenlogin_page_found = apply_filters( 'gardenlogin_create_page_id', $gardenlogin_page_found, $slug, $page_content );

    if ( $gardenlogin_page_found ) {

      if ( $option ) {

        $options['gardenlogin_page']     = $gardenlogin_page_found;
        $gardenlogin_page_found          = isset( $page_id ) ? $gardenlogin_page_found : $option_value;
        $merged_options                 = array_merge( $gardenlogin_setting, $options );
        $gardenlogin_setting             = $merged_options;

        update_option( 'gardenlogin_setting', $gardenlogin_setting );
      }
      return $gardenlogin_page_found;
    }

    // Search for an existing page with the specified page slug.
    $gardenlogin_trashed_found = $mcmsdb->get_var( $mcmsdb->prepare( "SELECT ID FROM $mcmsdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_name = %s LIMIT 1;", $slug ) );

    if ( $gardenlogin_trashed_found ) {
      $page_id   = $gardenlogin_trashed_found;
      $page_data = array(
        'ID'          => $page_id,
        'post_status' => 'publish',
      );

      mcms_update_post( $page_data );

    } else {

      $page_data = array(
        'post_status'    => 'publish',
        'post_type'      => 'page',
        'post_author'    => 1,
        'post_name'      => $slug,
        'post_title'     => $page_title,
        'post_content'   => $page_content,
        'comment_status' => 'closed',
      );

      $page_id = mcms_insert_post( $page_data );
    }

    if ( $option ) {

      $options['gardenlogin_page'] = $page_id;
      $page_id                        = isset( $page_id ) ? $page_id : $option_value;
      $merged_options                 = array_merge( $gardenlogin_setting, $options );
      $gardenlogin_setting             = $merged_options;

      update_option( 'gardenlogin_setting', $gardenlogin_setting );
    }

    // Assign the GardenLogin template.
    $this->gardenlogin_attach_template_to_page( $page_id, 'template-gardenlogin.php' );

    return $page_id;
  }

  /**
  * Attaches the specified template to the page identified by the specified name.
  *
  * @param int|int $page The id of the page to attach the template.
  * @param int|int $template The template's filename (assumes .php' is specified).
  *
  * @returns -1 if the page does not exist; otherwise, the ID of the page.
  */
  public function gardenlogin_attach_template_to_page( $page, $template ) {

    // Only attach the template if the page exists.
    if ( -1 !== $page ) {
      update_post_meta( $page, '_mcms_page_template', $template );
    }

    return $page;
  }

  /**
  * When a new Blog is created in multisite, check if GardenLogin is network activated, and run the installer
  *
  * @param int|int     $blog_id The Blog ID created.
  * @param int|int     $user_id The User ID set as the admin.
  * @param string      $domain The URL.
  * @param string      $path Site Path.
  * @param int|int     $site_id The Site ID.
  * @param array|array $meta Blog Meta.
  * @return void
  */
  public function gardenlogin_new_site_created( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

    if ( is_module_active_for_network( module_basename( LOGINPRESS_ROOT_FILE ) ) ) {

      switch_to_blog( $blog_id );
      $this->_init();
      restore_current_blog();

    }
  }
}


?>
