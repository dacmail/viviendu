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

	function list_options() {
		return array(
			'mfrh_auto_rename' => false,
			'mfrh_on_upload' => false,
			'mfrh_rename_slug' => false,
			'mfrh_convert_to_ascii' => false,
			'mfrh_update_posts' => true,
			'mfrh_update_excerpts' => false,
			'mfrh_update_postmeta' => false,
			'mfrh_undo' => false,
			'mfrh_move' => false,
			'mfrh_manual_rename' => false,
			'mfrh_manual_sanitize' => false,
			'mfrh_numbered_files' => false,
			'mfrh_sync_alt' => false,
			'mfrh_sync_media_title' => false,
			'mfrh_force_rename' => false,
			'mfrh_log' => false,
			'mfrh_logsql' => false,
			'mfrh_rename_guid' => false,
			'mfrh_case_insensitive_check' => false,
			'mfrh_rename_on_save' => false,
			'mfrh_acf_field_name' => false,
			'mfrh_images_only' => false,
			'mfrh_featured_only' => false,
			'mfrh_posts_per_page' => 10,
			'mfrh_autolock_auto' => false,
			'mfrh_autolock_manual' => true,
			'mfrh_delay' => 100,
			'mfrh_clean_uninstall' => false,
		);
	}

	function needs_registered_options() {
		return array(
			'mfrh_convert_to_ascii',
			'mfrh_numbered_files',
			'mfrh_sync_alt',
			'mfrh_sync_media_title',
			'mfrh_force_rename',
			'mfrh_logsql',
		);
	}

	function get_all_options() {
		$options = $this->list_options();
		$needs_registered_options = $this->needs_registered_options();
		$current_options = array();
		foreach ( $options as $option => $default ) {
			if (in_array($option, $needs_registered_options)) {
				$current_options[$option] = $this->is_registered() && get_option( $option, $default );
				continue;
			}
			$current_options[$option] = get_option( $option, $default );
		}
		return $current_options;
	}
}

?>
