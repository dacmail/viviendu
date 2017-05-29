<?php
/*
* Define class AATeam_Product_Updater
* Make sure you skip down to the end of this file, as there are a few
* lines of code that are very important.
*/
!defined('ABSPATH') and exit;
if (class_exists('AATeam_Product_Updater') != true) {
    class AATeam_Product_Updater
    {
    	/*
        * Store some helpers config
        */
        protected $the_plugin = null;
		
		/**
		 * Current version
		 *
		 * @var string
		 */
		public $current_version;
	
		/**
		 * The product remote update url
		 *
		 * @var string
		 */
		protected $update_url = 'http://docs.aa-team.com/apps-versions/';
		
		/**
		 * The product download update url
		 *
		 * @var string
		 */
		protected $download_url = 'http://docs.aa-team.com/apps-versions/';
		
		protected $key_alias = 'WooZone_register_key';
		
		/**
		 * Product alias & path
		 *
		 * @var string
		 */
		public $product_slug;
		public $product_path;
		
		/**
		 * @var string
		 */
		public $title = 'WooZone - WooCommerce Amazon Affiliates';
		    
        /*
        * Required __construct() function that initalizes the AA-Team Framework
        */
        function __construct( $the_plugin, $current_version, $product_slug, $product_path )
        {
            // Set the class public variables
            $this->update_url = $this->update_url . $product_slug;
            $this->the_plugin = $the_plugin;
			$this->current_version = $current_version;
			$this->product_slug = $product_slug;
			$this->product_path = $product_path;
			
			add_action( 'admin_enqueue_scripts', array($this, 'custom_update_style') );
			
			// define the alternative API for updating checking
			add_filter( 'pre_set_site_transient_update_plugins', array(
				$this,
				'check_update'
			) );
	
			// Define the alternative response for information checking
			add_filter( 'plugins_api', array(
				$this,
				'check_info',
			), 10, 3 );
	
			add_action( 'in_plugin_update_message-' . $this->product_slug, array(
				$this,
				'addUpgradeMessageLink',
			) );
			
			add_filter( 'upgrader_pre_download', array(
				$this,
				'preUpgradeFilter',
			), 10, 4 );
        }

		public function custom_update_style() {
			wp_enqueue_style('aa-updater-style', $this->the_plugin->cfg['paths']['freamwork_dir_url'] . 'css/updater.css');
		}
        
		/**
		 * Get url for version validation
		 * @return string
		 */
		public function versionUrl() {
			return $this->update_url;
		}
		
		/**
		 * Get unique, short-lived download link
		 *
		 * @return array|boolean JSON response or false if request failed
		 */
		public function getDownloadUrl() {
			$url = $this->getUrl();
			
			$response = wp_remote_get( $url );
			  
			if ( is_wp_error( $response ) ) {
				return false;
			}
	
			return json_decode( $response['body'], true );
		}
		
		protected function getUrl() {
			global $wpdb;
			
			if( isset($this->key_alias) && trim($this->key_alias) != '' ) {
				$ipc = get_option( $this->key_alias );
			}
	
			$url = $this->download_url . '?product=' . $this->product_slug . '&ipc=' . $ipc . '&version=' . $this->current_version . '&site_url=' . urlencode( esc_url(home_url('/')) );
			
			if( $this->product_slug == 'woozone' ) {
				$product_count = $wpdb->get_col( $wpdb->prepare( "
			        SELECT COUNT(pm.meta_id) FROM {$wpdb->postmeta} pm
			        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			        WHERE pm.meta_key = '%s' 
			        AND p.post_type = '%s'
			    ", '_amzASIN', 'product' ) );
				
				if( is_array($product_count) && count($product_count) > 0 ) {
					$url .= '&product_count=' . end($product_count);
				}
			}
	
			return $url;
		}
		
		/**
		 * Gets product is activated as network plugin.
		 *
		 * @since  9.0.4.1
		 * @access public
		 *
		 * @return bool
		 */
		public function isNetworkPlugin() {
			if ( is_multisite() && ( is_plugin_active_for_network( $this->product_path ) || is_network_only_plugin( $this->product_path ) ) ) {
				return true;
			}
			
			return false;
		}
		
		public static function getUpdaterUrl() {
			return $this->isNetworkPlugin() ? network_admin_url( 'admin.php?page=woozone-updater' ) : admin_url( 'admin.php?page=woozone-updater' );
		}
		
		/**
		 * Get link to newest product version
		 *
		 * @return mixed|string|WP_Error
		 */
		public function preUpgradeFilter( $reply, $package, $updater ) {
			$condition1 = isset( $updater->skin->plugin ) && $this->product_slug === $updater->skin->plugin;
			$condition2 = isset( $updater->skin->plugin_info ) && $updater->skin->plugin_info['Name'] === $this->title;
			if ( ! $condition1 && ! $condition2 ) {
				return $reply;
			}
			
			$res = $updater->fs_connect( array( WP_CONTENT_DIR ) );
			if ( ! $res ) {
				return new WP_Error( 'no_credentials', __( "Error! Can't connect to filesystem", 'woozone' ) );
			}
			
			/*if ( ! isActivated() ) {
				$url = esc_url( self::getUpdaterUrl() );
			 	return new WP_Error( 'no_credentials', __( 'To receive automatic updates license activation is required. Please visit <a href="' . $url . '' . '" target="_blank">Settings</a> to activate your product.', 'woozone' ) );
			}*/
	
			$updater->strings['downloading_package_url'] = __( 'Getting download link...', 'woozone' );
			$updater->skin->feedback( 'downloading_package_url' );
	
			$response = $this->getDownloadUrl();

			if ( ! $response ) {
				return new WP_Error( 'no_credentials', __( 'Download link could not be retrieved', 'woozone' ) );
			}
	
			if ( ! $response['status'] ) {
				return new WP_Error( 'no_credentials', $response['error'] );
			}
			
			if( $response['status'] == 'invalid' ) {
				return new WP_Error( 'no_credentials', $response['html'] );
			}
	
			$updater->strings['downloading_package'] = __( 'Downloading package...', 'woozone' );
			$updater->skin->feedback( 'downloading_package' );
	
			$downloaded_archive = download_url( $response['url'] );
			if ( is_wp_error( $downloaded_archive ) ) {
				return $downloaded_archive;
			}
	
			$plugin_directory_name = dirname( $this->product_slug );
	
			// WP will use same name for plugin directory as archive name, so we have to rename it
			if ( basename( $downloaded_archive, '.zip' ) !== $plugin_directory_name ) {
				$new_archive_name = dirname( $downloaded_archive ) . '/' . $plugin_directory_name . time() . '.zip';
				if ( rename( $downloaded_archive, $new_archive_name ) ) {
					$downloaded_archive = $new_archive_name;
				}
			}
	
			return $downloaded_archive;
		}

		/**
		 * Add our self-hosted autoupdate plugin to the filter transient
		 *
		 * @param $transient
		 *
		 * @return object $ transient
		 */
		public function check_update( $transient ) {
			$plugin_folder = $this->the_plugin->plugin_details['folder_index'];
			 
			// Extra check for 3rd plugins
			if ( isset( $transient->response[ $plugin_folder ] ) ) {
				return $transient;
			}
			  
			// Get the remote version
			$remote_version = $this->getRemote_version();  
			  
			// If a newer version is available, add the update
			if ( version_compare( $this->current_version, $remote_version, '<' ) ) {
				$obj = new stdClass();
				$obj->slug = $this->product_slug;
				$obj->new_version = $remote_version;
				$obj->url = '';
				$obj->package = 'woozone.zip';
				$obj->name = $this->the_plugin->pluginName;
				$transient->response[ "$plugin_folder" ] = $obj;
			}
			  
			return $transient;
		}
		
		/**
		 * Add our self-hosted description/changelog to the filter
		 *
		 * @param bool $false
		 * @param array $action
		 * @param object $arg
		 *
		 * @return bool|object
		 */
		public function check_info( $false, $action, $arg ) {   
			if ( isset( $arg->slug ) && $arg->slug === $this->product_slug ) {
				$changelog = $this->getRemote_information();
				  
				$array_pattern = array(
					'/^([\*\s])*(\d\d\.\d\d\.\d\d\d\d[^\n]*)/m',
					'/^\n+|^[\t\s]*\n+/m',
					'/\n/',
				);
				$array_replace = array(
					'<div>&nbsp;</div><strong>$2</strong>',
					'</div><div>',
					'</div><div>',
				);
				
				$changelog->name = $this->title;
				$changelog->sections = (array) $changelog->sections;
				$changelog->sections['changelog'] = '<div>' . preg_replace( $array_pattern, $array_replace, $changelog->sections['changelog'] ) . '</div>';
				
				return $changelog;
			}
	
			return $false;
		}
	
		/**
		 * Return the remote version
		 *
		 * @return string $remote_version
		 */
		public function getRemote_version() {
			$request = wp_remote_get( $this->update_url . '/latest' );
			if ( ! is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200 ) {
				return $request['body'];
			}
	
			return false;
		}
	
		/**
		 * Get information about the remote version
		 *
		 * @return bool|object
		 */
		public function getRemote_information() {
			$request = wp_remote_get( $this->update_url . '/changelog.json' );
			 
			if ( ! is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) === 200 ) {
				return json_decode( $request['body'] );
			}
	
			return false;
		}
	
		/**
		 * Shows message on Wp plugins page with a link for updating from envato.
		 */
		public function addUpgradeMessageLink() {
			//$is_activated = isActivated();
			//if ( ! $is_activated ) {
				$url = esc_url( $this->getUpdaterUrl() );
				$redirect = sprintf( '<a href="%s" target="_blank">%s</a>', $url, __( 'settings', 'woozone' ) );
	
				echo sprintf( ' ' . __( 'To receive automatic updates license activation is required. Please visit %s to activate your product.', 'woozone' ), $redirect );
			//}
		}
	}
}