<?php

class Meow_MFRH_Admin extends MeowCommon_Admin {

	public function __construct( $allow_setup ) {
		parent::__construct( MFRH_PREFIX, MFRH_ENTRY, MFRH_DOMAIN, class_exists( 'MeowPro_MFRH_Core' ) );
		if ( is_admin() ) {
			if ( $allow_setup ) {
				add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			}

			// Load the scripts only if they are needed by the current screen
			$uri = $_SERVER['REQUEST_URI'];
			$page = isset( $_GET["page"] ) ? $_GET["page"] : null;
			$is_media_library = preg_match( '/wp\-admin\/upload\.php/', $uri );
			$is_post_edit = preg_match( '/wp\-admin\/post\.php/', $uri );
			$is_mfrh_screen = in_array( $page, [ 'mfrh_dashboard', 'mfrh_settings' ] );
			$is_meowapps_dashboard = $page === 'meowapps-main-menu';
			if ( $is_meowapps_dashboard || $is_media_library || $is_mfrh_screen || $is_post_edit ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			}
		}
	}

	function admin_enqueue_scripts() {

		// Load the scripts
		$physical_file = MFRH_PATH . '/app/index.js';
		$cache_buster = file_exists( $physical_file ) ? filemtime( $physical_file ) : MFRH_VERSION;
		wp_register_script( 'mfrh_media_file_renamer-vendor', MFRH_URL . 'app/vendor.js',
			['wp-element', 'wp-i18n'], $cache_buster
		);
		wp_register_script( 'mfrh_media_file_renamer', MFRH_URL . 'app/index.js',
			['mfrh_media_file_renamer-vendor', 'wp-i18n'], $cache_buster
		);
		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'mfrh_media_file_renamer', 'media-file-renamer' );
		}
		wp_enqueue_script('mfrh_media_file_renamer' );

		// Load the fonts
		wp_register_style( 'meow-neko-ui-lato-font', 
			'//fonts.googleapis.com/css2?family=Lato:wght@100;300;400;700;900&display=swap');
		wp_enqueue_style( 'meow-neko-ui-lato-font' );

		// Localize and options
		wp_localize_script( 'mfrh_media_file_renamer', 'mfrh_media_file_renamer', array_merge( [
			//'api_nonce' => wp_create_nonce( 'mfrh_media_file_renamer' ),
			'api_url' => get_rest_url(null, '/media-file-renamer/v1/'),
			'rest_url' => get_rest_url(),
			'plugin_url' => MFRH_URL,
			'prefix' => MFRH_PREFIX,
			'domain' => MFRH_DOMAIN,
			'is_pro' => class_exists( 'MeowPro_MFRH_Core' ),
			'is_registered' => !!$this->is_registered(),
			'rest_nonce' => wp_create_nonce( 'wp_rest' ),
		], $this->get_all_options() ) );
	}

	function admin_menu() {
		add_submenu_page( 'meowapps-main-menu', __( 'Renamer', MFRH_DOMAIN ), __( 'Renamer', MFRH_DOMAIN ), 
			'read', 'mfrh_settings', array( $this, 'admin_settings' )
		);
	}

	public function admin_settings() {
		echo '<div id="mfrh-admin-settings"></div>';
	}

	function get_all_options() {
		return array(
			'mfrh_auto_rename' => get_option( 'mfrh_auto_rename', false ),
			'mfrh_on_upload' => get_option( 'mfrh_on_upload', false ),
			'mfrh_rename_slug' => get_option( 'mfrh_rename_slug', false ),
			'mfrh_convert_to_ascii' => $this->is_registered() && get_option( 'mfrh_convert_to_ascii', false ),
			'mfrh_update_posts' => get_option( 'mfrh_update_posts', true ),
			'mfrh_update_postmeta' => get_option( 'mfrh_update_postmeta', true ),
			'mfrh_undo' => get_option( 'mfrh_undo', false ),
			'mfrh_move' => get_option( 'mfrh_move', false ),
			'mfrh_manual_rename' => get_option( 'mfrh_manual_rename', false ),
			'mfrh_numbered_files' => $this->is_registered() && get_option( 'mfrh_numbered_files', false ),
			'mfrh_sync_alt' => $this->is_registered() && get_option( 'mfrh_sync_alt', false ),
			'mfrh_sync_media_title' => $this->is_registered() && get_option( 'mfrh_sync_media_title', false ),
			'mfrh_force_rename' => $this->is_registered() && get_option( 'mfrh_force_rename', false ),
			'mfrh_log' => get_option( 'mfrh_log', false ),
			'mfrh_logsql' => $this->is_registered() && get_option( 'mfrh_logsql', false ),
			'mfrh_rename_guid' => get_option( 'mfrh_rename_guid', false ),
			'mfrh_case_insensitive_check' => get_option( 'mfrh_case_insensitive_check', false ),
			'mfrh_rename_on_save' => get_option( 'mfrh_rename_on_save', false ),
			'mfrh_acf_field_name' => get_option( 'mfrh_acf_field_name' ),
			'mfrh_images_only' => get_option( 'mfrh_images_only', false ),
			'mfrh_posts_per_page' => get_option( 'mfrh_posts_per_page', 10 )
		);
	}
}

?>
