<?php
/**
 * @package MCMSSEO\Premium\Classes
 */

/**
 * Class MCMSSEO_Redirect_Manager
 */
class MCMSSEO_Redirect_Manager {

	/**
	 * @var MCMSSEO_Redirect_Option Model object to handle the redirects.
	 */
	protected $redirect_option;

	/**
	 * @var string The redirect format, this might be plain or regex.
	 */
	protected $redirect_format;

	/**
	 * @var MCMSSEO_Redirect_Exporter[]
	 */
	protected $exporters;

	/**
	 * Returns the default exporters.
	 *
	 * @return MCMSSEO_Redirect_Exporter[]
	 */
	public static function default_exporters() {
		$exporters[] = new MCMSSEO_Redirect_Option_Exporter();

		$options = MCMSSEO_Redirect_Page::get_options();
		if ( 'on' === $options['disable_php_redirect'] && $file_exporter = MCMSSEO_Redirect_File_Util::get_file_exporter( $options['separate_file'] ) ) {
			$exporters[] = $file_exporter;
		}

		return $exporters;
	}

	/**
	 * Setting the property with the redirects
	 *
	 * @param string                    $redirect_format The format for the redirects.
	 * @param MCMSSEO_Redirect_Exporter[] $exporters       The exporters used to save redirects in files.
	 * @param MCMSSEO_Redirect_Option     $option          Model object to handle the redirects.
	 */
	public function __construct( $redirect_format = MCMSSEO_Redirect::FORMAT_PLAIN, $exporters = null, MCMSSEO_Redirect_Option $option = null ) {
		if ( null === $option ) {
			$option = new MCMSSEO_Redirect_Option();
		}
		$this->redirect_option = $option;

		$this->redirect_format = $redirect_format;
		$this->exporters       = ( $exporters ) ? $exporters : self::default_exporters();
	}

	/**
	 * Get the redirects
	 *
	 * @return MCMSSEO_Redirect[]
	 */
	public function get_redirects() {
		// Filter the redirect for the current format.
		return array_filter( $this->redirect_option->get_all(), array( $this, 'filter_redirects_by_format' ) );
	}

	/**
	 * Returns all redirects
	 *
	 * @return MCMSSEO_Redirect[]
	 */
	public function get_all_redirects() {
		return $this->redirect_option->get_all();
	}

	/**
	 * Export the redirects to the specified sources.
	 */
	public function export_redirects() {
		$redirects = $this->redirect_option->get_all();

		foreach ( $this->exporters as $exporter ) {
			$exporter->export( $redirects );
		}
	}

	/**
	 * Create a new redirect
	 *
	 * @param MCMSSEO_Redirect $redirect The redirect object to add.
	 *
	 * @return bool
	 */
	public function create_redirect( MCMSSEO_Redirect $redirect ) {
		if ( $this->redirect_option->add( $redirect ) ) {
			$this->save_redirects();

			return true;
		}

		return false;
	}

	/**
	 * Save the redirect
	 *
	 * @param MCMSSEO_Redirect $current_redirect The old redirect, the value is a key in the redirects array.
	 * @param MCMSSEO_Redirect $redirect         New redirect object.
	 *
	 * @return bool
	 */
	public function update_redirect( MCMSSEO_Redirect $current_redirect, MCMSSEO_Redirect $redirect ) {
		if ( $this->redirect_option->update( $current_redirect, $redirect ) ) {
			$this->save_redirects();

			return true;
		}

		return false;
	}

	/**
	 * Delete the redirects
	 *
	 * @param MCMSSEO_Redirect[] $delete_redirects Array with the redirects to remove.
	 *
	 * @return bool
	 */
	public function delete_redirects( $delete_redirects ) {
		$deleted = false;
		foreach ( $delete_redirects as $delete_redirect ) {
			if ( $this->redirect_option->delete( $delete_redirect ) ) {
				$deleted = true;
			}
		}

		if ( $deleted === true ) {
			$this->save_redirects();
		}

		return $deleted;
	}

	/**
	 * Returns the redirect when it's found, otherwise it will return false.
	 *
	 * @param string $origin The origin to search for.
	 *
	 * @return bool|MCMSSEO_Redirect
	 */
	public function get_redirect( $origin ) {
		return $this->redirect_option->get( $origin );
	}

	/**
	 * This method will save the redirect option and if necessary the redirect file.
	 */
	public function save_redirects() {
		// Update the database option.
		$this->redirect_option->save();

		// Save the redirect file.
		$this->export_redirects();
	}

	/**
	 * Filter the redirects that don't match the needed format
	 *
	 * @param MCMSSEO_Redirect $redirect The redirect to filter.
	 *
	 * @return bool
	 */
	private function filter_redirects_by_format( MCMSSEO_Redirect $redirect ) {
		return $redirect->get_format() === $this->redirect_format;
	}
}
