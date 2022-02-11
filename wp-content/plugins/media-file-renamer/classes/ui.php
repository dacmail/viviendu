<?php

class Meow_MFRH_UI {
	private $core = null;

	function __construct( $core ) {
		$this->core = $core;
		$is_manual = get_option( 'mfrh_manual_rename', false );

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_filter( 'media_send_to_editor', array( $this, 'media_send_to_editor' ), 20, 3 );

		// Add the metabox and the column if it's either manual or automatic
		if ( $core->method != 'none' || $is_manual ) {
			add_filter( 'manage_media_columns', array( $this, 'add_media_columns' ) );
			add_action( 'manage_media_custom_column', array( $this, 'manage_media_custom_column' ), 10, 2 );
			add_action( 'add_meta_boxes', array( $this, 'add_rename_metabox' ) );
		}
	}

	function admin_menu() {
		add_media_page( 'Media File Renamer', __( 'Renamer', 'media-file-renamer' ), 'read', 
			'mfrh_dashboard', array( $this, 'rename_media_files' ), 1 );
	}

	function media_send_to_editor( $html, $id, $attachment ) {
		$output = array();
		$this->core->check_attachment( get_post( $id, ARRAY_A ), $output );
		return $html;
	}

	public function rename_media_files() {
		echo '<div id="mfrh-media-rename"></div>';
	}

	function add_rename_metabox() {
		add_meta_box( 'mfrh_media', 'Renamer', array( $this, 'attachment_fields' ), 'attachment', 'side', 'high' );
	}

	function attachment_fields( $post ) {
		if ( $post ) {
			echo '
				<div class="mfrh-renamer-field" data-id="' . $post->ID . '"></div>
				<div style="line-height: 15px; font-size: 12px; margin-top: 10px;">After an update, please reload this Edit Media page.</div>
			';
		}
	}

	function add_media_columns( $columns ) {
		$columns['mfrh_column'] = __( 'Renamer', 'media-file-renamer' );
		return $columns;
	}

	function manage_media_custom_column( $column_name, $id ) {
		if ( $column_name === 'mfrh_column' ) {
			echo '<div class="mfrh-renamer-field" data-id="' . $id . '"></div>';
		}
	}
}
