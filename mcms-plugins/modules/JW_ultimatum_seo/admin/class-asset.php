<?php
/**
 * @package MCMSSEO\Admin
 */

/**
 * Represents a MCMSSEO asset
 */
class MCMSSEO_Admin_Asset {

	const TYPE_JS = 'js';
	const TYPE_CSS = 'css';

	const NAME = 'name';
	const SRC = 'src';
	const DEPS = 'deps';
	const VERSION = 'version';

	// Style specific.
	const MEDIA = 'media';
	const RTL = 'rtl';

	// Script specific.
	const IN_FOOTER = 'in_footer';

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $src;

	/**
	 * @var string|array
	 */
	protected $deps;

	/**
	 * @var string
	 */
	protected $version;

	/**
	 * @var string
	 */
	protected $media;

	/**
	 * @var boolean
	 */
	protected $in_footer;

	/**
	 * @var boolean
	 */
	protected $rtl;

	/**
	 * @var string
	 */
	protected $suffix;

	/**
	 * @param array $args The arguments for this asset.
	 *
	 * @throws InvalidArgumentException Throws when no name or src has been provided.
	 */
	public function __construct( array $args ) {
		if ( ! isset( $args['name'] ) ) {
			throw new InvalidArgumentException( 'name is a required argument' );
		}

		if ( ! isset( $args['src'] ) ) {
			throw new InvalidArgumentException( 'src is a required argument' );
		}

		$args = array_merge( array(
			'deps'      => array(),
			'version'   => MCMSSEO_VERSION,
			'in_footer' => true,
			'rtl'       => true,
			'media'     => 'all',
			'suffix'    => MCMSSEO_CSSJS_SUFFIX,
		), $args );

		$this->name      = $args['name'];
		$this->src       = $args['src'];
		$this->deps      = $args['deps'];
		$this->version   = $args['version'];
		$this->media     = $args['media'];
		$this->in_footer = $args['in_footer'];
		$this->rtl       = $args['rtl'];
		$this->suffix    = $args['suffix'];
	}

	/**
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function get_src() {
		return $this->src;
	}

	/**
	 * @return array|string
	 */
	public function get_deps() {
		return $this->deps;
	}

	/**
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * @return string
	 */
	public function get_media() {
		return $this->media;
	}

	/**
	 * @return boolean
	 */
	public function is_in_footer() {
		return $this->in_footer;
	}

	/**
	 * @return boolean
	 */
	public function has_rtl() {
		return $this->rtl;
	}

	/**
	 * @return string
	 */
	public function get_suffix() {
		return $this->suffix;
	}

	/**
	 * Returns the full URL for this asset based on the path to the module file.
	 *
	 * @param string $type        Type of asset.
	 * @param string $module_file Absolute path to the module file.
	 *
	 * @return string The full URL to the asset.
	 */
	public function get_url( $type, $module_file ) {

		$relative_path = $this->get_relative_path( $type );
		if ( empty( $relative_path ) ) {
			return '';
		}

		if ( ! $this->get_suffix() ) {

			$module_path = module_dir_path( $module_file );
			if ( ! file_exists( $module_path . $relative_path ) ) {

				// Give a notice to the user in the console (only once).
				MCMSSEO_Utils::javascript_console_notification(
					'Development Files',
					sprintf(
						/* translators: %1$s resolves to https://github.com/Ultimatum/mandarincms-seo */
						__( 'You are trying to load non-minified files, these are only available in our development package. Check out %1$s to see all the source files.', 'mandarincms-seo' ),
						'https://github.com/Ultimatum/mandarincms-seo'
					),
					true
				);

				// Just load the .min file.
				$relative_path = $this->get_relative_path( $type, '.min' );
			}
		}

		return modules_url( $relative_path, $module_file );
	}

	/**
	 * Get the relative file for this asset
	 *
	 * @param string $type         Type of this asset.
	 * @param null   $force_suffix Force use suffix.
	 *
	 * @return string
	 */
	protected function get_relative_path( $type, $force_suffix = null ) {
		$relative_path = $rtl_path = $rtl_suffix = '';

		$suffix = ( is_null( $force_suffix ) ) ? $this->get_suffix() : $force_suffix;

		switch ( $type ) {
			case self::TYPE_JS:
				$relative_path = 'js/dist/' . $this->get_src() . $suffix . '.js';
				break;

			case self::TYPE_CSS:
				// Path and suffix for RTL stylesheets.
				if ( function_exists( 'is_rtl' ) && is_rtl() && $this->has_rtl() ) {
					$rtl_path = 'dist/';
					$rtl_suffix = '-rtl';
				}
				$relative_path = 'css/' . $rtl_path . $this->get_src() . $rtl_suffix . $suffix . '.css';
				break;
		}

		return $relative_path;
	}
}
