<?php
/*
* Define class WooZoneStatsProd
* Make sure you skip down to the end of this file, as there are a few
* lines of code that are very important.
*/
!defined('ABSPATH') and exit;
      
if (class_exists('WooZoneStatsProd') != true) {
    class WooZoneStatsProd
    {
        /*
        * Some required plugin information
        */
        const VERSION = '1.0';

        /*
        * Store some helpers config
        */
		public $the_plugin = null;

		private $module_folder = '';
		private $module = '';

		static protected $_instance;

        /*
        * Required __construct() function that initalizes the AA-Team Framework
        */
        public function __construct()
        {
        	global $WooZone;

        	$this->the_plugin = $WooZone;
			$this->module_folder = $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'modules/stats_prod/';
			$this->module = $this->the_plugin->cfg['modules']['stats_prod'];
   
			if (is_admin()) {
	            add_action('admin_menu', array( &$this, 'adminMenu' ));
			}

			if ( $this->the_plugin->is_admin !== true ) {
				$this->addFrontFilters();
			}
        }

		/**
	    * Singleton pattern
	    *
	    * @return WooZoneStatsProd Singleton instance
	    */
	    static public function getInstance()
	    {
	        if (!self::$_instance) {
	            self::$_instance = new self;
	        }

	        return self::$_instance;
	    }

		/**
	    * Hooks
	    */
	    static public function adminMenu()
	    {
	       self::getInstance()
	    		->_registerAdminPages();
	    }

	    /**
	    * Register plug-in module admin pages and menus
	    */
		protected function _registerAdminPages()
    	{
    		add_submenu_page(
    			$this->the_plugin->alias,
    			$this->the_plugin->alias . " " . __('Products Stats', $this->the_plugin->localizationName),
	            __('Products Stats', $this->the_plugin->localizationName),
	            'manage_options',
	            $this->the_plugin->alias . "_stats_prod",
	            array($this, 'display_index_page')
	        );

			return $this;
		}

		public function display_index_page()
		{
			$this->printBaseInterface();
		}
		
		/**
		 * frontend methods: update hits & add to cart for amazon product!
		 *
		 */
		public function addFrontFilters() {
			add_action('wp', array( $this, 'frontend' ), 0);
			
			add_action('woocommerce_add_to_cart', array($this, 'add_to_cart'), 1, 6); // add item to cart
			add_action('wp_ajax_woocommerce_add_to_cart', array($this, 'add_to_cart_ajax'), 0);
			add_action('wp_ajax_nopriv_woocommerce_add_to_cart', array($this, 'add_to_cart_ajax'), 0);
		}

		public function frontend() {
			global $wpdb, $wp;
   
			// $currentUri = home_url(add_query_arg(array(), $wp->request));

			if ( !is_admin() /*&& is_singular()*/ ) {
				global $post;
				$post_id = is_object($post) && isset($post->ID) ? (int)$post->ID : 0;
				if ( empty($post_id) ) return;
  
				// verify if it's an woocommerce amazon product!
				if ( $post_id <= 0 || !$this->the_plugin->verify_product_is_amazon($post_id) )
					return false;
   
				// update hits
				$hits = (int) get_post_meta($post_id, '_amzaff_hits', true);
				update_post_meta($post_id, '_amzaff_hits', (int)($hits+1));
                
                $hits2 = (int) get_post_meta($post_id, '_amzaff_hits_prev', true);
                update_post_meta($post_id, '_amzaff_hits_prev', (int)($hits2+1));
			}
		}

		public function add_to_cart_validation( $passed, $product_id, $quantity, $variation_id='', $variations='' ) {
			if ( !is_admin() ) {
				$post_id = $product_id;

				// verify if it's an woocommerce amazon product!
				if ( $post_id <= 0 || !$this->the_plugin->verify_product_is_amazon($post_id) )
					return false;
				
				$addtocart = (int) get_post_meta($post_id, '_amzaff_addtocart', true);
				update_post_meta($post_id, '_amzaff_addtocart', (int)($addtocart+1));
                
                $addtocart2 = (int) get_post_meta($post_id, '_amzaff_addtocart_prev', true);
                update_post_meta($post_id, '_amzaff_addtocart_prev', (int)($addtocart2+1));
				
				return true;
			}
		}
		
		public function add_to_cart_ajax() {
			global $woocommerce;
  
			check_ajax_referer( 'add-to-cart', 'security' );
			
			$product_id = (int) apply_filters('woocommerce_add_to_cart_product_id', $_POST['product_id']);
			
			$passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, 1);
			
			if ($passed_validation && $woocommerce->cart->add_to_cart($product_id, 1)) :
				// Return html fragments
				//$data = apply_filters('add_to_cart_fragments', array()); //deprecated as of version 2.3
				$data = apply_filters('woocommerce_add_to_cart_fragments', array());
				
				$post_id = $product_id;

				// verify if it's an woocommerce amazon product!
				if ( $post_id <= 0 || !$this->the_plugin->verify_product_is_amazon($post_id) )
					return false;
				
				$addtocart = (int) get_post_meta($post_id, '_amzaff_addtocart', true);
				update_post_meta($post_id, '_amzaff_addtocart', (int)($addtocart+1));
                
                $addtocart2 = (int) get_post_meta($post_id, '_amzaff_addtocart_prev', true);
                update_post_meta($post_id, '_amzaff_addtocart_prev', (int)($addtocart2+1));
			else :
				// If there was an error adding to the cart, redirect to the product page to show any errors
				$data = array(
					'error' => true,
					'product_url' => get_permalink( $product_id )
				);
				$woocommerce->set_messages();
			endif;
			
			echo json_encode( $data );
			
			die();
		}
		
		public function add_to_cart( $cart_item_key='', $product_id='', $quantity='', $variation_id='', $variation='', $cart_item_data='' ) {
  
			if ( !is_admin() ) {
				$post_id = $product_id;

				// verify if it's an woocommerce amazon product!
				if ( $post_id <= 0 || !$this->the_plugin->verify_product_is_amazon($post_id) )
					return false;
				
				$addtocart = (int) get_post_meta($post_id, '_amzaff_addtocart', true);
				update_post_meta($post_id, '_amzaff_addtocart', (int)($addtocart+1));
                
                $addtocart2 = (int) get_post_meta($post_id, '_amzaff_addtocart_prev', true);
                update_post_meta($post_id, '_amzaff_addtocart_prev', (int)($addtocart2+1));

				return true;
			}
		}
		

		/*
		* printBaseInterface, method
		* --------------------------
		*
		* this will add the base DOM code for you options interface
		*/
		private function printBaseInterface()
		{
            // Initialize the WooZoneTailSyncMonitor class
            require_once( $this->the_plugin->cfg['paths']['plugin_dir_path'] . '/modules/synchronization/tail.php' );
            $syncTail = new WooZoneTailSyncMonitor($this->the_plugin);
            
            $syncTail->printBaseInterface( 'stats_prod' );
		}
    }
}
 
// Initialize the WooZoneStatsProd class
$WooZoneStatsProd = WooZoneStatsProd::getInstance();
