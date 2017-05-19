<?php
/*
* Define class Modules Manager List
* Make sure you skip down to the end of this file, as there are a few
* lines of code that are very important.
*/
! defined( 'ABSPATH' ) and exit;

if(class_exists('WooZoneCSVBulkImport') != true) {

	class WooZoneCSVBulkImport {
		/*
		* Some required plugin information
		*/
		const VERSION = '1.0';

		/*
		* Store some helpers config
		*
		*/
		public $cfg	= array();
		public $module = array();
		public $networks = array();
		private $amz_setup = null;
		public $the_plugin = null;

		/*
		* Required __construct() function that initalizes the AA-Team Framework
		*/
		public function __construct($cfg, $module)
		{
			global $WooZone;
			
			$this->the_plugin = $WooZone;
			$this->cfg = $cfg;
			$this->module = $module;
			$this->amz_setup = $WooZone->getAllSettings('array', 'amazon');
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
			
			$db_protocol_setting = isset($this->amz_setup['protocol']) ? $this->amz_setup['protocol'] : 'auto';
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

		public function printListInterface ()
		{
			global $WooZone;
			
			// find if user makes the setup
			$moduleValidateStat = $this->moduleValidation();
			if ( !$moduleValidateStat['status'] || !is_object($this->the_plugin->amzHelper) || is_null($this->the_plugin->amzHelper) )
				echo $moduleValidateStat['html'];
			else{

			$amazon_settings = $WooZone->getAllSettings('array', 'amazon');

			$html = array();
			$html[] = WooZone()->print_demo_request( false );
				
			$html[] = 	'<style type="text/css">#WooZone-csvBulkImport { display: block } </style>';
			$html[] = '<script type="text/javascript" src="' . ( $this->module['folder_uri'] ) . 'bulk.js?' . ( time() ) . '" ></script>';
			
			$html[] = '<div id="WooZone-csvBulkImport">';
			$html[] = 	'<div id="WooZone-csvBulkImport-left-panel">';

			$html[] = 	'<h3>ASIN codes:</h3>';
			$html[] = 	'<textarea id="WooZone-csv-asin"></textarea>';

			$html[] = 	'<div class="WooZone-delimiters">';
			$html[] = 		'<h3>ASIN delimiter by:</h3>';
			$html[] = 		'<p><input id="WooZone-csv-radio-newline" type="radio" class="WooZone-csv-radio" checked name="WooZone-csv-delimiter" val="newline" /><label for="WooZone-csv-radio-newline">New line <code>\n</code></label></p>';
			$html[] = 		'<p><input id="WooZone-csv-radio-comma" type="radio" name="WooZone-csv-delimiter" val="comma" /><label for="WooZone-csv-radio-comma">Comma <code>,</code></label></p>';
			$html[] = 		'<p><input id="WooZone-csv-radio-tab" type="radio" name="WooZone-csv-delimiter" val="tab" /><label for="WooZone-csv-radio-tab">TAB <code>TAB</code></label></p>';
			$html[] = 		'<div style="clear:both;"></div>';
			$html[] = 	'</div>';

			$html[] = 	'<div class="amzStore-delimiters">';
			$html[] = 		'<h3>Import to category:</h3>';

			$args = array(
				'orderby' 	=> 'menu_order',
				'order' 	=> 'ASC',
				'hide_empty' => 0,
				'post_per_page' => '-1'
			);
			$categories = get_terms('product_cat', $args);
			  
			$args = array(
				'show_option_all'    => '',
				'show_option_none'   => 'Use category from Amazon',
				'orderby'            => 'ID', 
				'order'              => 'ASC',
				'show_count'         => 0,
				'hide_empty'         => 0, 
				'child_of'           => 0,
				'exclude'            => '',
				'echo'               => 0,
				'selected'           => 0,
				'hierarchical'       => 1, 
				'name'               => 'WooZone-to-category',
				'id'                 => 'WooZone-to-category',
				'class'              => 'postform',
				'depth'              => 0,
				'tab_index'          => 0,
				'taxonomy'           => 'product_cat',
				'hide_if_empty'      => false,
			);
			$html[] = wp_dropdown_categories( $args );
			$html[] = 		'<div style="clear:both;"></div>';
			$html[] = 	'</div>';

			$html[] = 	'<a href="#" class="WooZone-form-button WooZone-form-button-info" id="WooZone-addASINtoQueue">Add ASIN codes to Queue</a>';
			$html[] = 	'</div>';
			$html[] = 	'<div id="WooZone-csvBulkImport-right-panel">';
			$html[] = 	'<div id="WooZone-csvBulkImport-queue-response" style="display:none">';
			$html[] = 	'<table class="WooZone-table" style="border-collapse: collapse;">';
			$html[] = 		'<thead>';
			$html[] = 			'<tr>';
			$html[] = 				'<th width="150">ASINs</th>';
			$html[] = 				'<th>Status</th>';
			$html[] = 			'</tr>';
			$html[] = 		'</thead>';
			$html[] = 		'<tbody id="WooZone-print-response">';
			$html[] = 		'</tbody>';
			$html[] = 	'</table>';

			$html[] = 	'<a href="#" class="WooZone-form-button WooZone-form-button-success" id="WooZone-startImportASIN">Start import all</a>';
			$html[] = 	'<div class="WooZone-status-block">Importing product(s) ...<span id="WooZone-status-ready">0</span> ready, <span id="WooZone-status-remaining">0</span> remaining</div>';

			$html[] = 	'</div>';
			$html[] = 	'<p id="WooZone-no-ASIN" class="WooZone-message WooZone-info"><em>Please first add some ASIN codes to Queue!</em></p>';
			$html[] = 	'</div>';
			$html[] = 	'<div style="clear:both;"></div>';
			$html[] = '</div>';
			
			return implode("\n", $html);
			
			}
		}
	}
}

// Initalize the your WooZoneCSVBulkImport
//$WooZoneCSVBulkImport = new WooZoneCSVBulkImport($this->cfg, $module);