<?php
/*
* Define class WooZoneASINGrabber
* Make sure you skip down to the end of this file, as there are a few
* lines of code that are very important.
*/
!defined('ABSPATH') and exit;

if (class_exists('WooZoneASINGrabber') != true) {
    class WooZoneASINGrabber
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
		
		private $settings;

        /*
        * Required __construct() function that initalizes the AA-Team Framework
        */
        public function __construct()
        {
        	global $WooZone;

        	$this->the_plugin = $WooZone;
			$this->module_folder = $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'modules/asin_grabber/';
			$this->module = $this->the_plugin->cfg['modules']['asin_grabber'];
			
			$this->settings = $WooZone->getAllSettings('array', 'amazon');
  
			if (is_admin()) {
	            add_action('admin_menu', array( &$this, 'adminMenu' ));
			}
			
			add_action('wp_ajax_WooZone_grabb_asins', array( &$this, 'grabb_assins' ));
			
			$this->settings['page_types'] = array(
				'Best Sellers',
				//'Deals',
				'Top Rated',
				'Most Wished For',
				'Movers & Shakers',
				'Hot New Releases',
				//'Best Sellers Cattegory',
				//'Gift Ideas',
				//'New Arrivals',
			);
        }

		/**
	    * Singleton pattern
	    *
	    * @return WooZoneASINGrabber Singleton instance
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
    			$this->the_plugin->alias . " " . __('ASIN Grabber', $this->the_plugin->localizationName),
	            __('ASIN Grabber', $this->the_plugin->localizationName),
	            'manage_options',
	            $this->the_plugin->alias . "_asin_grabber",
	            array($this, 'display_index_page')
	        );

			return $this;
		}

		public function display_index_page()
		{
			$this->printBaseInterface();
		}
		
		/*
		* printBaseInterface, method
		* --------------------------
		*
		* this will add the base DOM code for you options interface
		*/
		private function printBaseInterface()
		{
			global $wpdb;
?>
		<link rel='stylesheet' href='<?php echo $this->module_folder;?>app.asin_grabber.css' type='text/css' media='all' />
		<div id="<?php echo WooZone()->alias?>" class="WooZone-asin-grabber">
			
			<div class="<?php echo WooZone()->alias?>-content"> 
				
				<?php
				// show the top menu
				WooZoneAdminMenu::getInstance()->make_active('import|asin_grabber')->show_menu();
				?>
				
				<!-- Content -->
				<section class="WooZone-main">
					
					<?php 
					echo WooZone()->print_section_header(
						$this->module['asin_grabber']['menu']['title'],
						$this->module['asin_grabber']['description'],
						$this->module['asin_grabber']['help']['url']
					);
					?>
					
					<div class="panel panel-default WooZone-panel WooZone-setup">
						
<?php
	if ( !WooZone()->can_import_products() ) {
		echo '<div class="panel-body WooZone-panel-body">';
		echo 	WooZone()->demo_products_import_end_html();
		echo '</div>';
	} else {
?>
					
						<div class="panel-heading WooZone-panel-heading">
							<h2><?php _e('ASIN Grabber', $this->the_plugin->localizationName);?></h2>
						</div>
						
						<div class="panel-body WooZone-panel-body">
								
		                    <?php
		                    // find if user makes the setup
		                    $moduleValidateStat = $this->moduleValidation();
		                    if ( !$moduleValidateStat['status'] || !is_object($this->the_plugin->amzHelper) || is_null($this->the_plugin->amzHelper) )
		                        echo $moduleValidateStat['html'];
		                    else {
		                    ?>
		
								<!-- Content Area -->
								<!--div id="WooZone-content-area">
									<div class="WooZone-grid_4">
			                        	<div class="WooZone-panel">
											<div class="WooZone-panel-content"-->
									            <form id="WooZone-grabb-asins" class="WooZone-form">

									            	<div class="panel-body WooZone-panel-body WooZone-form-row" style="border-top:0;">
														<label class="WooZone-form-label" for="protocol">Amazon URL:</label>
														<div class="WooZone-form-item">
															<input type="text" value="" name="WooZone[grabb-url]" placeholder="Paste the Amazon page URL here" />
															<span class="WooZone-form-note">The Amazon Page from where you want to import the ASIN codes. E.g: http://www.amazon.com/gp/top-rated</span>
														</div>
													</div>
													
													<div class="panel-body WooZone-panel-body WooZone-form-row">
														<label class="WooZone-form-label" for="protocol">Page type:</label>
														<div class="WooZone-form-item">
															<select name="WooZone[page-type]">
																<?php
																if( count($this->settings['page_types']) > 0 ){
																	foreach ($this->settings['page_types'] as $page) {
																		echo '<option value="' . ( strtolower( $page )) . '">' . ( $page ) . '</option>';
																	}
																}
																?>
															</select>
															<div style="display: none;" id="WooZone-filter-by-page-nr" style="float: left; width: inherit;">
																<label class="WooZone-form-label" style="margin-left: 100px; width: 130px;">Number of pages:</label>
																<select class="WooZone-number-of-results">
																	<option value="1">1</option>
																	<option value="2">2</option>
																	<option value="3">3</option>
																	<option value="4">4</option>
																	<option value="5">5</option>
																	<option value="0">Custom number of pages</option>
																</select>
																<div class="WooZoneCustomNrPages" style="float: left; width: inherit; margin-left: 29px; display: none;">
																	<span>OR:</span>
																	<input type="text" style="width: 120px; margin-left: 30px;" class="WooZone-custom-nr-pages" value="6" /> 
																</div>
															</div>
														</div>
													</div>
													
													<div class="panel-body WooZone-panel-body WooZone-form-row">
														<div class="WooZone-form-item" style="margin-left: 0px;">
															<input type="button" class="WooZone-form-button WooZone-form-button-info" id="WooZone-grabb-button" value="GET ASIN codes" style="width:132px">
														</div>
													</div>
						            			</form>
						            			
						            			<form id="WooZone-asin-codes" class="WooZone-form" style="display: none;">
						            				<div class="panel-body WooZone-panel-body WooZone-form-row">
														<label class="WooZone-form-label" for="protocol">ASIN codes:</label>
														<div class="WooZone-form-item">
															<textarea name="WooZone[asin-codes]" id="WooZone[asin-codes]"></textarea>
														</div>
													</div>
													
													<div class="panel-body WooZone-panel-body WooZone-form-row">
														<div class="WooZone-form-item" style="margin-left: 0px;">
															<input type="button" class="WooZone-form-button WooZone-form-button-info" id="WooZone-import-to-queue" value="Add ASIN codes to Import Queue" style="width:212px">
														</div>
													</div>
						            			</form>
						            		<!--/div>
										</div>
									</div>
									<div class="clear"></div>
									
								</div-->
		                    <?php
		                    } // end moduleValidation
		                    ?>
						</div>
						
<?php } // end demo keys ?>

					</div>
				</section>
			</div>
		</div>
		<script type="text/javascript" src="<?php echo $this->module_folder;?>app.asin_grabber.js" ></script>

<?php
		}

		public function moduleValidation() {
			$ret = array(
				'status'			=> false,
				'html'				=> ''
			);
			
			// AccessKeyID, SecretAccessKey, AffiliateId, main_aff_id
			
			// find if user makes the setup
			$module_settings = $this->the_plugin->getAllSettings('array', 'amazon');

			$module_mandatoryFields = array(
				'AccessKeyID'			=> false,
				'SecretAccessKey'		=> false,
				'main_aff_id'			=> false
			);
			if ( isset($module_settings['AccessKeyID']) && !empty($module_settings['AccessKeyID']) ) {
				$module_mandatoryFields['AccessKeyID'] = true;
			}
			if ( isset($module_settings['SecretAccessKey']) && !empty($module_settings['SecretAccessKey']) ) {
				$module_mandatoryFields['SecretAccessKey'] = true;
			}
			if ( isset($module_settings['main_aff_id']) && !empty($module_settings['main_aff_id']) ) {
				$module_mandatoryFields['main_aff_id'] = true;
			}
			$mandatoryValid = true;
			foreach ($module_mandatoryFields as $k=>$v) {
				if ( !$v ) {
					$mandatoryValid = false;
					break;
				}
			}
			if ( !$mandatoryValid ) {
				$error_number = 1; // from config.php / errors key
				
				$ret['html'] = $this->the_plugin->print_module_error( $this->module, $error_number, 'Error: Unable to use CSV Bulk Import module, yet!' );
				return $ret;
			}
			
			if( !$this->the_plugin->is_woocommerce_installed() ) {  
				$error_number = 2; // from config.php / errors key
				
				$ret['html'] = $this->the_plugin->print_module_error( $this->module, $error_number, 'Error: Unable to use Advanced Search module, yet!' );
				return $ret;
			}
			
			$db_protocol_setting = isset($this->settings['protocol']) ? $this->settings['protocol'] : 'auto';
			if( ( !extension_loaded('soap') && !class_exists("SOAPClient") && !class_exists("SOAP_Client") )
				&& in_array($db_protocol_setting, array('soap')) ) {
				$error_number = 3; // from config.php / errors key
				
				$ret['html'] = $this->the_plugin->print_module_error( $this->module, $error_number, 'Error: Unable to use Advanced Search module, yet!' );
				return $ret;
			}

			if( !(extension_loaded("curl") && function_exists('curl_init')) ) {  
				$error_number = 4; // from config.php / errors key
				
				$ret['html'] = $this->the_plugin->print_module_error( $this->module, $error_number, 'Error: Unable to use Advanced Search module, yet!' );
				return $ret;
			}
			
			$ret['status'] = true;
			return $ret;
		}


		/*
		* ajax_request, method
		* --------------------
		*
		* this will create requesto 
		*/
		public function grabb_assins()
		{
			$params = array();
			$base = array();
			$base['status'] = 'invalid';
			parse_str( $_REQUEST['params'], $params ); 
			
			$remote_url = $params['WooZone']['grabb-url'];
			$page_type = $params['WooZone']['page-type'];

			// https://www.amazon.com/Best-Sellers/zgbs
			if( trim($remote_url) != "" ){
				require_once( $this->the_plugin->cfg['paths']['scripts_dir_path'] . '/php-query/phpQuery.php' );
 
				$input = wp_remote_get( 
					$remote_url, 
					array( 'timeout' => 30 ) 
				);
				
				$response = wp_remote_retrieve_body( $input );
				$doc = WooZonephpQuery::newDocument( $response );

				// Best Sellers page type
				if( $page_type == 'best sellers' ){
					$container = $doc->find( '#zg_left_col1' );

					$asins = array();

					if (strpos($remote_url, 'ref=') !== false) {
						//$products = $container->find(".zg_itemImmersion .zg_itemWrapper .zg_image");
						$products = $container->find(".zg_itemImmersion .zg_itemWrapper"); 
					} else {
						//$products = $container->find(".zg_item .zg_image");
						$products = $container->find(".zg_item");
					}

					if( (int)$products->size() > 0 ){
						foreach ( $products as $product ) {
							//$product_url = trim(WooZonepq( $product )->find("a")->attr('href'));
							$product_url = trim(WooZonepq( $product )->find("a.a-link-normal:first")->attr('href'));
							if( $product_url != "" ){
								$product_url = @urldecode( $product_url );
								$__ = explode("/", $product_url );
								$__ = preg_replace('~\?.*~', '', $__);
								$asins[] = end( $__ );
							} 					
						} 
					}
				}
  
				// Deals page type
				elseif( $page_type == 'deals' ){
					$container = $doc->find( '#mainResults' );
					 
					$asins = array();

					if ($container->find( ".prod" ) != "") {
						$products = $container->find( ".prod" );
					} else {
							$products = $container->find( ".product" );
						}

					if( (int)$products->size() > 0 ){
						foreach ( $products as $product ) {
							$asin_item = WooZonepq( $product )->attr('name');     
							$asins[] = $asin_item; 					
						} 
					}
				}

				// Top Rated, Most Wished For, Movers & Shakers, Hot New Releases, Best Sellers Cattegory, Gift Ideas page type
				elseif( $page_type == 'top rated' || 'most wished for' || 'movers & shakers' || 'hot new releases' || 'best sellers cattegory' || 'gift ideas' ){
						$container = $doc->find( '#zg_left_col1' );
  
						$asins = array();

						if (strpos($remote_url, 'ref=') !== false) {
							//$products = $container->find(".zg_itemImmersion .zg_itemWrapper .zg_image");
							$products = $container->find(".zg_itemImmersion .zg_itemWrapper"); 
						} else {
							//$products = $container->find(".zg_item .zg_image");
							$products = $container->find(".zg_item");
						}
						if( (int)$products->size() > 0 ){
							foreach ( $products as $product ) {
								//$product_url = trim(WooZonepq( $product )->find("a")->attr('href'));
								$product_url = trim(WooZonepq( $product )->find("a.a-link-normal:first")->attr('href'));
								if( $product_url != "" ){
									$product_url = @urldecode( $product_url );
									$__ = explode("/", $product_url );
									$__ = preg_replace('~\?.*~', '', $__);
									$asins[] = end( $__ );
								} 					
							} 
						}
					}



				// New Arrivals page type
				if( $page_type == 'new arrivals' ){
					$container = $doc->find( '#resultsCol' );
					
					$asins = array();

					$products = $container->find(".prod .image");
					if( (int)$products->size() > 0 ){
						foreach ( $products as $product ) {
							$product_url = trim(WooZonepq( $product )->find("a")->attr('href'));
							if( $product_url != "" ){
								$product_url = @urldecode( $product_url );
								$__ = explode("/", $product_url );
								$asins[] = end( $__ );
							} 					
						} 
					}
				}

				$base['status'] = 'valid';

				if( count($asins) == 0 ){

					$base['status'] = 'invalid';
					$base['msg'] = 'The script was unable to grab any ASIN codes. Please try again using another Page Type parameter.';
				}
				$base['asins'] = $asins; 
			}
			
			die( json_encode( $base ) );
		}
    }
}

if ( !function_exists('WooZoneASINGrabber_cronjob') ) {
function WooZoneASINGrabber_cronjob() {
	// Initialize the WooZoneASINGrabber class
	$amzaffAssetDownload = new WooZoneASINGrabber();
	$amzaffAssetDownload->cronjob();
}
}

// Initialize the WooZoneASINGrabber class
$WooZoneASINGrabber = WooZoneASINGrabber::getInstance();