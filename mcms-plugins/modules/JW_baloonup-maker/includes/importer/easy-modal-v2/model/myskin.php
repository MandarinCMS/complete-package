<?php

// Exit if accessed directly
if ( ! defined( 'BASED_TREE_URI' ) ) {
	exit;
}

class EModal_Model_mySkin extends EModal_Model {
	protected $_class_name = 'EModal_Model_mySkin';
	protected $_table_name = 'em_myskins';
	protected $meta;
	protected $_default_fields = array(
		'id'        => null,
		'name'      => 'Default',
		'created'   => '0000-00-00 00:00:00',
		'modified'  => '0000-00-00 00:00:00',
		'is_system' => 0,
		'is_trash'  => 0
	);

	public function __construct( $id = null ) {
		parent::__construct( $id );
		$this->load_meta();

		return $this;
	}

	public function __get( $key ) {
		if ( $key == 'meta' ) {
			return $this->meta;
		} else {
			return parent::__get( $key );
		}
	}

	public function save() {
		if ( ! $this->id ) {
			$this->created = date( 'Y-m-d H:i:s' );
		}
		$this->modified = date( 'Y-m-d H:i:s' );
		parent::save();
		$this->meta->myskin_id = $this->id;
		$this->meta->save();
	}

	public function load_meta() {
		if ( empty( $this->meta ) ) {
			$this->meta = new EModal_Model_mySkin_Meta( $this->id );
		}

		return $this->meta;
	}

	public function as_array() {
		$array         = parent::as_array();
		$array['meta'] = $this->meta->as_array();

		return $array;
	}

	public function set_fields( array $data ) {
		if ( ! empty( $data['meta'] ) ) {
			$this->meta->set_fields( $data['meta'] );
			unset( $data['meta'] );
		}
		parent::set_fields( $data );
	}
}

if ( ! function_exists( "get_all_modal_myskins" ) ) {
	function get_all_modal_myskins( $where = "is_trash != 1" ) {
		global $mcmsdb;

		$myskins                  = array();
		$myskin_ids               = array();
		$EModal_Model_mySkin      = new EModal_Model_mySkin;
		$EModal_Model_mySkin_Meta = new EModal_Model_mySkin_Meta;
		foreach ( $EModal_Model_mySkin->load( "SELECT * FROM  {$mcmsdb->prefix}em_myskins" . ( $where ? " WHERE " . $where : '' ) ) as $myskin ) {
			$myskins[ $myskin->id ] = $myskin;
			$myskin_ids[]          = $myskin->id;
		}
		if ( count( $myskins ) ) {
			foreach ( $EModal_Model_mySkin_Meta->load( "SELECT * FROM  {$mcmsdb->prefix}em_myskin_metas WHERE myskin_id IN (" . implode( ',', $myskin_ids ) . ")" ) as $meta ) {
				$myskins[ $meta->myskin_id ]->meta->process_load( $meta->as_array() );
			}
		}

		return $myskins;
	}
}

if ( ! function_exists( "get_current_modal_myskin" ) ) {
	function get_current_modal_myskin( $key = null ) {
		global $current_myskin;
		if ( ! $key ) {
			return $current_myskin;
		} else {
			$values = $current_myskin->as_array();

			return emresolve( $values, $key, false );
		}
	}
}

if ( ! function_exists( "get_current_modal_myskin_id" ) ) {
	function get_current_modal_myskin_id() {
		global $current_myskin;

		return $current_myskin->id;
	}
}


if ( ! function_exists( "count_all_modal_myskins" ) ) {
	function count_all_modal_myskins() {
		global $mcmsdb;

		return (int) $mcmsdb->get_var( "SELECT COUNT(*) FROM  {$mcmsdb->prefix}em_myskins WHERE is_trash = 0" );
	}
}

if ( ! function_exists( "count_deleted_modal_myskins" ) ) {
	function count_deleted_modal_myskins() {
		global $mcmsdb;

		return (int) $mcmsdb->get_var( "SELECT COUNT(*) FROM  {$mcmsdb->prefix}em_myskins WHERE is_trash = 1" );
	}
}