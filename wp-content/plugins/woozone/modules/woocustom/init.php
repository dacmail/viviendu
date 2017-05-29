<?php
/*
* Define class WooZoneWooCustom
* Make sure you skip down to the end of this file, as there are a few
* lines of code that are very important.
*/
!defined('ABSPATH') and exit;
if (class_exists('WooZoneWooCustom') != true) {
    class WooZoneWooCustom
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
		
		//custom attributes
		private $plugin_settings = array();
        
        public $is_admin = false;
        
        private $WooZonePriceSelect = null;


        /*
        * Required __construct() function that initalizes the AA-Team Framework
        */
        public function __construct()
        {
        	global $WooZone;

        	$this->the_plugin = $WooZone;
			$this->module_folder = $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'modules/woocustom/';
			$this->module = $this->the_plugin->cfg['modules']['woocustom'];
 
            $this->is_admin = $this->the_plugin->is_admin;

			$this->init();
        }
        
        /**
         * Head Filters & Init!
         *
         */
        
		public function init() {
            if ( $this->is_admin ) {
                // adminheader
                add_action( 'admin_head', array( $this, 'admin_make_head' ), 1 );
                
                // admin footer
                add_action( 'admin_footer', array( &$this, 'admin_make_footer' ), 1 );
            }
            
            if ( $this->is_admin ) {
                add_action( 'WooZone_admin_header', array($this, 'admin_custom_fields_header'), 0 );
                add_action( 'WooZone_admin_footer', array($this, 'admin_custom_fields_footer'), 31 );

                // adding custom product info on the edit product page, the general tab section of the WooCommerce, underneath the price fields
                add_action( 'WooZone_admin_footer', array($this, 'admin_edit_metabox_footer'), 30 );
                add_action( 'woocommerce_product_options_sku', array( $this, 'admin_edit_metabox' ) );

                // adding custom product info on the product listing page
                $screens = array('product');
                foreach ($screens as $screen) {
                    add_filter( 'manage_edit-' . $screen . '_columns', array( &$this, 'admin_prodlist_edit_columns' ), 10, 1 );
                    //add_filter( 'manage_' . $screen . '_posts_columns', array( $this, 'admin_prodlist_edit_columns' ), 10, 1 );
                    add_action( 'manage_' . $screen . '_posts_custom_column', array( $this, 'admin_prodlist_posts_columns' ), 10, 2 );
                }

				// woocommerce fix thumb for remote images with https - on product listing page - admin product listing wp-admin/edit.php?post_type=product
				add_action( 'manage_product_posts_custom_column', array( $this, 'render_product_columns' ), 999 );
				add_filter( 'manage_product_posts_columns', array( $this, 'product_columns' ), 999 );

                // try to get the price_select module
                if ( in_array('price_select', $this->the_plugin->cfg['core-modules'])
                    || $this->the_plugin->verify_module_status( 'price_select' ) ) {
                    require_once( $this->the_plugin->cfg['modules']['price_select']['folder_path'] . 'init.php');
                    $this->WooZonePriceSelect = WooZonePriceSelect::getInstance();
                }
            }

            add_filter( 'woocommerce_get_catalog_ordering_args', array( $this, 'get_catalog_ordering_args') );
            add_filter( 'woocommerce_default_catalog_orderby_options', array( $this, 'catalog_orderby') );
            add_filter( 'woocommerce_catalog_orderby', array( $this, 'catalog_orderby') );
		}
        
        
        /**
         * Admin Header & Footer hooks!
         */
        public function admin_make_head() {
            $details = array('plugin_name' => 'WooZone');

            if ( !has_action('WooZone_admin_header') )
                return true;
   
            ob_start();
        ?>
            <!-- start/ admin header/ <?php echo $details['plugin_name']; ?> -->
        <?php
            do_action( 'WooZone_admin_header' );
        ?>
            <!-- end/ admin header/ <?php echo $details['plugin_name']; ?> -->
        <?php
            $contents = ob_get_clean();
            echo $contents;
            return true;
        }
        
        public function admin_make_footer() {
            $details = array('plugin_name' => 'WooZone');
            
            if ( !has_action('WooZone_admin_footer') )
                return true;

            ob_start();
        ?>
            <!-- start/ admin footer/ <?php echo $details['plugin_name']; ?> -->
        <?php
            do_action( 'WooZone_admin_footer' );
        ?>
            <!-- end/ admin footer/ <?php echo $details['plugin_name']; ?> -->
        <?php
            $contents = ob_get_clean();
            echo $contents;
            return true;
        }


        /**
         * custom fields header & footer / css & js files
         */
        public function admin_custom_fields_header() {
            ob_start();
        ?>
            <link rel='stylesheet' href='<?php echo $this->module_folder; ?>app.woocustom.css' type='text/css' media='all' />
        <?php
            $contents = ob_get_clean();
            
            $contents .= $this->WooZonePriceSelect->css_page_list();

            echo $contents;
        }

        public function admin_custom_fields_footer() {
            ob_start();
        ?>
            <script type="text/javascript" src="<?php echo $this->module_folder; ?>app.woocustom.js"></script>
        <?php
            $contents = ob_get_clean();
            echo $contents;
        }
        

        /**
         * edit product page & listing products - add ASIN & amazon product URL fields
         */
        public function admin_edit_metabox() {
            global $post;
            $post_id = isset($post->ID) ? (int) $post->ID : 0;
            
            if ( $post_id <= 0 ) return ;
            $asin = (string) get_post_meta( $post_id, '_amzASIN', true);
            
            // no asin => not an amazon product!
            if ( empty($asin) ) return ;

            woocommerce_wp_text_input( array( 'id' => 'WooZone_asin', 'class' => 'wc_input_url short', 'label' => __( 'Amazon ASIN', 'woocommerce' ), 'value' => $asin, 'data_type' => 'price', 'custom_attributes' => array('readonly' => 'readonly', 'disabled' => 'disabled', 'style' => 'color: green; font-weight: bold;'), 'style' => 'color: green; font-weight: bold;' ) );
        }
        
        public function admin_edit_metabox_footer( $post_id = 0 ) {
            $req = array(
                'is_post_edit'      => isset($_REQUEST['post']) ? true : false,
                'post_id'           => isset($_REQUEST['post']) ? (int) $_REQUEST['post'] : $post_id,
            );
            extract($req);

            $arrProducts = array();

            if ( empty($post_id) ) return;

            $isProdValid = $this->the_plugin->verify_product_is_amazon_valid($post_id);
            if ( empty($isProdValid) ) return;
            
            $arrProducts[0] = $post_id;
            
            $isProdVariation = $this->the_plugin->verify_product_isvariation($post_id);
            if ( $isProdVariation ) {
                $arrProducts = array_merge( $arrProducts, $this->the_plugin->get_product_variations($post_id) );
                
                //if ( empty($arrProducts) ) return;
            };
  
            $post_id_orig = $post_id;
            $html = array();
            foreach ( $arrProducts as $post_id ) { // products loop!
  
                $asin = (string) get_post_meta( $post_id, '_amzASIN', true);
                $prod_url = $this->the_plugin->_product_buy_url( $post_id, $asin );
            
                // start html
                $html[] = '<div class="WooZoneWoocustomFields" data-post_id="' . ($post_id) . '" data-asin="' . ($asin) . '" style="display: none;">';

                //$attr = array(
                //    'cssClass'          => "WooZone-price-$priceType-$metaVal",
                //    'name'              => "WooZone-price[$post_id][$priceType][$metaVal]",
                //    'value'             => $_value,
                //);
                //$html[] = '<input type="hidden" class="'.$attr['cssClass'].'" name="'.$attr['name'].'" value="'.$attr['value'].'" />';
                $html[] = '<a href="' . $prod_url . '" class="button button-primary button-large" target="_blank">' . __('View Product Amazon page', 'WooZone') . '</a>';

                $html[] = '</div>';
                // end html
            
            } // end // products loop!
            if ( empty($html) ) return;
            
            $ret = implode( PHP_EOL, $html );
            if ( $is_post_edit ) {
                echo $ret;
            } else {
                return $ret;
            }
        }
        
        public function admin_prodlist_edit_columns($columns) {
            $new_columns['WooZone_product_info'] = __('WooZone Info', $this->the_plugin->localizationName);
        
            //return $new_columns;
            return array_merge( $columns, $new_columns );
        }
        
        public function admin_prodlist_posts_columns($column_name, $id) {
            global $id, $wpdb;

            $post_id = $id;

            switch ($column_name) {

                case 'WooZone_product_info':

                    if ( empty($post_id) ) break;

                    $isProdValid = $this->the_plugin->verify_product_is_amazon_valid($post_id);
                    if ( empty($isProdValid) ) break;

                    $arrProducts = array();
                    $isProdVariation = $this->the_plugin->verify_product_isvariation($post_id);
                    if ( $isProdVariation ) {
                        $arrProducts = array_merge( $arrProducts, $this->the_plugin->get_product_variations($post_id) );
                        //if ( empty($arrProducts) ) return;
                    }

                    // product (parent in case of variations)
                    $asin = (string) get_post_meta( $post_id, '_amzASIN', true);
                    $prod_url = $this->the_plugin->_product_buy_url( $post_id, $asin );

                    ob_start();
        ?>
                    <div class="WooZoneWoocustomFields" data-post_id="<?php echo $post_id; ?>" data-asin="<?php echo $asin; ?>" style="">
                        <span title="<?php _e('Amazon ASIN', $this->the_plugin->localizationName); ?>"><?php echo $asin; ?></span>
                        <a href="<?php echo $prod_url; ?>" target="_blank" title="<?php _e('View Product Amazon page', $this->the_plugin->localizationName); ?>"><i class="fa fa-lg fa-external-link"></i></a>
                        <?php if ( $isProdVariation ) { ?>
                        <span title="<?php _e('variations number for this product', $this->the_plugin->localizationName); ?>">(<?php echo count($arrProducts); ?>)</span>
                        <?php } ?>
                    </div>
        <?php
                    $html[] = ob_get_contents();
                    ob_end_clean();
                    
                    /*
                    $post_id_orig = $post_id;
                    foreach ( $arrProducts as $post_id ) { // products loop!
          
                        $asin = (string) get_post_meta( $post_id, '_amzASIN', true);
                        $prod_url = $this->the_plugin->_product_buy_url( $post_id, $asin );
                    
                        // start html
                        $html[] = '<div class="WooZoneWoocustomFields" data-post_id="' . ($post_id) . '" data-asin="' . ($asin) . '" style="display: none;">';
        
                        //$attr = array(
                        //    'cssClass'          => "WooZone-price-$priceType-$metaVal",
                        //    'name'              => "WooZone-price[$post_id][$priceType][$metaVal]",
                        //    'value'             => $_value,
                        //);
                        //$html[] = '<input type="hidden" class="'.$attr['cssClass'].'" name="'.$attr['name'].'" value="'.$attr['value'].'" />';
                        $html[] = '<a href="' . $prod_url . '" class="button button-primary button-large" target="_blank">' . __('View Product Amazon page', 'WooZone') . '</a>';
        
                        $html[] = '</div>';
                        // end html
                    
                    } // end // products loop!
                    */
                    
                    // price_select module
                    $html[] = $this->WooZonePriceSelect->get_post_column($post_id);
                    
                    echo implode(PHP_EOL, $html);
                    break;
                    
                default:
                    break;
            } // end switch
        }


        /**
         * Others 
         */
        public function get_catalog_ordering_args( $args )
		{
          $orderby_value = isset( $_GET['orderby'] )
		  	? ( function_exists('wc_clean') ? wc_clean( $_GET['orderby'] ) : woocommerce_clean( $_GET['orderby'] ) )
			: apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );
        
            if ( 'sales_rank' == $orderby_value ) {
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'ASC';
                $args['meta_key'] = '_sales_rank';
            }
			
            return $args;
        }

        public function catalog_orderby( $sortby ) 
		{
            $sortby['sales_rank'] = __('Sort by Sales Rank', 'WooZone');
            return $sortby;
        }


		/**
		 * woocommerce fix thumb for remote images with https - on product listing page - admin product listing wp-admin/edit.php?post_type=product
		 */
		public function product_columns( $existing_columns ) {
			$old_key = 'thumb'; $new_key = 'thumb_woozone';

			if ( empty( $existing_columns ) && ! is_array( $existing_columns ) ) {
				$existing_columns = array();
			}
	
			$keys = array_keys($existing_columns);
			if ( false !== ($index = array_search($old_key, $keys)) ) {
				$keys[$index] = $new_key;
				$existing_columns = array_combine($keys, array_values($existing_columns));
			}

			//var_dump('<pre>',$existing_columns,'</pre>');
			return $existing_columns;
		}

		public function render_product_columns( $column ) {
			if ( 'thumb_woozone' == $column ) {
				global $post, $the_product;

				if ( ! empty( $the_product ) && is_object($the_product) ) {
					if ( method_exists( $the_product, 'get_id' ) ) {
						$prod_id = (int) $the_product->get_id();
					} else if ( isset($the_product->id) && (int) $the_product->id > 0 ) {
						$prod_id = (int) $the_product->id;
					}
				}
				if ( empty( $the_product ) || $prod_id != $post->ID ) {
					$the_product = wc_get_product( $post );
				}

				echo '<a href="' . get_edit_post_link( $post->ID ) . '">' . $this->product_get_image( $the_product, 'thumbnail' ) . '</a>';
			}
		}
		
		public function product_get_image( $product, $size = 'shop_thumbnail', $attr = array(), $placeholder = true ) {
			$prod_id = 0;
			if ( is_object($product) ) {
				if ( method_exists( $product, 'get_id' ) ) {
					$prod_id = (int) $product->get_id();
				} else if ( isset($product->id) && (int) $product->id > 0 ) {
					$prod_id = (int) $product->id;
				}
			}

			if ( has_post_thumbnail( $prod_id ) ) {
				$image = get_the_post_thumbnail( $prod_id, $size, $attr );
			} elseif ( ( $parent_id = wp_get_post_parent_id( $prod_id ) ) && has_post_thumbnail( $parent_id ) ) {
				$image = get_the_post_thumbnail( $parent_id, $size, $attr );
			} elseif ( $placeholder ) {
				$image = wc_placeholder_img( $size );
			} else {
				$image = '';
			}
			
			// NOT an woozone product
			if ( ! $this->the_plugin->verify_product_is_amazon_valid($product) ) {
				return str_replace( array( 'https://', 'http://' ), '//', $image );
			}
			// IS woozone product
			return $image;
		}

	
		/**
	    * Singleton pattern
	    *
	    * @return WooZoneWooCustom Singleton instance
	    */
	    static public function getInstance()
	    {
	        if (!self::$_instance) {
	            self::$_instance = new self;
	        }

	        return self::$_instance;
	    }
		
    }
}

//$WooZoneWooCustom = new WooZoneWooCustom();
$WooZoneWooCustom = WooZoneWooCustom::getInstance();