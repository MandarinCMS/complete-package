<?php
/**
 * @package MCMSSEO\Admin\Bulk Editor
 * @since      1.5.0
 */

/**
 * Implements table for bulk description editing.
 */
class MCMSSEO_Bulk_Description_List_Table extends MCMSSEO_Bulk_List_Table {

	/**
	 * Current type for this class will be (meta) description.
	 *
	 * @var string
	 */
	protected $page_type = 'description';

	/**
	 * Settings with are used in __construct
	 *
	 * @var array
	 */
	protected $settings = array(
		'singular' => 'mcmsseo_bulk_description',
		'plural'   => 'mcmsseo_bulk_descriptions',
		'ajax'     => true,
	);

	/**
	 * The field in the database where meta field is saved.
	 *
	 * @var string
	 */
	protected $target_db_field = 'metadesc';

	/**
	 * The columns shown on the table
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'col_existing_ultimatum_seo_metadesc' => __( 'Existing Ultimatum Meta Description', 'mandarincms-seo' ),
			'col_new_ultimatum_seo_metadesc'      => __( 'New Ultimatum Meta Description', 'mandarincms-seo' ),
		);

		return $this->merge_columns( $columns );
	}

	/**
	 * Parse the metadescription
	 *
	 * @param string $column_name Column name.
	 * @param object $record      Data object.
	 * @param string $attributes  HTML attributes.
	 *
	 * @return string
	 */
	protected function parse_page_specific_column( $column_name, $record, $attributes ) {
		switch ( $column_name ) {
			case 'col_new_ultimatum_seo_metadesc':
				return sprintf(
					'<textarea id="%1$s" name="%1$s" class="mcmsseo-new-metadesc" data-id="%2$s" aria-labelledby="col_new_ultimatum_seo_metadesc"></textarea>',
					'mcmsseo-new-metadesc-' . $record->ID,
					$record->ID
				);
				break;

			case 'col_existing_ultimatum_seo_metadesc':
				// TODO inconsistent return/echo behavior R.
				echo $this->parse_meta_data_field( $record->ID, $attributes );
				break;
		}
	}
} /* End of class */
