<?php
/*
* Define class Modules Manager List
* Make sure you skip down to the end of this file, as there are a few
* lines of code that are very important.
*/
! defined( 'ABSPATH' ) and exit;

if(class_exists('amazonDebug') != true) {

	class amazonDebug {
		/*
		* Some required plugin information
		*/
		const VERSION = '1.0';

		/*
		* Store some helpers config
		*
		*/
		public $cfg	= array();
		public $module	= array();
		public $networks	= array();
		public $the_plugin = null;
		
		/*
		http://docs.aws.amazon.com/AWSECommerceService/latest/DG/CHAP_ResponseGroupsList.html
		(function($) {
		  var $wrap = $('.informaltable');
		  
		  $wrap.find('li.listitem').each(function(i, el) {
		    var $this = $(el),
		        $alink = $this.find('a.link'),
		        val = $alink.text(),
		        href = $alink.attr('href');
		    
		    //href = 'http://docs.aws.amazon.com/AWSECommerceService/latest/DG/' + href;
		    
		    console.log( '\''+val+'\' => \''+href+'\',' );
		    
		  });
		}(jQuery));
		*/
		private static $ResponseGroups_baseurl = 'http://docs.aws.amazon.com/AWSECommerceService/latest/DG/';
		private static $ResponseGroups_parenturl = 'http://docs.aws.amazon.com/AWSECommerceService/latest/DG/CHAP_ResponseGroupsList.html';
		private static $ResponseGroups_default = array('Large', 'ItemAttributes', 'OfferFull', 'Variations', 'PromotionSummary');
		private static $ResponseGroups = array(
			'Accessories',
			'AlternateVersions',
			'BrowseNodeInfo',
			'BrowseNodes',
			'Cart',
			'CartNewReleases',
			'CartTopSellers',
			'CartSimilarities',
			'EditorialReview',
			'Images',
			'ItemAttributes',
			'ItemIds',
			'Large',
			'Medium',
			'MostGifted ',
			'MostWishedFor',
			'NewReleases',
			'OfferFull',
			'OfferListings',
			'Offers',
			'OfferSummary',
			'PromotionSummary',
			'RelatedItems',
			'Request',
			'Reviews',
			'SalesRank',
			'SearchBins',
			'Similarities',
			'Small',
			'TopSellers',
			'Tracks',
			'Variations',
			'VariationImages',
			'VariationMatrix',
			'VariationOffers',
			'VariationSummary',
		);
		private static $ResponseGroups_deprecated = array(
			"BrowseNodeInfo",
			"Cart",
			"CartNewReleases",
			"CartTopSellers",
			"CartSimilarities",
			"MostGifted ",
			"MostWishedFor ",
			"NewReleases",
			"OfferFull",
			"SearchBins",
			"TopSellers",
		);
		private static $ResponseGroups_new = array(
			"PromotionDetails",
			"VariationMinimum",
			"TagsSummary",
			"Tags",
			"MerchantItemAttributes",
			"Accessories",
			"Subjects",
			"ListmaniaLists",
			"SearchInside",
			"PromotionalTag",
			"Collections",
			"ShippingCharges",
			"ShippingOptions",
		);
		

		/*
		* Required __construct() function that initalizes the AA-Team Framework
		*/
		public function __construct($cfg, $module)
		{
			global $WooZone;
			
			$this->the_plugin = $WooZone;
			$this->cfg = $cfg;
			$this->module = $module;
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
			
			if( !extension_loaded('soap') ) {  
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

		public function printListInterface ()
		{
			global $WooZone;

			// find if user makes the setup
			$moduleValidateStat = $this->moduleValidation();
			if ( !$moduleValidateStat['status'] || !is_object($this->the_plugin->amzHelper) || is_null($this->the_plugin->amzHelper) )
				echo $moduleValidateStat['html'];
			else{
				
        	/*if ( !is_object($this->the_plugin->amzHelper) || is_null($this->the_plugin->amzHelper) ) {
        		$html = array();
        		$html[] = '<div class="WooZone-message blue">You need to set the Access Key ID, Secret Access Key and Your Affiliate IDs first!</div>';
				return implode('\n', $html);
        	} else {*/

			$amazon_settings = $WooZone->getAllSettings('array', 'amazon');
        		
			$html = array();
			$html[] = '<style type="text/css">#WooZone-amazonDebug { display: block } </style>';
			
			// hightlight.js
 			//$html[] = '<link rel="stylesheet" type="text/css" href="' . ( $this->module['folder_uri'] ) . 'lib/school_book.css" />';
 			//$html[] = '<script type="text/javascript" src="' . ( $this->module['folder_uri'] ) . 'lib/highlight.pack.js" ></script>';
			
			// collapsible
 			$html[] = '<link rel="stylesheet" type="text/css" href="' . ( $this->module['folder_uri'] ) . 'lib.collapsible/json.format.css" />';
 			$html[] = '<script type="text/javascript" src="' . ( $this->module['folder_uri'] ) . 'lib.collapsible/json.format.js" ></script>';

			$html[] = '<script type="text/javascript" src="' . ( $this->module['folder_uri'] ) . 'app.amazon_debug.js" ></script>';

			
			ob_start();
		?>
			<div id="WooZone-amazonDebug">
				<div>
					<div class="WooZone-amzdbg-ResponseGroups-Head">
						<span style="display: none;" id="WooZone-amzdbg-datael" alt="<?php echo $this->module['folder_uri'] . 'lib.collapsible/'; ?>"></span>
						<input type="hidden" id="WooZone-amzdbg-default" name="WooZone-amzdbg-default" value="<?php echo implode(',', self::$ResponseGroups_default); ?>" />
						<input id="WooZone-amzdbg-rg[all]" type="checkbox" name="WooZone-amzdbg-rg[all]" value="all" <?php echo count(self::$ResponseGroups) == count(self::$ResponseGroups_default) ? 'checked="checked" ' : ''; ?>/>
						<label for="WooZone-amzdbg-rg[all]">Check / Uncheck All</label>
						
						<a href="#" class="WooZone-form-button WooZone-form-button-success" id="WooZone-amzdbg-rg-godefault">Restore to default reponse groups</a>

						<a href="<?php echo self::$ResponseGroups_parenturl; ?>" class="WooZone-form-button WooZone-form-button-info WooZone-amz-docs-details" target="_blank">Amazon Available Response Groups</a>
						
					</div>
					<?php /*<div style="clear: both;"></div>*/ ?>
					<ul class="WooZone-amzdbg-ResponseGroups">
						<?php
							$ResponseGroups = self::$ResponseGroups;
							//$ResponseGroups = array_diff($ResponseGroups, self::$ResponseGroups_deprecated);
							//$ResponseGroups = array_merge($ResponseGroups, self::$ResponseGroups_new);

							foreach($ResponseGroups as $key) {
								$checked = in_array($key, self::$ResponseGroups_default);
						?>
							<li>
								<input id="WooZone-amzdbg-rg[<?php echo $key; ?>]" type="checkbox" name="WooZone-amzdbg-rg[]" value="<?php echo $key; ?>" <?php echo $checked ? 'checked="checked" ' : ''; ?>/>
								<label for="WooZone-amzdbg-rg[<?php echo $key; ?>]">
									<a href="<?php echo self::$ResponseGroups_baseurl . "RG_$key.html"; ?>" target="_blank" class="<?php echo $checked ? 'on' : ''; ?>"><?php echo $key; ?></a>
								</label>
							</li>
						<?php
							}
						?>
					</ul>
					<div class="WooZone-amzdbg-exec">
						<label for="WooZone-amzdbg-asin">ASIN code:</label>
						<input id="WooZone-amzdbg-asin" type="text" class="" name="WooZone-amzdbg-asin" value="B00KDRPW76" />
						
						<a href="#" class="WooZone-form-button WooZone-form-button-success" id="WooZone-amzdbg-getAmzResponse">Get Amazon Response</a>
					</div>
					
					<div id="WooZone-amzdbg-amazonResponse">
						<?php
							require('lib.collapsible/json.format.html');
						?>
					</div>
				</div>
			</div>
		<?php
			$html[] = ob_get_clean();

			return implode("\n", $html);
			
			}
		}


		/**
		 * Others
		 */
	}
}

// Initalize the your amazonDebug
$amazonDebug = new amazonDebug($this->cfg, $module);