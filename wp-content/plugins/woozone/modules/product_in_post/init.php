<?php
/*
* Define class WooZoneProductInPost
* Make sure you skip down to the end of this file, as there are a few
* lines of code that are very important.
*/
!defined('ABSPATH') and exit;

if (class_exists('WooZoneProductInPost') != true) {
    class WooZoneProductInPost
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
		
		private $amz_settings;

        /*
        * Required __construct() function that initalizes the AA-Team Framework
        */
        public function __construct()
        {
        	global $WooZone;

        	$this->the_plugin = $WooZone;
			$this->module_folder = $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'modules/product_in_post/';
			$this->module = $this->the_plugin->cfg['modules']['product_in_post'];
			
			$this->amz_settings = $this->the_plugin->getAllSettings('array', 'amazon');
  			 
			if (is_admin()) {
	            add_action( 'admin_head', array( $this, 'add_tinymce_button' ) );
				add_action( 'admin_footer', array( $this, 'add_product_inline' ) );
			}
			
			add_action('wp_ajax_WooZoneProductInPost', array( $this, 'ajax_request' ));
			
			add_shortcode( 'WooZoneProducts', array( $this, 'shortcode' ) );
			add_shortcode( 'wwcAmzAffProducts', array( $this, 'shortcode' ) ); // fix for old plugin versions (previous to 9.0) which used wwcAmzAff prefix
			
			add_action( 'wp_enqueue_scripts', array( $this, 'products_styles' ) );
			
			add_action('init', array( $this, 'thickbox' ));
        }
		
		public function products_styles()
		{
			wp_enqueue_style( 'WooZoneProducts', plugins_url('style.css', __FILE__) );
			
			$extra_css = isset($this->amz_settings['productinpost_extra_css'])
				&& '' != trim($this->amz_settings['productinpost_extra_css']) ? true : false;
			if ( $extra_css ) {
 				wp_add_inline_style( 'WooZoneProducts', $this->amz_settings['productinpost_extra_css'] );
			}
		}
		
		public function thickbox() {
			if (! is_admin()) {
				wp_enqueue_script('thickbox', null,  array('jquery'));
				wp_enqueue_style('thickbox.css',  '/' . WPINC . '/js/thickbox/thickbox.css', null, '1.0');
			}
		}
		
		public function add_tinymce_button() 
		{
		    global $typenow;
 
		    // only on Post Type: post and page
		    if( ! in_array( $typenow, array( 'post', 'page' ) ) )
		        return ;
		
		    add_filter( 'mce_external_plugins', array( $this, 'fb_add_tinymce_plugin' ) );
			
		    // Add to line 1 form WP TinyMCE
		    add_filter( 'mce_buttons', array( $this, 'fb_add_tinymce_button' ) );
		}
		
		// inline content for woo amazon add new products
		public function add_product_inline()
		{
			$html = array();
			
			$html[] = '<div class="add_product_inline" id="WooZoneAddProductInline">';
			$html[] = 	'<div>';
			$html[] = 		$this->product_in_page();
			$html[] = 	'</div>';
			$html[] = '</div>';
			
			echo implode( "\n", $html );
		}
		
		// inlcude the js for tinymce
		public function fb_add_tinymce_plugin( $plugin_array ) 
		{
		
		    $plugin_array['product_in_post'] = plugins_url( '/app.product_in_post.js', __FILE__ );
		    
		    // Print all plugin js path
		    //var_dump('<pre>',$plugin_array,'</pre>'); die;  
		    return $plugin_array;
		}
		
		// Add the button key for address via JS
		public function fb_add_tinymce_button( $buttons ) 
		{
		
		    array_push( $buttons, 'product_in_post' );
			 
		    // Print all buttons
		    //var_dump( $buttons );
		    return $buttons;
		}
		
		public function product_in_page()
		{
			$html = array();
			
			$html[] = '<div id="WooZoneAddProduct">';
			//$html[] = 	'<h2>Add Amazon Product(s)<h2>';
			/*$html[] = 		'<ul class="WooZoneChooseMenu">';
			$html[] = 			'<li><a href="#" rel="WooZoneAddAsinsCode" class="on">Based on ASIN code(s)</a></li>';
			$html[] = 			'<li><a href="#" rel="WooZoneAddImportedProducts">Imported Products</a></li>';
			$html[] = 		'</ul>';
			
			$html[] = 		'<div id="WooZoneAddAsinsCode">';
			$html[] = 			'<label>ASIN(s)</label>';
			$html[] = 			'<input type="text" class="WooZoneAddCode" id="WooZoneAddCode" />';
			$html[] = 			'<input type="submit" value="Add Products" class="button button-primary button-large" />';
			$html[] = 			'<p class="note">Amazon ASIN(s) - separate multiple with a comma. E.g: B00ISSD6QG, B00ISSD6Q1, B00ISSD6QG</p>';
			$html[] = 		'</div>';
			*/
			
			$html[] = 		'<div id="WooZoneAddImportedProducts"></div>';
			
			$html[] = '</div>';
			
			return implode( "\n", $html );
		}
		
		public function ajax_request()
		{
			$html = array();
			
			$products = $this->getAllPublishProducts();
			$html[] = '<div id="WooZoneListOfProducts">';
			$html[] = 	'<div class="WooZoneAllProducts">';
			$html[] = 		'<ul class="list-of-products">';
			
			foreach ($products as $product) {
			
				$html[] = 		'<li>
					<a href="#" data-postid="' . ( $product['ID'] ) . '" id="list-product-' . ( $product['ID'] ) . '">
						<span class="product-post-image">' . ( $product['thumb'] ) . '</span>
						<h2><span>' . ( $product['title'] ) . '</span></h2>
						<h3>' . ( $product['asin'] ) . '</h3>
					</a>
				</li>';
			}
			$html[] = 		'</ul>';
			$html[] = 	'</div>';
			$html[] = 	'<div class="WooZoneChosenProducts">
				<h4>' . ( __( 'Chosen Product(s)', $this->the_plugin->localizationName ) ) . ':</h4>
				<ul class="WooZoneChosenProducts-list">
					<li class="product-note">' . ( __( 'Please first select a product from the left side', $this->the_plugin->localizationName ) ) . '.</li>
				</ul>
				
				<input type="submit" value="' . ( __( 'Add Products', $this->the_plugin->localizationName ) ) . '" class="button button-primary button-large" />';
			$html[] = '</div>';
			
			$html[] = '<script>
				WooZoneProductInPost.trigger_select_products(); 
			</script>';
			
			die( json_encode(array(
				'status' => 'valid',
				'html'	=> implode( "\n", $html )
			)) );
			
		}

		final protected function getAllPublishProducts()
		{
		
			$ret = array();
			$args = array();
			$args['post_type'] = 'product';
	
			$args['meta_key'] = '_amzASIN';
			$args['meta_value'] = '';
			$args['meta_compare'] = '!=';
	
			// show all posts
			$args['fields'] = 'ids';
			$args['posts_per_page'] = '-1';
			
			$loop = new WP_Query( $args );
			$cc = 0;
			$html = array(); 
			while ( $loop->have_posts() ) : $loop->the_post();
				global $post;
				
				$asin = get_post_meta( $post, '_amzASIN', true );
				$ret[] = array(  
					'ID' => $post,
					'title' => get_the_title(),
					'asin' => $asin,
					'thumb' => get_the_post_thumbnail( $post, array(100, 100) ),
					'fullimage' => wp_get_attachment_image_src( get_post_thumbnail_id( $post ), 'single-post-thumbnail' )
				);
				
			endwhile;
			return $ret;
		}

		public function shortcode( $atts )
		{
			//$p_type = ( isset($this->amz_settings['onsite_cart']) && 'no' == $this->amz_settings['onsite_cart'] ? 'external' : 'simple' );

			$html = array();
			$products = array();
			
			$atts = shortcode_atts( array(
		 	    'add_to_cart' => false,
		 	    'asin' => '',
		 	    'gallery' => true
		    ), $atts, 'WooZoneProducts' );
	  		
			$atts['gallery'] = ($atts['gallery'] == 'true' ? true : false);
			
			if( trim($atts['asin']) != "" ){
				$products = $this->getProducts( $atts['asin'], $atts['gallery'] );
			}
			
			$html[] = '<div class="wb-box wb-multiple-products">';
			foreach ($products as $key => $product) {
				
				$prod_link = home_url('/?redirectAmzASIN=' . $product['asin'] );
				// product buy url is the original amazon url!
				if( (!isset($this->amz_settings['product_buy_is_amazon_url'])
					    || (isset($this->amz_settings['product_buy_is_amazon_url'])
	                    && $this->amz_settings['product_buy_is_amazon_url'] == 'yes')
	                )
	                //&& ( 'external' == $p_type )
	            ) {
	            	$prod_link = $this->the_plugin->_product_buy_url( $product['ID'], $product['asin'] );
				}
				
				//var_dump('<pre>',$product,'</pre>'); die;  
				$html[] = '<div class="wb-product">
					<div class="wb-left">
						<div class="wb-prod-image">
							<a href="' . ( $product['fullimage'][0] ) . '" class="thickbox">' . ( $product['thumb'] ) . '</a>
							<a href="' . ( $product['fullimage'][0] ) . '" class="thickbox">' . __('See larger image', $this->the_plugin->localizationName) . '</a>
						</div>
					</div>
					<div class="wb-right">
						<a href="' . ( $prod_link ) . '" target="_blank"><h2>' . ( $product['title'] ) . '</h2></a>
						<div class="wb-description">
							' . ( wpautop( $product['post_excerpt'] ) ) . '
						</div>';
				
				$show_additional_img = isset($this->amz_settings['productinpost_additional_images'])
					&& 'no' == $this->amz_settings['productinpost_additional_images'] ? false : true;

				if( $atts['gallery'] == true && count($product['images']) > 0 && $show_additional_img ){

					$html[] = '		
						<div class="wb-aditional-images">
							<p>' . __('Additional images:', $this->the_plugin->localizationName) . '</p>';
					
					foreach( $product['images'] as $image ){ 
						$html[] = '<a rel="gallery-' . ( sanitize_title($product['title']) ) . '" href="' . ( $image['full'] ) . '" class="thickbox"><img src="' . ( $image['small'] ) . '" alt="Product Thumbnail"></a>';
					}
					
					$html[] = '</div>';
				}

				$html[] = '
						<div class="wb-price">
							<p><span>' . __('Price:', $this->the_plugin->localizationName) . '</span> ' . ( $product['price'] ) . '</p>
						</div>
						<a href="' . ( $prod_link ) . '" class="wb-buy" target="_blank">' . __('Buy Now', $this->the_plugin->localizationName) . '</a>
					</div>
				</div>'; 
			}
			
			
			$html[] = '</div>'; 
			$html[] = ''; 
			
			return implode( "\n", $html );
		}
		
		public function getProducts( $asins='', $gallery=false )
		{
			$asins = explode( ",", $asins ); 
			$asins = array_map( 'trim', $asins );
			
			$ret = array();
			// check if products exists into DB
			$args = array();
			$args['post_type'] = 'product';
	
			$args['meta_key'] = '_amzASIN';
			$args['meta_value'] = implode(", ", $asins);
			$args['meta_compare'] = 'IN';
	
			// show all posts
			$args['fields'] = 'ids';
			$args['posts_per_page'] = '-1';
			
			$loop = new WP_Query( $args );
			
			while ( $loop->have_posts() ) : $loop->the_post();
				global $post;
				
				$product = new WC_Product( $post );
				
				
	 			
				$asin = get_post_meta( $post, '_amzASIN', true );
				$ret[$asin] = array(  
					'ID' => $post,
					'price' => $product->get_price_html(),
					'title' => get_the_title(),
					'post_excerpt' => get_the_excerpt(),
					'asin' => $asin,
					'thumb' => get_the_post_thumbnail( $post, array(100, 100) ),
					'fullimage' => wp_get_attachment_image_src( get_post_thumbnail_id( $post ), 'single-post-thumbnail' )
				);
				
				if( $gallery == true ){
					$args = array(
					   'post_type' => 'attachment',
					   'numberposts' => -1,
					   'post_status' => null,
					   'post_parent' => $post
					);
					$attachments = get_posts( $args );
					$images = array();
				    if ( $attachments ) {
				    	$cc = 0;
				        foreach ( $attachments as $attachment ) {
				        	$_ = wp_get_attachment_image_src( $attachment->ID, 'thumbnail' );
				        	$__ = wp_get_attachment_image_src( $attachment->ID, 'full' );
				        	$images[$cc]['small'] = $_[0];
							$images[$cc]['full'] = $__[0];
							
							$cc++;
				        }
				    }
					
					$ret[$asin]['images'] = $images;
				}
				
			endwhile;
			  
			wp_reset_query();
			
			$base = array();
			foreach ($asins as $asin) {
				if( in_array($asin, array_keys($ret)) ){
					$base[$asin] = $ret[$asin];
				}
			}
			
			return $base;
		}

		/**
	    * Singleton pattern
	    *
	    * @return WooZoneProductInPost Singleton instance
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
    }
}

// Initialize the WooZoneProductInPost class
$WooZoneProductInPost = WooZoneProductInPost::getInstance();