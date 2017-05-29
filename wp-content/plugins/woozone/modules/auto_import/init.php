<?php
/*
* Define class WooZoneAutoImport
* Make sure you skip down to the end of this file, as there are a few
* lines of code that are very important.
*/
!defined('ABSPATH') and exit;

if (class_exists('WooZoneAutoImport') != true) {
    class WooZoneAutoImport
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
        private $module_folder_path = '';
		private $module = '';

		static protected $_instance;
		
		public $localizationName;
		
		private $settings;
		
		private $searchParameters = array();
		private $searchParametersCore = array();
		private $searchParametersGroups = array();

		private $queue_chunk_search = array(); // cache searches import parameters when import product from queue
		private static $max_nb_tries = 3; // maximum number or retries to import product / execute search
		private static $queue_chunk_nb = 10; // number of rows retrieved from queue per request
		private static $queue_chunk_nb_search = 3; // number of rows retrieved from search table per request
		
		public $recurrency = array();
		public $countries = array();
		public $main_aff_ids = array();


        /*
        * Required __construct() function that initalizes the AA-Team Framework
        */
        public function __construct( $is_cron=false )
        {
        	//return false; // DEACTIVATED
        	global $WooZone;

        	$this->the_plugin = $WooZone;

			$this->module_folder = $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'modules/auto_import/';
            $this->module_folder_path = $this->the_plugin->cfg['paths']['plugin_dir_path'] . 'modules/auto_import/';
			$this->module = $this->the_plugin->cfg['modules']['auto_import'];
			
			$this->localizationName = $this->the_plugin->localizationName;
			
			$this->settings = $this->the_plugin->getAllSettings('array', 'amazon');

			$this->recurrency = array(
				12      => __('Every 12 hours', $this->the_plugin->localizationName),
		        24      => __('Every single day', $this->the_plugin->localizationName),
		        48      => __('Every 2 days', $this->the_plugin->localizationName),
		        72      => __('Every 3 days', $this->the_plugin->localizationName),
		        96      => __('Every 4 days', $this->the_plugin->localizationName),
		        120     => __('Every 5 days', $this->the_plugin->localizationName),
		        144     => __('Every 6 days', $this->the_plugin->localizationName),
		        168     => __('Every 1 week', $this->the_plugin->localizationName),
		        336     => __('Every 2 weeks', $this->the_plugin->localizationName),
		        504     => __('Every 3 weeks', $this->the_plugin->localizationName),
		        720     => __('Every 1 month', $this->the_plugin->localizationName), // ~ 4 weeks + 2 days
    		);

			$theHelper = $this->the_plugin->amzHelper;
			$this->countries = is_object($theHelper) ? $theHelper->get_countries( 'country' ) : array();
			$this->main_aff_ids = is_object($theHelper) ? $theHelper->get_countries( 'main_aff_id' ) : array();

			// search parameters details:
			//		- title = title text
			//		- type = html element type (input, select)
			//		- options = drop down options array
			//		- readonly = element cannot be excluded from search list
			//		- editable = element is selectable (as input text or dropdown select) not just "text display"
			$this->searchParameters = array(
				// extra params
				'provider'				=> array(
					'title'				=> __('Provider', $this->the_plugin->localizationName),
				),
				'country'				=> array(
					'title'				=> __('Country', $this->the_plugin->localizationName),
				),
				'main_aff_id'				=> array(
					'title'				=> __('Main Affiliate ID', $this->the_plugin->localizationName),
				),
				'search_title'			=> array(
					'title'				=> __('Search title', $this->the_plugin->localizationName),
					'editable'			=> true,
					'type'				=> 'input',
				),
				'recurrency'			=> array(
					'title'				=> __('Recurrency', $this->the_plugin->localizationName),
					'editable'			=> true,
					'type'				=> 'select',
					'options'			=> $this->recurrency,
				),
				/*
				'startdate'				=> array(
					'title'				=> __('Start date', $this->the_plugin->localizationName),
					'editable'			=> true,
					'type'				=> 'date',
					'std' 				=> '',
				),
				'starttime'				=> array(
					'title'				=> __('Start time', $this->the_plugin->localizationName),
					'editable'			=> true,
					'type'				=> 'time',
					'std' 				=> '',
				),
				*/

				
				// search params
				'keyword'				=> array(
					'title'				=> __('Keyword', $this->the_plugin->localizationName),
					'readonly'			=> true,
				),
				'category'				=> array(
					'title'				=> __('Category', $this->the_plugin->localizationName), 
					'readonly'			=> true,
				),
				'category_id'			=> array(
					'title'				=> __('Category ID', $this->the_plugin->localizationName),
					'readonly'			=> true,
				),
				'nbpages'				=> array(
					'title'				=> __('Grab Nb Pages', $this->the_plugin->localizationName),
					'readonly'			=> true,
				),
				'page'					=> array(
					'title'				=> __('Page Nb', $this->the_plugin->localizationName),
					'readonly'			=> true,
				),
				'site'					=> array(
					'title'				=> __('Choose Site', $this->the_plugin->localizationName), 
				),
				'BrowseNode_list'		=> array(
					'title'				=> __('Browse Node Tree', $this->the_plugin->localizationName), 
				),
				
				// import params
				'import_type'			=> array(
					'title'				=> __('Image Import Type', $this->the_plugin->localizationName), 
				),
				'nbimages'				=> array(
					'title'				=> __('Number of Images', $this->the_plugin->localizationName), 
				),
				'nbvariations'			=> array(
					'title'				=> __('Number of Variations', $this->the_plugin->localizationName), 
				),
				'spin'					=> array(
					'title'				=> __('Spin on Import', $this->the_plugin->localizationName), 
				),
				'attributes'			=> array(
					'title'				=> __('Import attributes', $this->the_plugin->localizationName), 
				),
				'to_category'			=> array(
					'title'				=> __('Import in category', $this->the_plugin->localizationName),
					'readonly'			=> true, 
				),
				'prods_import_type'		=> array(
					'title'				=> __('Products Import Type', $this->the_plugin->localizationName),
					'readonly'			=> true,
				),
			);
			// core search parameters (cannot be deselected)
			// (key, value) => (parameter key, is editable?)
			$this->searchParametersCore = array(
				'provider'			=> false,
				'country'			=> false,
				'_country'			=> false,
				'main_aff_id'		=> false,
				'_main_aff_id'		=> false,
				'search_title'		=> true,
				'recurrency'		=> true,
				//'startdate'			=> true,
				//'starttime'			=> true,
			);
			$this->searchParametersGroups = array(
				'extra_params'				=> __('General Parameters', $this->the_plugin->localizationName),
				'params'					=> __('Search Parameters', $this->the_plugin->localizationName),
				'import_params'				=> __('Import Parameters', $this->the_plugin->localizationName),
			);
  
			if (is_admin() && !$is_cron) {
	            add_action('admin_menu', array( &$this, 'adminMenu' ));
			}
			
            // ajax requests
			add_action('wp_ajax_WooZone_AutoImportAjax', array( &$this, 'ajax_request' ), 10, 2);
        }

		public function get_module() {
			return $this;

			$obj = new stdClass();
			$obj->searchParameters 			= $this->searchParameters;
			$obj->searchParametersCore 		= $this->searchParametersCore;
			$obj->searchParametersGroups 	= $this->searchParametersGroups;
			return $obj;
		}

		/**
	    * Singleton pattern
	    *
	    * @return WooZoneAutoImport Singleton instance
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
    		// auto import Queue 
    		add_submenu_page(
    			$this->the_plugin->alias,
    			$this->the_plugin->alias . " " . __('Auto Import Queue', $this->the_plugin->localizationName),
	            __('Auto Import Queue', $this->the_plugin->localizationName),
	            'manage_options',
	            $this->the_plugin->alias . "_auto_import_queue",
	            array($this, 'printInterface_queue')
	        );
			
			// auto import searches
    		add_submenu_page(
    			$this->the_plugin->alias,
    			$this->the_plugin->alias . " " . __('Auto Import Search', $this->the_plugin->localizationName),
	            __('Auto Import Search', $this->the_plugin->localizationName),
	            'manage_options',
	            $this->the_plugin->alias . "_auto_import_search",
	            array($this, 'printInterface_search')
	        );

			return $this;
		}


		/*
		 * Queue - printBaseInterface, method
		 */
		public function printInterface_queue()
		{
            $ss = $this->settings;

			$module = 'auto_import';
            $mod_vars = array();

            // Auto Import
            $mod_vars['mod_menu'] = 'import|auto_import';
            $mod_vars['mod_title'] = __('Auto Import Queue', $this->the_plugin->localizationName);
            extract($mod_vars);
            
            $module_data = $this->the_plugin->cfg['modules']["$module"];
            $module_folder = $this->the_plugin->cfg['paths']['plugin_dir_url'] . "modules/$module/";
		?>
			<!-- simplemodal -->
			<link type='text/css' href='<?php echo $this->module_folder;?>jquery.simplemodal/basic.css' rel='stylesheet' media='screen' />
			<!-- IE6 "fix" for the close png image -->
			<!--[if lt IE 7]>
			<link type='text/css' href='<?php echo $this->module_folder;?>jquery.simplemodal/basic_ie.css' rel='stylesheet' media='screen' />
			<![endif]-->
			<script type='text/javascript' src='<?php echo $this->module_folder;?>jquery.simplemodal/jquery.simplemodal.1.4.4.min.js'></script>

			<!-- preload the images -->
			<div style='display:none'>
				<img src='<?php echo $this->module_folder;?>jquery.simplemodal/x.png' alt='' />
			</div>

			<!-- current module -->
			<script type='text/javascript' src='<?php echo $this->module_folder;?>app.auto_import.js' ></script>
			<style type="text/css">
				.WooZone-list-table-left-col {
					width: 64%;
				}
				.WooZone-list-table-right-col {
					width: 34%;
				}
			</style>
			
        <div id="<?php echo WooZone()->alias?>">
        	<div id="WooZone-wrapper" class="<?php echo WooZone()->alias?>-content">
            
            <?php
            // show the top menu
            WooZoneAdminMenu::getInstance()->make_active($mod_menu)->show_menu(); 
            ?>
            
			<?php
				// Lang Messages
				$lang = array(
					'loading'                   => __('Loading...', 'WooZone'),
					'closing'                   => __('Closing...', 'WooZone'),
				); 
			?>
			<!-- Lang Messages -->
			<div id="WooZone-lang-translation" style="display: none;"><?php echo htmlentities(json_encode( $lang )); ?></div>
            
             <!-- Content -->
			<section class="WooZone-main">

            
	            <?php 
				echo WooZone()->print_section_header(
					$module_data["$module"]['menu']['title'],
					$module_data["$module"]['description'],
					$module_data["$module"]['help']['url']
				);
				?>
				
	            <div class="panel panel-default WooZone-panel">
	            	
<?php
	if ( !WooZone()->can_import_products() ) {
		echo '<div class="panel-body WooZone-panel-body">';
		echo 	WooZone()->demo_products_import_end_html();
		echo '</div>';
	}
	else if ( WooZone()->is_aateam_demo_keys() ) {
		echo '<div class="panel-body WooZone-panel-body">';
		echo 	WooZone()->demo_products_import_end_html(array(
			'is_block_demo_keys'	=> true,
		));
		echo '</div>';
	}
	else {
?>

					<div class="panel-heading WooZone-panel-heading">
						<h2><?php echo $mod_title; ?></h2>
					</div>
					
					<div class="panel-body WooZone-panel-body">

	                    <!-- Content Area -->
	                    <div id="WooZone-content-area">
	                        <div class="WooZone-grid_4">
	                            <div class="WooZone-panel">

										<!-- Cronjob stats -->
										<div class="WooZone-sync-stats" data-what="queue">
											<h3><?php _e('Auto Import Cronjob Stats', $this->the_plugin->localizationName);?></h3>
											<?php echo $this->get_cronjob_stats_queue(); ?>
										</div>

										<form class="WooZone-form" action="#save_with_ajax">
											<div class="WooZone-form-row WooZone-table-ajax-list" id="WooZone-table-ajax-response">
												
											<?php
											WooZoneAjaxListTable::getInstance( $this->the_plugin )
												->setup(array(
													'id' 				=> 'WooZoneAutoImportQueue',
													'show_header' 		=> true,
													//'search_box' 		=> false,
													//'post_statuses' 	=> array(
													//	'publish'   => __('Published', $this->the_plugin->localizationName)
													//),
													//'list_post_types'	=> array('product'),
													'show_header_buttons' => true,
													'items_per_page' 	=> '10',
													'custom_table'		=> 'amz_queue',
													'orderby'			=> 'id',
													'order'				=> 'DESC',
													'filter_fields'		=> array(
														'from_op' => array(
															'title' 			=> __('From', $this->the_plugin->localizationName),
															'options_from_db' 	=> true,
															'include_all'		=> true,
														),
														'nb_tries'  => array(
															'title' 			=> __('Nb tries (max = 3)', $this->the_plugin->localizationName),
															'options_from_db' 	=> false,
															'include_all'		=> true,
															'options'			=> array(
																'0'			=> __('0 tries', $this->the_plugin->localizationName),
																'1'			=> __('1 try', $this->the_plugin->localizationName),
																'2'			=> __('2 tries', $this->the_plugin->localizationName),
																'3'			=> __('3 tries', $this->the_plugin->localizationName),
															),
														),
														'status'  => array(
															'title' 			=> __('Status', $this->the_plugin->localizationName),
															'options_from_db' 	=> false,
															'include_all'		=> true,
															'options'			=> array(
																'new'		=> __('New', $this->the_plugin->localizationName),
																'done'		=> __('Done successfully', $this->the_plugin->localizationName),
																'error'		=> __('Error', $this->the_plugin->localizationName),
																'already'	=> __('Already imported', $this->the_plugin->localizationName),
															),
															'display'			=> 'links',
														),
													),
													'search_box'		=> array(
														'title' 	=> __('Search ASIN', $this->the_plugin->localizationName),
														'fields'	=> array('asin'),
													),
													'columns'			=> array(

														'checkbox'	=> array(
															'th'	=>  'checkbox',
															'td'	=>  'checkbox',
														),

														'id'		=> array(
															'th'	=> __('ID', $this->the_plugin->localizationName),
															'td'	=> '%ID%',
															'width' => '40'
														),
														
														/*'thumb'		=> array(
															'th'	=> __('Thumb', $this->the_plugin->localizationName),
															'td'	=> '%thumb%',
															'align' => 'center',
															'width' => '50'
														),*/
														
														'asin'		=> array(
															'th'	=> __('ASIN', $this->the_plugin->localizationName),
															'td'	=> '%asin%',
															'align' => 'center',
															'width' => '70'
														),
														
														'from_op'		=> array(
															'th'	=> __('From', $this->the_plugin->localizationName),
															'td'	=> '%from_op%',
															'align' => 'center',
															'width' => '250'
														),

														'status'		=> array(
															'th'	=> __('Status', $this->the_plugin->localizationName),
															'td'	=> '%status%',
															'align' => 'left',
															'width' => '120',
														),

														'created_date'		=> array(
															'th'	=> __('Created Date', $this->the_plugin->localizationName),
															'td'	=> '%created_date%',
															'width' => '120'
														),
														'imported_date'		=> array(
															'th'	=> __('Imported Date', $this->the_plugin->localizationName),
															'td'	=> '%imported_date%',
															'width' => '120'
														),
														
														'product'		=> array(
															'th'	=> __('Product', $this->the_plugin->localizationName),
															'td'	=> '%product_links%',
															'align' => 'left',
															'width' => '120'
														),

														'nb_tries'		=> array(
															'th'	=> __('Nb tries (max = 3)', $this->the_plugin->localizationName),
															'td'	=> '%nb_tries%',
															'align' => 'center',
															'width' => '50'
														),

														'delete_btn' => array(
															'th'	=> __('Delete', $this->the_plugin->localizationName),
															'td'	=> '%button%',
															'option' => array(
																'action' => 'do_item_delete',
																'value' => __('Delete row', $this->the_plugin->localizationName),
																'color' => 'WooZone-form-button-small WooZone-button WooZone-form-button-danger'
															),
															'width' => '60'
														),
														
														/*
														'publish_btn' => array(
															'th'	=> __('Active', 'psp'),
															'td'	=> '%button_publish%',
															'option' => array(
																'action' => 'do_item_publish',
																'value' => __('Unpublish', 'psp'),
																'color'	=> 'orange',
																'value_change' => __('Publish', 'psp'),
																'color_change' => 'green',
															),
															'width' => '60'
														),
														*/
													),
													'mass_actions' 	=> array(
														'delete_all' => array(
															'value' => __('Delete all rows', 'psp'),
															'action' => 'do_bulk_delete_rows',
															'color' => 'WooZone-form-button-small WooZone-form-button-danger'
														),
													),
												))
												->print_html();
								            ?>
								            </div>
							            </form>
				            		</div>
				            		
                                </div>
                            </div>
                            <div class="clear"></div>
                        </div>
                        
<?php } // end demo keys ?>

                    </div>
                </section>
            </div>
        </div>
		<?php
		}

        private function get_cronjob_stats_queue() {
            ob_start();

            $ss = $this->settings;

            // last report
            $report_last_date = get_option('WooZone_ai_report_last_date', false);

			// cron stats
			$cron_stats_db = get_option('WooZone_ai_cron_stats', array());
			$cron_stats_db = is_array($cron_stats_db) ? $cron_stats_db : array();

			// current chunk ids
			$current_chunk = isset($cron_stats_db['current_chunk']) ? $cron_stats_db['current_chunk'] : array();
			$current_chunk = implode(', ', $current_chunk);

			// current row (in chunk) id
			$current_row = isset($cron_stats_db['current_chunk_row']) ? $cron_stats_db['current_chunk_row'] : '';
			
			// estimated new rows in queue
			$queue_rows_new = isset($cron_stats_db['queue_rows_new']) ? (int) $cron_stats_db['queue_rows_new'] : 0;
			
			// start & end time, duration
			$time_html = ''; $duration = 0;
            if ( isset($cron_stats_db['start_time'], $cron_stats_db['end_time'])
                && $cron_stats_db['end_time'] > $cron_stats_db['start_time'] ) {

                $duration = $this->the_plugin->u->time_since($cron_stats_db['start_time'], $cron_stats_db['end_time']);
				$time_html = sprintf(
					__('start: %s , end: %s , duration: %s', $this->the_plugin->localizationName),
					$this->the_plugin->last_update_date('true', $cron_stats_db['start_time']),
					$this->the_plugin->last_update_date('true', $cron_stats_db['end_time']),
					$duration
				);
            }
			else if( isset($cron_stats_db['start_time']) ) {
				$time_html = sprintf(
					__('start: %s', $this->the_plugin->localizationName),
					$this->the_plugin->last_update_date('true', $cron_stats_db['start_time'])
				);
			}
			
			// process status
            $sync_status = 0; // in progress
            $sync_status_text = __('in progress', $this->the_plugin->localizationName);
			if ( empty($cron_stats_db) ) {

                $sync_status = 2; // not initialized yet.
                $sync_status_text = __('to be initialized', $this->the_plugin->localizationName);
            }
			else if ( empty($current_chunk) || !empty($duration) ) {

                $sync_status = 1; // success
                $sync_status_text = __('completed', $this->the_plugin->localizationName);                
            } 
?>
            <table>
                <thead>
                </thead>
                <tfoot>
                </tfoot>
                <tbody>
                    <tr>
                        <td width="70%">
                            <span class="title"><?php _e('Last Cron Stats', $this->the_plugin->localizationName);?></span>
                            <span class="WooZone-message <?php echo $sync_status == 1 ? 'WooZone-success' : 'WooZone-info'; ?>"><?php echo $sync_status_text; ?></span>
                            <ul>
                                <li>
                                    <?php _e('Time', $this->the_plugin->localizationName);?>:
                                    <span><?php echo $time_html; ?></span>
                                </li>
                                <li>
                                    <?php _e('Estimated "New" rows in queue', $this->the_plugin->localizationName);?>:
                                    <span><?php echo $queue_rows_new; ?></span>
                                </li>
							</ul>
							<ul>
                                <li>
                                    <?php _e('Current processed chunk IDs', $this->the_plugin->localizationName);?>:
                                    <span><?php echo $current_chunk; ?></span>
                                </li>
                                <li>
                                    <?php _e('Last processed row (in chunk) ID', $this->the_plugin->localizationName);?>:
                                    <span><?php echo $current_row; ?></span>
                                </li>
                            </ul>
                        </td>
                        <td>
                            <span class="title"><?php _e('Last Report', $this->the_plugin->localizationName);?></span>
                            <ul>
                                <?php if ( !empty($report_last_date) ) { ?>
                                <li>
                                    <?php _e('Generation date', $this->the_plugin->localizationName);?>:
                                    <span><?php
                                        echo $this->the_plugin->last_update_date('true', $report_last_date);
                                    ?></span>
                                </li>
                                <?php } else { ?>
                                <li>
                                    <?php _e('not available yet.', $this->the_plugin->localizationName);?>
                                </li>
                                <?php } ?>
                            </ul>
                        </td>
                    </tr>
                </tbody>
            </table>
<?php
            return ob_get_clean();
        }


		/*
		 * Search - printBaseInterface, method
		 */
		public function printInterface_search()
		{
            $ss = $this->settings;

			$module = 'auto_import';
            $mod_vars = array();

            // Auto Import
            $mod_vars['mod_menu'] = 'import|auto_import';
            $mod_vars['mod_title'] = __('Auto Import Search', $this->the_plugin->localizationName);
            extract($mod_vars);
            
            $module_data = $this->the_plugin->cfg['modules']["$module"];
            $module_folder = $this->the_plugin->cfg['paths']['plugin_dir_url'] . "modules/$module/";
		?>
			<!-- simplemodal -->
			<link type='text/css' href='<?php echo $this->module_folder;?>jquery.simplemodal/basic.css' rel='stylesheet' media='screen' />
			<!-- IE6 "fix" for the close png image -->
			<!--[if lt IE 7]>
			<link type='text/css' href='<?php echo $this->module_folder;?>jquery.simplemodal/basic_ie.css' rel='stylesheet' media='screen' />
			<![endif]-->
			<script type='text/javascript' src='<?php echo $this->module_folder;?>jquery.simplemodal/jquery.simplemodal.1.4.4.min.js'></script>

			<!-- preload the images -->
			<div style='display:none'>
				<img src='<?php echo $this->module_folder;?>jquery.simplemodal/x.png' alt='' />
			</div>

			<!-- current module -->
			<script type='text/javascript' src='<?php echo $this->module_folder;?>app.auto_import.js' ></script>
			<style type="text/css">
				.WooZone-list-table-left-col {
					width: 64%;
					height: auto;
				}
				.WooZone-list-table-right-col {
					width: 34%;
				}
			</style>
		
		<div id="<?php echo WooZone()->alias?>">
			<div class="<?php echo WooZone()->alias?>-content">
            
            <?php
            // show the top menu
            WooZoneAdminMenu::getInstance()->make_active($mod_menu)->show_menu(); 
            ?>
            
            <!-- Content -->
			<section class="<?php echo WooZone()->alias?>-main">
			
				<?php
					// Lang Messages
					$lang = array(
						'loading'                   => __('Loading...', 'WooZone'),
						'closing'                   => __('Closing...', 'WooZone'),
					); 
				?>

				<!-- Lang Messages -->
				<div id="WooZone-lang-translation" style="display: none;"><?php echo htmlentities(json_encode( $lang )); ?></div>

				<?php
				echo WooZone()->print_section_header(
					$module_data["$module"]['menu']['title'],
					$module_data["$module"]['description'],
					$module_data["$module"]['help']['url']
				);
				?>

                <div class="panel panel-default WooZone-panel">

<?php
	if ( !WooZone()->can_import_products() ) {
		echo '<div class="panel-body WooZone-panel-body">';
		echo 	WooZone()->demo_products_import_end_html();
		echo '</div>';
	}
	else if ( WooZone()->is_aateam_demo_keys() ) {
		echo '<div class="panel-body WooZone-panel-body">';
		echo 	WooZone()->demo_products_import_end_html(array(
			'is_block_demo_keys'	=> true,
		));
		echo '</div>';
	}
	else {
?>
                	
					<div class="panel-heading WooZone-panel-heading">
						<h2><?php echo $mod_title; ?></h2>
					</div>
					
					<div class="panel-body WooZone-panel-body">

                        <!-- Content Area -->
                        <div id="WooZone-content-area">
                            <div class="WooZone-grid_4">
								<div class="WooZone-panel">

									<!-- Cronjob stats -->
									<div class="WooZone-sync-stats" data-what="search">
										<h3><?php _e('Auto Import Cronjob Stats', $this->the_plugin->localizationName);?></h3>
										<?php echo $this->get_cronjob_stats_search(); ?>
									</div>

									<form class="WooZone-form" action="#save_with_ajax">
										<div class="WooZone-form-row WooZone-table-ajax-list" id="WooZone-table-ajax-response">
											
										<?php
										WooZoneAjaxListTable::getInstance( $this->the_plugin )
											->setup(array(
												'id' 				=> 'WooZoneAutoImportSearch',
												'show_header' 		=> true,
												//'search_box' 		=> false,
												//'post_statuses' 	=> array(
												//	'publish'   => __('Published', $this->the_plugin->localizationName)
												//),
												//'list_post_types'	=> array('product'),
												'show_header_buttons' => true,
												'items_per_page' 	=> '10',
												'custom_table'		=> 'amz_search',
												'orderby'			=> 'id',
												'order'				=> 'DESC',
												'filter_fields'		=> array(
													'publish'  => array(
														'title' 			=> __('Published', $this->the_plugin->localizationName),
														'options_from_db' 	=> false,
														'include_all'		=> true,
														//'options'			=> array(
														//	'Y'			=> __('Yes', $this->the_plugin->localizationName),
														//	'N'			=> __('No', $this->the_plugin->localizationName),
														//),
														'options'			=> array(
															'Y'			=> __('Published', $this->the_plugin->localizationName),
															'N'			=> __('Unpublished', $this->the_plugin->localizationName),
														),
														'display'			=> 'links',
													),
													/*'provider'  => array(
														'title' 			=> __('Provider', $this->the_plugin->localizationName),
														'options_from_db' 	=> false,
														'include_all'		=> true,
														'options'			=> array(
															'amazon'	=> __('Amazon', $this->the_plugin->localizationName),
														),
													),*/
													'country'  => array(
														'title' 			=> __('Country', $this->the_plugin->localizationName),
														'options_from_db' 	=> false,
														'include_all'		=> true,
														'options'			=> $this->countries,
													),
													'recurrency'  => array(
														'title' 			=> __('Recurrency', $this->the_plugin->localizationName),
														'options_from_db' 	=> false,
														'include_all'		=> true,
														'options'			=> $this->recurrency,
													),
													'nb_tries'  => array(
														'title' 			=> __('Nb tries (max = 3)', $this->the_plugin->localizationName),
														'options_from_db' 	=> false,
														'include_all'		=> true,
														'options'			=> array(
															'0'			=> __('0 tries', $this->the_plugin->localizationName),
															'1'			=> __('1 try', $this->the_plugin->localizationName),
															'2'			=> __('2 tries', $this->the_plugin->localizationName),
															'3'			=> __('3 tries', $this->the_plugin->localizationName),
														),
													),
													'status'  => array(
														'title' 			=> __('Status', $this->the_plugin->localizationName),
														'options_from_db' 	=> false,
														'include_all'		=> true,
														'options'			=> array(
															'new'		=> __('New', $this->the_plugin->localizationName),
															'done'		=> __('Done successfully', $this->the_plugin->localizationName),
															'error'		=> __('Error', $this->the_plugin->localizationName),
														),
														'display'			=> 'links',
													),
												),
												'search_box'		=> array(
													'title' 	=> __('Search title', $this->the_plugin->localizationName),
													'fields'	=> array('search_title'),
												),
												'columns'			=> array(

													'checkbox'	=> array(
														'th'	=>  'checkbox',
														'td'	=>  'checkbox',
													),

													'id'		=> array(
														'th'	=> __('ID', $this->the_plugin->localizationName),
														'td'	=> '%ID%',
														'width' => '40'
													),
													
													'search_title'		=> array(
														'th'	=> __('Search title', $this->the_plugin->localizationName),
														'td'	=> '%search_title%',
														'align' => 'center',
														'width' => '150'
													),
													
													'params_box'		=> array(
														'th'	=> __('Params', $this->the_plugin->localizationName),
														'td'	=> '%params_box%',
														'align' => 'center',
														'width' => '180'
													),
													
													'info_set1'		=> array(
														'th'	=> __('Queue Prods', $this->the_plugin->localizationName),
														'td'	=> '%info_set1%',
														'align' => 'center',
														'width' => '100'
													),

													'status'		=> array(
														'th'	=> __('Status', $this->the_plugin->localizationName),
														'td'	=> '%status%',
														'align' => 'left',
														'width' => '120'
													),

													'created_date'		=> array(
														'th'	=> __('Created Date', $this->the_plugin->localizationName),
														'td'	=> '%created_date%',
														'width' => '120'
													),
													'info_set2'		=> array(
														'th'	=> __('Execution Info', $this->the_plugin->localizationName),
														'td'	=> '%info_set2%',
														'width' => '200'
													),
													
													'nb_tries'		=> array(
														'th'	=> __('Nb tries (max = 3)', $this->the_plugin->localizationName),
														'td'	=> '%nb_tries%',
														'align' => 'center',
														'width' => '60'
													),

													'delete_btn' => array(
														'th'	=> __('Delete', $this->the_plugin->localizationName),
														'td'	=> '%button%',
														'option' => array(
															'action' => 'do_item_delete',
															'value' => __('Delete row', $this->the_plugin->localizationName),
															'color' => 'WooZone-form-button-small WooZone-form-button-danger'
														),
														'width' => '60'
													),
													
													'publish_btn' => array(
														'th'	=> __('Published', 'psp'),
														'td'	=> '%button_publish%',
														'option' => array(
															'action' => 'do_item_publish',
															'value' => __('Unpublish', 'psp'),
															'color'	=> 'WooZone-form-button-small WooZone-form-button-warning',
															'value_change' => __('Publish', 'psp'),
															'color_change' => 'WooZone-form-button-small WooZone-form-button-success',
														),
														'width' => '60'
													),
												),
												'mass_actions' 	=> array(
													'delete_all' => array(
														'value' => __('Delete all rows', 'psp'),
														'action' => 'do_bulk_delete_rows',
														'color' => 'WooZone-form-button-small WooZone-form-button-danger'
													),
												),
												'moduleparams' => array(
													'auto_import' => $this->get_module()
												),
											))
											->print_html();
							            ?>
							            </div>
						            </form>
			            		</div>
                            </div>
                        </div>
                        <div class="clear"></div>
                    </div>
                    
<?php } // end demo keys ?>

                </div>
            </section>
        </div>
		<?php
		}

        private function get_cronjob_stats_search() {
            ob_start();

            $ss = $this->settings;

            // last report
            //$report_last_date = get_option('WooZone_ai_report_last_date', false);

			// cron stats
			$cron_stats_db = get_option('WooZone_ai_cron_stats_search', array());
			$cron_stats_db = is_array($cron_stats_db) ? $cron_stats_db : array();

			// current chunk ids
			$current_chunk = isset($cron_stats_db['current_chunk']) ? $cron_stats_db['current_chunk'] : array();
			$current_chunk = implode(', ', $current_chunk);

			// current row (in chunk) id
			$current_row = isset($cron_stats_db['current_chunk_row']) ? $cron_stats_db['current_chunk_row'] : '';
			
			// start & end time, duration
			$time_html = ''; $duration = 0;
            if ( isset($cron_stats_db['start_time'], $cron_stats_db['end_time'])
                && $cron_stats_db['end_time'] > $cron_stats_db['start_time'] ) {

                $duration = $this->the_plugin->u->time_since($cron_stats_db['start_time'], $cron_stats_db['end_time']);
				$time_html = sprintf(
					__('start: %s , end: %s , duration: %s', $this->the_plugin->localizationName),
					$this->the_plugin->last_update_date('true', $cron_stats_db['start_time']),
					$this->the_plugin->last_update_date('true', $cron_stats_db['end_time']),
					$duration
				);
            }
			else if( isset($cron_stats_db['start_time']) ) {
				$time_html = sprintf(
					__('start: %s', $this->the_plugin->localizationName),
					$this->the_plugin->last_update_date('true', $cron_stats_db['start_time'])
				);
			}
			
			// process status
            $sync_status = 0; // in progress
            $sync_status_text = __('in progress', $this->the_plugin->localizationName);
			if ( empty($cron_stats_db) ) {

                $sync_status = 2; // not initialized yet.
                $sync_status_text = __('to be initialized', $this->the_plugin->localizationName);
            }
			else if ( empty($current_chunk) || !empty($duration) ) {

                $sync_status = 1; // success
                $sync_status_text = __('completed', $this->the_plugin->localizationName);                
            } 
?>
            <table>
                <thead>
                </thead>
                <tfoot>
                </tfoot>
                <tbody>
                    <tr>
                        <td width="70%">
                            <span class="title"><?php _e('Last Cron Stats', $this->the_plugin->localizationName);?></span>
                            <span class="WooZone-message <?php echo $sync_status == 1 ? 'WooZone-success' : 'WooZone-info'; ?>"><?php echo $sync_status_text; ?></span>
                            <ul>
                                <li>
                                    <?php _e('Time', $this->the_plugin->localizationName);?>:
                                    <span><?php echo $time_html; ?></span>
                                </li>
							</ul>
							<ul>
                                <li>
                                    <?php _e('Current processed chunk IDs', $this->the_plugin->localizationName);?>:
                                    <span><?php echo $current_chunk; ?></span>
                                </li>
                                <li>
                                    <?php _e('Last processed row (in chunk) ID', $this->the_plugin->localizationName);?>:
                                    <span><?php echo $current_row; ?></span>
                                </li>
                            </ul>
                        </td>
                        <?php /*<td>
                            <span class="title"><?php _e('Last Report', $this->the_plugin->localizationName);?></span>
                            <ul>
                                <?php if ( !empty($report_last_date) ) { ?>
                                <li>
                                    <?php _e('Generation date', $this->the_plugin->localizationName);?>:
                                    <span><?php
                                        echo $this->the_plugin->last_update_date('true', $report_last_date);
                                    ?></span>
                                </li>
                                <?php } else { ?>
                                <li>
                                    <?php _e('not available yet.', $this->the_plugin->localizationName);?>
                                </li>
                                <?php } ?>
                            </ul>
                        </td>*/ ?>
                    </tr>
                </tbody>
            </table>
<?php
            return ob_get_clean();
        }

		public function show_search_params( $search_params ) {
			$html = array();
			
			$search_params_ = $search_params;
			foreach ( $search_params_ as $k => $v ) {
				if ( empty($v) || !is_array($v) ) {
					unset($search_params_["$k"]);
					continue 1;
				}
				foreach ($v as $key => $val) {
					$val = is_array($val) ? $val : trim($val);
					if ( empty($val) ) {
						unset($search_params_["$k"]["$key"]);
						continue 1;
					}
					
					if ( '_' == substr($key, 0, 1) ) {
						$__ = substr($key, 1);
						if (!empty($val) && isset($search_params_["$k"]["$__"])) {
							$search_params_["$k"]["$__"] = $val;
							unset( $search_params_["$k"]["$key"] );
						}
					}
				}
			}
			//var_dump('<pre>', $search_params_, '</pre>'); die('debug...');

			$html[] = 		'<div class="WooZone-autoimport-search-params">';
			foreach ( $search_params_ as $k => $v ) {
				
				$searchGroupTitle = isset($this->searchParametersGroups["$k"]) ? $this->searchParametersGroups["$k"]
					: __('Parameters', $this->localizationName );
				$html[] = 		'<table class="WooZone-table WooZone-debug-info">';
				$html[] = 			'<thead>';
				$html[] = 				'<tr>';
				//$html[] = 					'<th width="250">' . __('Parameter Name', $this->localizationName ) . '</th>';
				//$html[] = 					'<th>' . __('Parameter Value', $this->localizationName ) . '</th>';
				$html[] = 					'<th colspan="2">' . $searchGroupTitle . '</th>';
				$html[] = 				'</tr>';
				$html[] = 			'</thead>';
				$html[] = 			'<tbody>';
			
				foreach ($v as $key => $val) {
					$val = is_array($val) ? $val : trim($val);
					$val_orig = $search_params["$k"]["$key"];

					if ( isset($this->searchParameters["$key"], $this->searchParameters["$key"]['title']) ) {
						$nice_name = $this->searchParameters["$key"]['title'];
					} else {
						$nice_name = $this->the_plugin->__category_nice_name( $key );
					}
					$nice_name = trim($nice_name);
					
					// BrowseNode list/tree parameter
					$is_bnl = false; // parameter is browse node list?
					if ( 'BrowseNode_list' == $key ) {
						$is_bnl = true;
					}
					if ( 'country' == $key ) {
						$val = isset($this->countries["$val"]) ? $this->countries["$val"] : $val;
					}
					if ( 'recurrency' == $key ) {
						$val = isset($this->recurrency["$val"]) ? $this->recurrency["$val"] : $val;
					}
					// All the Other parameters
					if (1) {
						$html[] = 			'<tr>';
						$html[] = 				'<td width="250">';
						$html[] = 					'<span>' . ($nice_name) . '</span>';
						$html[] = 				'</td>';
						if (1) {
							if (is_array($val)) {
								$html[] = 		'<td>' . implode(' &gt; ', $val) . '</td>';
							}
							else {
								$html[] = 		'<td>' . ( $val ) . '</td>';								
							}
						}
						$html[] = 			'</tr>';
					}
				}

				$html[] = 			'</tbody>';
				$html[] = 		'</table>';
			}
			$html[] = 		'</div>';
			
			$html = implode(PHP_EOL, $html);
			$html = str_replace('"', '\'', $html);
			return $html;
		}

		
		/**
		 * Insane Mode related
		 */
		// load asset file: css, javascript or else!
		public function load_asset( $what='', $print=true ) {
			$asset = '';
			if ( 'js' == $what ) {
				// date & time picker
				if( !wp_script_is('jquery-timepicker') ) {
					if( 1 ) wp_enqueue_script( 'jquery-timepicker' , $this->the_plugin->cfg['paths']['freamwork_dir_url'] . 'js/jquery.timepicker.v1.1.1.min.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'jquery-ui-slider' ) );
				}

				$asset = "<script type='text/javascript' src='".$this->module_folder."app.auto_import.js' ></script>";
			}
			else {
				$asset = $what;
			}
			if ( empty($asset) ) return;

			if ( $print ) echo $asset;
			else return $asset;
		}
		
		public function print_schedule_button( $pms=array(), $print=true ) {
			extract($pms);

			if ( isset($ver2) && $ver2 ) {
				$asset = '<div class="WooZone-add-to-schedule WooZone-form-button WooZone-form-button-success">' . $title . '</div>';
			}
			else {
				$asset = '<li class="button-block">
					<input type="button" value="' . $title . '" class="WooZone-form-button WooZone-form-button-success WooZone-add-to-schedule" />
				</li>';
			}
			
			if ( $print ) echo $asset;
			else return $asset;
		}
		
		public function print_auto_import_options( $pms=array(), $print=true ) {
			extract($pms);
			
			ob_start();

?>
                                        <li>
                                            <h4><?php _e('Products Import Type', $this->the_plugin->localizationName);?></h4>
                                            <span class="WooZone-checked-product squaredThree">
                                                <input type="radio" value="default" name="import-parameters[prods_import_type]" id="import-parameters-prods_import_type-default" <?php echo $import_params['prods_import_type'] == 'default' ? 'checked="checked"' : ''; ?>></span>
                                            <label for="import-parameters-prods_import_type-default"><?php _e('Do it NOW!', $this->the_plugin->localizationName);?></label>
                                            <br />
                                            <span class="WooZone-checked-product squaredThree">
                                                <input type="radio" value="asynchronous" name="import-parameters[prods_import_type]" id="import-parameters-prods_import_type-asynchronous" <?php echo $import_params['prods_import_type'] == 'asynchronous' ? 'checked="checked"' : ''; ?>></span>
                                            <label for="import-parameters-prods_import_type-asynchronous"><?php _e('Asynchronuous products import', $this->the_plugin->localizationName);?></label>
                                        </li>
<?php
			$asset = ob_get_contents();
			ob_end_clean();

			if ( $print ) echo $asset;
			else return $asset;
		}
		
		public function print_auto_import_screen( $pms=array(), $print=true ) {
			extract($pms);
			
			ob_start();

?>
						<!-- Import Product Screen -->
						<div id="WooZone-import-screen-auto" style="display: none;">

<div class="WooZone-iip-lightbox" id="WooZone-iip-screen-auto">
    <div class="WooZone-iip-in-progress-box">

        <h1><?php _e('Auto-Import products in progress ...', $this->the_plugin->localizationName); ?></h1>
        <p class="WooZone-message WooZone-info WooZone-iip-notice">
        <?php echo sprintf( __('Please be patient while the products are been set in the auto import queue (<i>we add products in queue by <strong>chunks of %s products</strong> per ajax request</i>). 
        Do not navigate away from this page until this script is done. 
        You will be notified via this box when the regenerating is completed.', $this->the_plugin->localizationName), 10 ); ?>
        </p>
        <div class="WooZone-iip-details">
            <table>
                <thead>
                    <tr>
                        <th><span><?php _e('Import Status', $this->the_plugin->localizationName); ?></span></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td id="WooZone-iip-estimate-status-auto">
                            <input type="button" value="<?php _e('STOP', $this->the_plugin->localizationName); ?>" class="WooZone-button red" id="WooZone-import-stop-button">
                            <span><?php _e('the process is running', 'WooZone'); ?></span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="WooZone-iip-process-progress-bar im-products">
            <div class="WooZone-iip-process-progress-marker"></div>
            <div class="WooZone-iip-process-progress-text">
                <span><?php _e('Progress', $this->the_plugin->localizationName); ?>: <span>0%</span></span>
                <span><?php _e('Parsed', $this->the_plugin->localizationName); ?>: <span></span></span>
                <span><?php _e('Elapsed time', $this->the_plugin->localizationName); ?>: <span></span></span>
            </div>
        </div>
      
        <div class="WooZone-iip-tail-auto">
            <ul class="WZC-keyword-attached-auto">
            </ul>
        </div>
        
        <!--<div class="WooZone-iip-log">
        </div>-->
        
    </div>
</div>

						</div>
<?php
			$asset = ob_get_contents();
			ob_end_clean();

			if ( $print ) echo $asset;
			else return $asset;
		}


		/**
		 * Add Search To Schedule
		 */
		public function ajax_request( $retType='die', $pms=array() ) {
            $requestData = array(
                'action'             => isset($_REQUEST['sub_action']) ? $_REQUEST['sub_action'] : '',
            );
            extract($requestData);
			//var_dump('<pre>', $requestData, '</pre>'); die('debug...');

            $ret = array(
                'status'        => 'invalid',
                'html'          => '',
            );

            if ( 'search_get_params' == $action ) {
            	$opStatus = $this->schedule_search_get_params();
				$ret = array_merge($ret, $opStatus);
            }
			else if ( 'search_save_params' == $action ) {
            	$opStatus = $this->schedule_search_save_params();
				$ret = array_merge($ret, $opStatus);
            }
			else if ( 'auto_save_queue' == $action ) {
            	$opStatus = $this->auto_save_queue();
				$ret = array_merge($ret, $opStatus);
            }
			else if ( 'cronjob_stats_queue' == $action ) {
            	$opStatus = $this->get_cronjob_stats_queue();
				$ret = array_merge($ret, array(
					'status'		=> 'valid',
					'html'			=> $opStatus,
				));
            }
			else if ( 'cronjob_stats_search' == $action ) {
            	$opStatus = $this->get_cronjob_stats_search();
				$ret = array_merge($ret, array(
					'status'		=> 'valid',
					'html'			=> $opStatus,
				));
            }

            if ( $retType == 'return' ) { return $ret; }
            else { die( json_encode( $ret ) ); }
		}

		// schedule: get search parameters from form
		public function schedule_search_get_params( $pms=array() ) {
            $requestData = array(
            	// start core search parameters (cannot be deselected)
                'provider'			 => isset($_REQUEST['provider']) ? $_REQUEST['provider'] : 'amazon',
                'country'			 => isset($_REQUEST['country']) ? $_REQUEST['country'] : '',
                'main_aff_id'		 => isset($_REQUEST['main_aff_id']) ? $_REQUEST['main_aff_id'] : '',
                'search_title'		 => isset($_REQUEST['search_title']) ? $_REQUEST['search_title'] : '--Search Unnamed',
                'recurrency'		 => isset($_REQUEST['recurrency']) ? $_REQUEST['recurrency'] : 24,
                'startdate'		 	 => isset($_REQUEST['startdate']) ? $_REQUEST['startdate'] : '',
                'starttime'		 	 => isset($_REQUEST['starttime']) ? $_REQUEST['starttime'] : '',
                // end core search parameters (cannot be deselected)

                'extra_params'		 => isset($_REQUEST['extra_params']) ? $_REQUEST['extra_params'] : '',
                'params'			 => isset($_REQUEST['params']) ? $_REQUEST['params'] : '',
                'import_params'		 => isset($_REQUEST['import_params']) ? $_REQUEST['import_params'] : '',
            );
			
            // search params
            // & import params: import_type, nbimages, nbvariations, spin, attributes, to-category
            // & extra params for schedule
            foreach (array_keys($this->searchParametersGroups) as $what_params) {
	            $params = array();
	            parse_str( ( $requestData["$what_params"] ), $params);
	            if( !empty($params) ) {
		            if( isset($params['WooZone-search'])) {
		                //$requestData = array_merge($requestData, $params['WooZone-search']);
		                $requestData["$what_params"] = $params['WooZone-search'];
		            } else {
	                	//$requestData = array_merge($requestData, $params);
	                	$requestData["$what_params"] = $params;
		            }
	            }
				//unset( $requestData["$what_params"] );
            }

            foreach ($requestData as $rk => $rv) {
				if ( isset($pms["$rk"]) ) {
					$new_val = $pms["$rk"];
                    $new_val = in_array($rk, array()) ? (array) $new_val : $new_val;
                    $requestData["$rk"] = $new_val;
                }
            }
			
			// country
			$country = isset($this->settings['country']) ? (string)$this->settings['country'] : 'com';
			if ( empty($requestData['country']) ) {
				$requestData['country'] = $country;
			}
			$requestData['_country'] = $this->countries["$country"];
			
			// main_aff_id
			$main_aff_id = isset($this->settings['main_aff_id']) ? (string)$this->settings['main_aff_id'] : 'com';
			if ( empty($requestData['main_aff_id']) ) {
				$requestData['main_aff_id'] = $main_aff_id;
			}
			$requestData['_main_aff_id'] = $this->main_aff_ids["$main_aff_id"];
			
			// extra params
			foreach ($this->searchParametersCore as $key => $val) {
				if ( !isset($requestData['extra_params']["$key"]) ) {
					$requestData['extra_params']["$key"] = isset($requestData["$key"]) ? $requestData["$key"] : '';
				}				
			}

            foreach ($requestData as $key => $val) {
                if ( strpos($key, '-') !== false ) {
                    $_key = str_replace('-', '_', $key); 
                    $requestData["$_key"] = $val;
                    unset($requestData["$key"]);
                	$key = $_key;
                }
                
				if ( !empty($val) && is_array($val) ) {
					foreach ($val as $key2 => $val2) {
		                if ( strpos($key2, '-') !== false ) {
		                    $_key2 = str_replace('-', '_', $key2); 
		                    $requestData["$key"]["$_key2"] = $val2;
		                    unset($requestData["$key"]["$key2"]);
							$key2 = $_key2;
		                }
					}
				}
            }
            extract($requestData);
			//var_dump('<pre>', $requestData, '</pre>'); die('debug...');
			
			$search_params = array_diff_key($requestData, $this->searchParametersCore);
			//var_dump('<pre>', $search_params, '</pre>'); die('debug...');

			$search_params_ = $search_params;
			foreach ( $search_params_ as $k => $v ) {
				if ( empty($v) || !is_array($v) ) {
					unset($search_params_["$k"]);
					continue 1;
				}
				foreach ($v as $key => $val) {
					$val = is_array($val) ? $val : trim($val);
					if ( empty($val) && !in_array($key, array('keyword')) ) {
						unset($search_params_["$k"]["$key"]);
						continue 1;
					}
					
					if ( '_' == substr($key, 0, 1) ) {
						$__ = substr($key, 1);
						if (!empty($val) && isset($search_params_["$k"]["$__"])) {
							$search_params_["$k"]["$__"] = $val;
							unset( $search_params_["$k"]["$key"] );
						}
					}
				}
			}
			//var_dump('<pre>', $search_params_, '</pre>'); die('debug...');

            $ret = array(
                'status'        => 'invalid',
                'html'          => '',
            );


            $css = array();
            $css['container'] = '';
			
			$html = array();
			//$html[] = '<div class="WooZone-big-overlay-lightbox '.$css['container'].'">';
			$html[] = 	'<div class="WooZone-donwload-in-progress-box">';
			$html[] = 		'<h1>' . __('Add Search to schedule', $this->localizationName ) . '<a href="#" class="WooZone-form-button WooZone-form-button-danger" id="WooZone-close-btn">' . __('CLOSE', $this->localizationName ) . '</a></h1>';
			
			//$html[] = 		'<p class="WooZone-message WooZone-info WooZone-donwload-notice">';
			//$html[] = 		__('Search Parameters', $this->localizationName );
			//$html[] = 		'</p>';
			
			$html[] = 		'<form id="WooZone-search-add-schedule" class="WooZone-search-add-schedule">';
			//$html[] = 		'<h2 class="WooZone-process-headline">' . __('Debugging Information:', $this->localizationName ) . '</h2>';
			$html[] = 		'<div class="WooZone-autoimport-search-params">';
			foreach ( $search_params_ as $k => $v ) {
				
				$searchGroupTitle = isset($this->searchParametersGroups["$k"]) ? $this->searchParametersGroups["$k"]
					: __('Parameters', $this->localizationName );
				$html[] = 		'<table class="WooZone-table WooZone-debug-info">';
				$html[] = 			'<thead>';
				$html[] = 				'<tr>';
				//$html[] = 					'<th width="250">' . __('Parameter Name', $this->localizationName ) . '</th>';
				//$html[] = 					'<th>' . __('Parameter Value', $this->localizationName ) . '</th>';
				$html[] = 					'<th colspan="2">' . $searchGroupTitle . '</th>';
				$html[] = 				'</tr>';
				$html[] = 			'</thead>';
				$html[] = 			'<tbody>';
			
				foreach ($v as $key => $val) {
					$val = is_array($val) ? $val : trim($val);
					$val_orig = $search_params["$k"]["$key"];

					if ( isset($this->searchParameters["$key"], $this->searchParameters["$key"]['title']) ) {
						$nice_name = $this->searchParameters["$key"]['title'];
					} else {
						$nice_name = $this->the_plugin->__category_nice_name( $key );
					}
					$nice_name = trim($nice_name);
					
					$readonly = '';
					if ( in_array($key, array_keys($this->searchParametersCore))
						|| (
							isset($this->searchParameters["$key"], $this->searchParameters["$key"]['readonly'])
							&& $this->searchParameters["$key"]['readonly'] )
					) {
						$readonly = 'readonly="readonly"';
					}
					
					$elem = array(
						'chk'			=> array(
							'name' 			=> "sschedule_stat[$k][$key]",
							'id' 			=> "sschedule_stat[$k][$key]",
						),
						'param'			=> array(
							'name' 			=> "sschedule[$k][$key]",
							'id' 			=> "sschedule[$k][$key]",
							'value'			=> $val_orig,
						),
						'_param'		=> array(
							'name' 			=> "sschedule[$k][_$key]",
							'id' 			=> "sschedule[$k][_$key]",
							'value'			=> $val,
						),
					);

					// BrowseNode list/tree parameter
					$is_bnl = false; // parameter is browse node list?
					if ( 'BrowseNode_list' == $key ) {
						if ( !empty($val_orig) ) {

							$browsenode_list = array();
							foreach ($val_orig as $key2 => $val2) {

								$browsenode_list['hidden'][] = '<input type="hidden" name="' . $elem['param']['name'] . '['.$key2.']" id="' . $elem['param']['id'] . '['.$key2.']" value="' . $elem['param']['value'][$key2] . '"/>';
								if ( isset($search_params["$k"]["_$key"]) ) {
									$browsenode_list['hidden'][] = '<input type="hidden" name="' . $elem['_param']['name'] . '['.$key2.']" id="' . $elem['_param']['id'] . '['.$key2.']" value="' . $elem['_param']['value'][$key2] . '"/>';
								}

								$browsenode_list['show'][] = $elem['_param']['value'][$key2];
							}
							/*
							$html[] = 			'<tr>';
							$html[] = 				'<td class="WooZone-bn">' . $nice_name . '</td>';
							$html[] = 				'<td>';
							
							$html[] = 				implode(PHP_EOL, $browsenode_list['hidden']);
							$html[] = 				implode(' &gt; ', $browsenode_list['show']);
								
							$html[] = 				'</td>';
							$html[] = 			'</tr>';
							*/
							$readonly = 'style="visibility: hidden;"';
							$is_bnl = true;
						}
					}
					// All the Other parameters
					//else {
					if (1) {
						$is_editable = false; // parameter is editable?
						if ( isset($this->searchParameters["$key"], $this->searchParameters["$key"]['editable'])
							&& $this->searchParameters["$key"]['editable'] ) {
							$is_editable = true;
						}

						$html[] = 			'<tr>';
						$html[] = 				'<td width="250">';
						
						$html[] = 					'<input type="checkbox" name="' . $elem['chk']['name'] . '" id="' . $elem['chk']['id'] . '" checked="checked" '.$readonly.'/>';
						$html[] = 					'<label for="' . $elem['chk']['id'] . '">' . ($nice_name) . '</label>';
						
						if ($is_editable) {
							$__el = $this->searchParameters["$key"];
							$__el_type = $__el['type'];
							$__el_value = 'select' == $__el_type ? $__el['options'] : '';
							$__el_default = $elem['_param']['value'];
			                $__el_extra = array(
			                    'global_desc'       => '',
			                    'desc'              => '',
			                    
								'field_name'		=> $elem['param']['name'],
								'field_id'			=> $elem['param']['id'],
			                );

							$editable_html = $this->build_searchform_element( $__el_type, $key, $__el_value, $__el_default, $__el_extra );
						}
						else {
							if ($is_bnl) {
								$html[] = 			implode(PHP_EOL, $browsenode_list['hidden']);
							}
							else {
								$html[] = 			'<input type="hidden" name="' . $elem['param']['name'] . '" id="' . $elem['param']['id'] . '" value="' . $elem['param']['value'] . '"/>';
								if ( isset($search_params["$k"]["_$key"]) ) {
									$html[] = 		'<input type="hidden" name="' . $elem['_param']['name'] . '" id="' . $elem['_param']['id'] . '" value="' . $elem['_param']['value'] . '"/>';
								}
							}
						}
						
						$html[] = 				'</td>';
						if ($is_editable) {
							$html[] = 			'<td>' . ( $editable_html ) . '</td>';
						}
						else {
							if ($is_bnl) {
								$html[] = 		'<td>' . implode(' &gt; ', $browsenode_list['show']) . '</td>';
							}
							else {
								$html[] = 		'<td>' . ( $elem['_param']['value'] ) . '</td>';								
							}
						}
						$html[] = 			'</tr>';
					}
				}

				$html[] = 			'</tbody>';
				/*
				$html[] = 			'<tfoot>';
				$html[] = 				'<tr>';
				$html[] = 					'<td></td>';
				$html[] = 					'<td><input type="submit" value="' . __('Save Search to schedule', $this->the_plugin->localizationName) . '" class="WooZone-button green" /></td>';
				$html[] = 				'</tr>';
				$html[] = 			'</tfoot>';
				*/
				$html[] = 		'</table>';
			}
			$html[] = 		'</div>';
			
			$html[] = 		'<div class="WooZone-autoimport-search-button">';
			$html[] =			'<input type="submit" value="' . __('Save Search to schedule', $this->the_plugin->localizationName) . '" class="WooZone-form-button WooZone-form-button-success" />';
			$html[] = 		'</div>';
			
			$html[] = 		'<div class="WooZone-autoimport-search-msg">';
			$html[] = 		'</div>';
			
			$html[] = 		'</form>';

			$html[] = 	'</div>';
			//$html[] = '</div>';
			
			$ret = array_merge($ret, array(
				'status' 	=> 'valid',
				'html'		=> implode("\n", $html)
			));
			return $ret;
		}

		// schedule: save search parameters to queue
		public function schedule_search_save_params( $pms=array() ) {
            $requestData = array(
                'allparams'		 => isset($_REQUEST['allparams']) ? $_REQUEST['allparams'] : '',

                'sschedule_stat' => array(),
                'sschedule'		 => array(),
                '_theparams'	 => array(),
            );
			
			$allparams = array();
			parse_str( ( $requestData["allparams"] ), $allparams);
			$requestData['allparams'] = $allparams;
			//var_dump('<pre>', $requestData, '</pre>'); die('debug...');

            // extra_params (extra general parameters) & params (search parameters) & import_params (import parameters)
			$requestData['sschedule_stat'] = isset($requestData['allparams']['sschedule_stat']) ? $requestData['allparams']['sschedule_stat']
				: array();
			$requestData['sschedule'] = isset($requestData['allparams']['sschedule']) ? $requestData['allparams']['sschedule']
				: array();

			// only selected parameters
			$_theparams = array();
			foreach ($requestData['sschedule_stat'] as $key => $val) {
				foreach ($val as $key2 => $val2) {
					if ( isset($val2) ) {
						if ( isset($requestData['sschedule']["$key"]["$key2"]) ) {
							$_theparams["$key"]["$key2"] = $requestData['sschedule']["$key"]["$key2"];
						}
						if ( isset($requestData['sschedule']["$key"]["_$key2"]) ) {
							$_theparams["$key"]["_$key2"] = $requestData['sschedule']["$key"]["_$key2"];
						}
					}
				}
			}
			$requestData['_theparams'] = $_theparams;
			
			// overwrite parameters
            foreach ($requestData as $rk => $rv) {
				if ( isset($pms["$rk"]) ) {
					$new_val = $pms["$rk"];
                    $new_val = in_array($rk, array()) ? (array) $new_val : $new_val;
                    $requestData["$rk"] = $new_val;
                }
            }
            extract($requestData);
			//var_dump('<pre>', $requestData, '</pre>'); die('debug...');
			
            $ret = array(
                'status'        => 'invalid',
                'html'          => '',
            );

			global $wpdb;
			$table_name = $wpdb->prefix . "amz_search";

			if (1) {
				$code = md5( serialize( $_theparams ) );

				// try to insert asin to queue
				$checkSql = "SELECT id FROM {$table_name} WHERE 1=1 AND code = %s;";
				$checkSql = $wpdb->prepare( $checkSql, $code );

				// does NOT exists in queue
				$status_text = ''; $status = 'invalid';
				if( !$wpdb->get_var($checkSql) ) {
					if (1) {
						// status: new, failed, done, running
						$insertSql = $this->the_plugin->db_custom_insert(
			               	$table_name,
			               	array(
			               		'values' => array(
									'code' 			=> $code,
									'publish' 		=> 'Y',
									'status'		=> 'new',
									'status_msg'	=> '',
									'params'		=> serialize( $_theparams ),
									'provider'		=> $_theparams['extra_params']['provider'],
									'search_title'	=> $_theparams['extra_params']['search_title'],
									'country'		=> $_theparams['extra_params']['country'],
									'recurrency'	=> $_theparams['extra_params']['recurrency'],
									'nb_tries'		=> '0',
									//'started_at'	=> '',
									//'ended_at'		=> '',
									//'run_date'		=> '',
								),
								'format' => array(
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
								)
			                ),
			                true
			            );
						$status_text = __('search was added', $this->the_plugin->localizationName);
						$status = 'valid';
					}
				}
				// already exists in queue
				else {
					$status_text = __('search already exists', $this->the_plugin->localizationName);
					$status = 'invalid';
				}
			}

			$ret = array_merge($ret, array(
				'status' 	=> 'valid',
				'html'		=> '<span class="WooZone-message ' . ('invalid' == $status ? 'WooZone-error' : 'WooZone-success') . '">' . $status_text . '</span>'
			));
			return $ret;
		}

		// auto import: save in queue
		public function auto_save_queue( $pms=array() ) {
			//$this->the_plugin->timer_start(); // Start Timer

            $requestData = array(
                'asins'		 		 => isset($_REQUEST['asins']) ? $_REQUEST['asins'] : '',
                'operation_id'		 => isset($_REQUEST['operation_id']) ? $_REQUEST['operation_id'] : '',
                'is_init'		 	 => isset($_REQUEST['is_init']) ? (int) $_REQUEST['is_init'] : 0,
                'params'             => isset($_REQUEST['params']) ? $_REQUEST['params'] : '', // import params
                'from_op'			 => isset($_REQUEST['from_op']) ? $_REQUEST['from_op'] : '',
            );

            // params: import_type, nbimages, nbvariations, spin, attributes, to-category
            $params = array();
            parse_str( ( $requestData['params'] ), $params);
			$requestData['params'] = $params;

            //if( !empty($params) ) {
            //    $requestData = array_merge($requestData, $params);
            //}
            foreach ($requestData as $rk => $rv) {
				if ( isset($pms["$rk"]) ) {
					$new_val = $pms["$rk"];
                    $new_val = in_array($rk, array()) ? (array) $new_val : $new_val;
                    $requestData["$rk"] = $new_val;
                }
            }

            if ( !empty($requestData['asins']) ) {
            	if ( !is_array($requestData['asins']) && substr_count($requestData['asins'], ',') ) {
                	$requestData['asins'] = explode(',', $requestData['asins']);
				}
            } else {
                $requestData['asins'] = array();
            }
            $requestData['asins'] = array_unique(array_filter($requestData['asins']));

			extract($requestData);
			
			$from_op_ = isset($from_op) && !empty($from_op) ? str_replace('auto#', '', $from_op) : $operation_id;

            $ret = array(
                'status'        => 'invalid',
                'msg'          => '',
            );
			
            // status messages
            $this->the_plugin->opStatusMsgInit(array(
            	'keep_msg'		=> $is_init ? false : true,
				'operation_id'  => $requestData['operation_id'],
				'operation'     => isset($requestData['from_op']) ? $requestData['from_op'] : 'auto_import',
				//'msg_header'    => __('Add Products to Import Queue', 'WooZone'),
			));
			
			// save import params
			if ( !empty($asins) && !empty($params) ) {
				$import_params_db = get_option('WooZone_ai_import_params', array());
				$import_params_db = is_array($import_params_db) ? $import_params_db : array();
				$import_params_db["$from_op_"] = $params;
				update_option('WooZone_ai_import_params', $import_params_db);
			}


			global $wpdb;
			$table_name = $wpdb->prefix . "amz_queue";
			
			// verify asins exists in queue
			$asins_db = array();
			if ( !empty($asins) ) {
				$asins_ = implode(',', array_map(array($this->the_plugin, 'prepareForInList'), $asins));

				$sql = "select a.id, a.asin from " . $table_name . " as a where 1=1 and a.asin in ($asins_) and a.from_op = %s;";
				$sql = $wpdb->prepare( $sql, $from_op );
				$res = $wpdb->get_results( $sql, OBJECT_K );
				//var_dump('<pre>', $res, '</pre>'); die('debug...');  
				if ( !empty($res) ) {
					foreach ($res as $k => $v) {
						$asin = $v->asin;
						$asins_db["$asin"] = $v;
					}
				}
			}
			
			// verify asin exists in wp_posts table as wp_postmeta _amzASIN
			//$asins_post = array();
			$asins_post = WooZone_product_by_asin( $asins );
 
			// add asins to queue
			foreach ($asins as $asin) {
				// try to insert asin to queue
				//$checkSql = "SELECT id FROM {$table_name} WHERE 1=1 AND asin = %s AND from_op = %s;";
				//$checkSql = $wpdb->prepare( $checkSql, $asin, $from_op );

				// does NOT exists in queue
				$status_text = '';
				//if( !$wpdb->get_var($checkSql) ) {
				if ( !isset($asins_db["$asin"]) ) {
					// already exists in wp_posts
					if ( isset($asins_post["$asin"]) && !empty($asins_post["$asin"]) ) {
						$status_text = __('product already exists', $this->the_plugin->localizationName);
					}
					// does NOT exists in wp_posts
					else {
						// status: new, failed, done, running
						$insertSql = $this->the_plugin->db_custom_insert(
			               	$table_name,
			               	array(
			               		'values' => array(
									'asin' 			=> $asin, 
									'status' 		=> 'new',
									'status_msg'	=> '',
									'from_op'		=> $from_op,
									'created_date'	=> date("Y-m-d H:i:s"),
								),
								'format' => array(
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
								)
			                ),
			                true
			            );
						$status_text = __('added in queue', $this->the_plugin->localizationName);
					}
				}
				// already exists in queue
				else {
					$status_text = __('already exists in queue', $this->the_plugin->localizationName);
				}
				
                // status messages
                $this->the_plugin->opStatusMsgSet(array(
                    'status'    => 'valid',
                    'msg'       => '<li><strong>' . $asin . '</strong> : ' . $status_text . '</li>',
                ));
				
				// DEBUG
				//usleep(3000000);
			}

            $opStatusMsg = $this->the_plugin->opStatusMsgGet();

			$ret = array_merge($ret, array(
				'status' 	=> 'valid',
				'msg'		=> $opStatusMsg['msg']
			));
			return $ret;
		}


		/**
		 * Utils
		 */
        private function build_select( $param, $values, $default='', $extra=array() ) {
            $extra = array_replace_recursive(array(
                'prefix'        => 'WooZone-search',
                'desc'          => array(),
                'nodeid'        => array(),
                
                'field_name'			=> '',
                'field_id'				=> '',
            ), $extra);
            extract($extra);

            $html = array();
            if (empty($values) || !is_array($values)) return '';
            foreach ($values as $k => $v) {
                
                $__selected = ($k == $default ? ' selected="selected"' : '');
                $__desc = (!empty($desc) && isset($desc["$k"]) ? ' data-desc="'.$desc["$k"].'"' : '');
                $__nodeid = (!empty($nodeid) && isset($nodeid["$k"]) ? ' data-nodeid="'.$nodeid["$k"].'"' : '');
                $html[] = '<option value="' . $k . '"' . $__selected . $__desc . $__nodeid . '>' . $v . '</option>';
            }
            return implode('', $html);
        }

        private function build_input_text( $param, $placeholder, $default='', $extra=array() ) {
            $extra = array_replace_recursive(array(
                'prefix'        => 'WooZone-search',
                'desc'          => array(),
                'nodeid'        => array(),
                
                'field_name'			=> '',
                'field_id'				=> '',
            ), $extra);
            extract($extra);

            $name = $prefix.'['.$param.']';
            $id = "$prefix-$param";
			if ( isset($field_name) && !empty($field_name) ) $name = str_replace('%s', $param, $field_name);
			if ( isset($field_id) && !empty($field_id) ) $id = str_replace('%s', $param, $field_id);

            return '<input placeholder="' . $placeholder . '" name="' . $name . '" id="' . $id . '" type="text" value="' . (isset($default) && !empty($default) ? $default : '') . '"' . '>';
        }
		
        public function build_searchform_element( $elm_type, $param, $value, $default, $extra=array() ) {
            $extra = array_replace_recursive(array(
                'prefix'        		=> 'WooZone-search',
                'global_desc'           => '',
                'desc'                  => array(),

                'field_name'			=> '',
                'field_id'				=> '',
            ), $extra);
            extract($extra);

            $css = array();
            /*$fa = 'fa-bars';
            if ( $param == 'Sort' ) {
                $fa = 'fa-sort';
            } else if ( $param == 'BrowseNode' ) {
                $fa = 'fa-sitemap';
                $css[] = 'WooZone-param-node';
            }*/
            $css = !empty($css) ? ' ' .implode(' ', $css) : '';
			
            $name = $prefix.'['.$param.']';
            $id = "$prefix-$param";
			if ( isset($field_name) && !empty($field_name) ) $name = str_replace('%s', $param, $field_name);
			if ( isset($field_id) && !empty($field_id) ) $id = str_replace('%s', $param, $field_id);
            
            $html = array();
            //$html[] = '<li class="WooZone-param-optional'.$css.'">';
            //$html[] =       '<span class="tooltip" title="'.$global_desc.'" data-title="'.$global_desc.'"><i class="fa '.$fa.'"></i></span>';
            $nice_name = $this->the_plugin->__category_nice_name( $param );
            if ( $elm_type == 'input' ) {
                //$value = $nice_name;
                $html[] =   $this->build_input_text( $param, $value, $default, $extra );
            } else if ( $elm_type == 'select' ) {
                $html[] =   '<select id="'.$id.'" name="'.$name.'">';
                $html[] =       '<option value="" disabled="disabled">'.$nice_name.'</option>';
                $html[] =   $this->build_select( $param, $value, $default, $extra );
                $html[] =   '</select>';
            }
            //$html[] = '</li>';
            return implode('', $html);
        }
	
	
		/**
		 * Cronjob
		 */
		public function cronjob_queue( $pms, $return='die' ) {
		    $ret = array('status' => 'failed');

            //$current_cron_status = $pms['status']; //'new'; //

            if ( !WooZone()->can_import_products() || WooZone()->is_aateam_demo_keys() ) {
	            //$ret = array_merge($ret, array(
	            //    'status'            => 'done',
	            //));
	            return $ret;
			}

			// cron stats
			$cron_stats_db = get_option('WooZone_ai_cron_stats', array());
			$cron_stats_db = is_array($cron_stats_db) ? $cron_stats_db : array();
			$cron_stats_db['start_time'] = time();
			$cron_stats_db['end_time'] = '';
			$cron_stats_db['current_chunk'] = array();
			$cron_stats_db['current_chunk_row'] = '';
			$cron_stats_db['queue_rows_new'] = 0;

            global $wpdb;

			$table = $wpdb->prefix  . 'amz_queue';
			
			$sql_new = "SELECT count(a.id) as nb FROM " . $table . " as a WHERE 1=1 AND a.status = 'new' AND a.nb_tries < %s;";
			$sql_new = $wpdb->prepare( $sql_new, self::$max_nb_tries );
			$res_new = $wpdb->get_var( $sql_new );
			$cron_stats_db['queue_rows_new'] = (int) $res_new;
			
			$sql = "SELECT a.* FROM " . $table . " as a WHERE 1=1 AND a.status IN ('new', 'error') AND a.nb_tries < %s ORDER BY a.nb_tries ASC, a.id ASC LIMIT 0, ".self::$queue_chunk_nb.";";
			$sql = $wpdb->prepare( $sql, self::$max_nb_tries );
			$res = $wpdb->get_results( $sql, ARRAY_A);
			//var_dump('<pre>',$res,'</pre>');
			if (empty($res)) {
				//$cron_stats_db['current_chunk'] = array();
				//$cron_stats_db['current_chunk_row'] = '';
				update_option('WooZone_ai_cron_stats', $cron_stats_db);
				return $ret;
			}
			
			// search parameters
			$this->get_import_params_search( $res );

			// get import params
			$import_params = array_merge(
				array(),
				array(
               		//'params'		=> '',
               		'operation_id'	=> round(microtime(true) * 1000), // in miliseconds - as in javascript
				)
			);
			
			// get products from auto import queue
			$is_cron_assets = false;
			$prods = $res;
			unset($res);
			foreach ($prods as $key => $row) {
				$asin = $row['asin'];
				$from_op = $row['from_op'];

				// get import params
				$import_params = array_merge(
					$import_params,
					array(
                		'asin'		=> $asin,
					),
					$this->get_import_params( $from_op )
				);
				$prods["$key"]['__import_params'] = $import_params;
				
				if ( 'default' == $import_params['import_type'] && !$is_cron_assets ) {
					$is_cron_assets = true;
				}
				
				$cron_stats_db['current_chunk'][] = $row['id'];
			}
			if (empty($prods)) return $ret;
			//var_dump('<pre>',$prods,'</pre>');

			// insane import mode module
			// Initialize the WooZoneInsaneImport class
			require_once( $this->the_plugin->cfg['paths']['plugin_dir_path'] . '/modules/insane_import/init.php' );
			$WooZoneInsaneImport = new WooZoneInsaneImport(true);
			//$WooZoneInsaneImport = WooZoneInsaneImport::getInstance();
			
			if ( $is_cron_assets ) {
			// assets download module
			// Initialize the WooZoneAssetDownload class
			require_once( $this->the_plugin->cfg['paths']['plugin_dir_path'] . '/modules/assets_download/init.php' );
			$WooZoneAssetDownload = new WooZoneAssetDownload(true);
			//$WooZoneAssetDownload = WooZoneAssetDownload::getInstance();
			}
			
			foreach ($prods as $key => $row) {
				$status_msg = array();
				
				// import product
				$import_stat = $WooZoneInsaneImport->import_product( 'return', $row['__import_params'] );
				$product_id = isset($import_stat['product_id']) ? $import_stat['product_id'] : 0;
				$status_msg[] = $import_stat['msg']; 
				
				// download product assets - if not asynchronous
				if ( 'default' == $import_params['import_type'] && $product_id > 0 ) {
					if ( !$this->the_plugin->is_remote_images ) {
						$assets_stat = $WooZoneAssetDownload->product_assets_download( $product_id );
						$status_msg[] = $assets_stat['msg'];
					}
				}
  
				// update queue table
				// fields to update: status, status_msg, imported_date, nb_tries
				$wpdb->update( 
					$table, 
					array( 
						'status' 			=> ($product_id > 0 ? 'done' : ($product_id == -1 ? 'already' : 'error')),
						'status_msg'		=> serialize( implode('<br /><br />', $status_msg) ),
						'imported_date'		=> date("Y-m-d H:i:s"),
						'nb_tries'			=> (int) ($row['nb_tries'] + 1),
						'nb_tries_prev'		=> (int) ($row['nb_tries_prev'] + 1),
					), 
					array( 'id' => $row['id'] ), 
					array(
						'%s',
						'%s',
						'%s',
						'%d',
						'%d',
					), 
					array( '%d' ) 
				);
				
				$cron_stats_db['current_chunk_row'] = $row['id'];
				update_option('WooZone_ai_cron_stats', $cron_stats_db);
			}

			$cron_stats_db['end_time'] = time();
			update_option('WooZone_ai_cron_stats', $cron_stats_db);
			
            $ret = array_merge($ret, array(
                'status'            => 'done',
            ));
            return $ret;
		}

		public function cronjob_search( $pms, $return='die' ) {
		    $ret = array('status' => 'failed');

            //$current_cron_status = $pms['status']; //'new'; //
            
            if ( !WooZone()->can_import_products() || WooZone()->is_aateam_demo_keys() ) {
	            //$ret = array_merge($ret, array(
	            //    'status'            => 'done',
	            //));
	            return $ret;
			}

			// cron stats
			$cron_stats_db = get_option('WooZone_ai_cron_stats_search', array());
			$cron_stats_db = is_array($cron_stats_db) ? $cron_stats_db : array();
			$cron_stats_db['start_time'] = time();
			$cron_stats_db['end_time'] = '';
			$cron_stats_db['current_chunk'] = array();
			$cron_stats_db['current_chunk_row'] = '';

            global $wpdb;

			$table = $wpdb->prefix  . 'amz_search';
   
			$now = date("Y-m-d H:i:s");
			
			//isnull(ended_at) || ( $now >= date_add( date_format( ended_at, '%Y-%m-%d %H:%i:%s' ), interval $recurrency hour ) )
			$sql = "SELECT a.*, ( isnull(a.run_date) || '$now' >= a.run_date ) as is_run_time FROM " . $table . " as a WHERE 1=1 AND a.publish ='Y' AND ( ( isnull(a.run_date) || a.run_date = '0000-00-00 00:00:00' || '$now' >= a.run_date ) OR ( a.status IN ('new', 'error') AND a.nb_tries < %s ) ) ORDER BY a.nb_tries ASC, a.id ASC LIMIT 0, ".self::$queue_chunk_nb_search.";";
			$sql = $wpdb->prepare( $sql, self::$max_nb_tries );
			$res = $wpdb->get_results( $sql, ARRAY_A);
			//var_dump('<pre>',$res,'</pre>'); die;
			if (empty($res)) {
				//$cron_stats_db['current_chunk'] = array();
				//$cron_stats_db['current_chunk_row'] = '';
				update_option('WooZone_ai_cron_stats_search', $cron_stats_db);
				return $ret;
			}
			$prods = $res;
			unset($res);
			//var_dump('<pre>',$prods,'</pre>');
   
			// insane import mode module
			// Initialize the WooZoneInsaneImport class
			require_once( $this->the_plugin->cfg['paths']['plugin_dir_path'] . '/modules/insane_import/init.php' );
			$WooZoneInsaneImport = new WooZoneInsaneImport(true);
			//$WooZoneInsaneImport = WooZoneInsaneImport::getInstance();
 
			foreach ($prods as $key => $row) {
				$status_msg = array();

				// update table - start
				// fields to update: status, status_msg, nb_tries...
				$wpdb->update( 
					$table, 
					array( 
						'started_at'		=> date("Y-m-d H:i:s"),
					), 
					array( 'id' => $row['id'] ), 
					array(
						'%s',
					), 
					array( '%d' ) 
				);

				// get search parameters
				$searchParams = $this->get_search_params( $row['params'] );
				$searchParams_ = array_merge(
					array(),
					array(
	                	'params'                => '',
	                	'operation'             => '',
	                	'operation_id'          => time(),
	                	'asins_inqueue'         => '',
	                	'page'                  => 0,
            		),
            		$searchParams['params']
				);

				//DEBUG
				//var_dump('<pre>',$searchParams_,'</pre>'); continue 1;
				
				// validate search
				$search_stat = $this->validate_search_params( $searchParams['params'] );
				if ( !$search_stat ) { // INVALID
					$search_stat = array('status' => 'invalid');
					$status_msg[] = __('Invalid search parameters!', $this->the_plugin->localizationName);
				}
				else { // VALID
					// run search
					$WooZoneInsaneImport->setupAmazonWS(array(
						'country' 		=> $searchParams['country'],
						'main_aff_id'	=> $searchParams['main_aff_id'],
					));

					// DEBUG
					//var_dump('<pre>', $WooZoneInsaneImport->amzHelper->aaAmazonWS, '</pre>');
					//continue 1;

					$search_stat = $WooZoneInsaneImport->loadprods_queue_by_search( 'return', $searchParams_ );
					$status_msg[] = $search_stat['msg'];
					//var_dump('<pre>',$row['id'], $search_stat,'</pre>'); die;
	
					// add search products to queue
					$asins = isset($search_stat['asins'], $search_stat['asins']['loaded'])
						? (array) $search_stat['asins']['loaded'] : array();
					$this->auto_save_queue(array(
	                	'asins'		 		 => $asins,
	                	'operation_id'		 => 'search#'.$row['id'], //time(),
	                	'is_init'		 	 => 1,
	                	'params'             => '', // import params
	                	'from_op'			 => 'search#'.$row['id'],
	            	));
            	}
				
				// update table - end
				// fields to update: status, status_msg, nb_tries...
				$status_s = 'valid' == $search_stat['status'] ? 'done' : 'error';
				$nb_tries = 'done' == $status_s || $row['is_run_time'] ? 0 : (int) ($row['nb_tries'] + 1);
				$recurrency = $row['recurrency'];
				
				// deprecated: to update 'run_date' field by a php value is not optimal
				/*
				$wpdb->update( 
					$table, 
					array( 
						'status' 			=> $status_s,
						'status_msg'		=> serialize( implode('<br /><br />', $status_msg) ),
						'nb_tries'			=> (int) ($row['nb_tries'] + 1),
						//'nb_tries_prev'		=> (int) ($row['nb_tries_prev'] + 1),
						'ended_at'			=> date("Y-m-d H:i:s"),
						'run_date'			=> '',
					), 
					array( 'id' => $row['id'] ), 
					array(
						'%s',
						'%s',
						'%d',
						'%s',
						'%s',
					), 
					array( '%d' ) 
				);
			 	*/
				$sql_upd = "UPDATE " . $table . " as a SET a.status = %s, a.status_msg = %s, a.nb_tries = %s, a.ended_at = %s, a.run_date = date_add( date_format( a.ended_at, '%s' ), interval $recurrency hour ) WHERE 1=1 AND a.id = %s;";
				$sql_upd = $wpdb->prepare($sql_upd,
					$status_s,
					serialize( implode('<br /><br />', $status_msg) ),
					(int) ($row['nb_tries'] + 1),
					date("Y-m-d H:i:s"),
					'%Y-%m-%d %H:%i:%s',
					
					$row['id']
				);
				$wpdb->query( $sql_upd );
				
				$cron_stats_db['current_chunk_row'] = $row['id'];
				update_option('WooZone_ai_cron_stats_search', $cron_stats_db);
			}

			$cron_stats_db['end_time'] = time();
			update_option('WooZone_ai_cron_stats_search', $cron_stats_db);
			
            $ret = array_merge($ret, array(
                'status'            => 'done',
            ));
            return $ret;
		}

		private function get_search_params( $params ) {
			$ret = array(
				'country'		=> '',
				'main_aff_id'	=> '',
				'params'		=> '',
			);
			
			$params_db = !empty($params) ? maybe_unserialize( $params ) : array();
			$params_db = is_array($params_db) ? $params_db : array();

			$country = isset($params_db['extra_params'], $params_db['extra_params']['country'])
				? $params_db['extra_params']['country'] : $this->settings['country'];

			$main_aff_id = isset($params_db['extra_params'], $params_db['extra_params']['main_aff_id'])
				? $params_db['extra_params']['main_aff_id'] : $this->settings['main_aff_id'];
				  
			$params_db = isset($params_db['params'])
				? $params_db['params'] : array();
			foreach ($params_db as $key => $val) {
				if ( '_' == $key[0] ) {
					unset($params_db["$key"]);
				}
			}
			if ( !isset($params_db['keyword']) ) { // mandatory field for amazon request
				$params_db['keyword'] = '';
			}
			//$params_db = array_unique(array_filter($params_db));

			$ret['country'] = $country;
			$ret['main_aff_id'] = $main_aff_id;
			$ret['params'] = $params_db;
			return $ret;
		}
		
		private function validate_search_params( $params ) {
			if (empty($params) || !is_array($params)) return false;

			$status = true;
			// mandatory fields
			foreach (array('category', 'keyword', 'nbpages') as $field) {
				if ( !isset($params["$field"]) ) {
					$status = false;
					break 1;
				}
			}
			return $status;
		}

		private function __parse_from( $from ) {
			$_from = explode('#', $from);
			$id = 0; $from = '';
			if ( is_array($_from) && count($_from) > 1 ) {
				$id = $_from[1]; $from = $_from[0];
			}
			return compact('id', 'from');
		}

		private function get_import_params_search( $rows ) {
			$ret = array();
			
			$search_ids = array();
			foreach ($rows as $key => $row) {
				$from = $this->__parse_from($row['from_op']);
				if ( 'search' != $from['from'] ) continue 1;
				
				$search_ids[] = $from['id'];
			}
			$search_ids = array_unique( array_filter( $search_ids ) );
     
			if ( !empty($search_ids) ) {
				global $wpdb;

				$search_ids_ = implode(',', array_map(array($this->the_plugin, 'prepareForInList'), $search_ids));
				
				$sql = "select a.id, a.params from " . $wpdb->prefix.'amz_search' . " as a where 1=1 and a.id in ($search_ids_);";
				$ret = $wpdb->get_results( $sql, OBJECT_K );
			}

			$this->queue_chunk_search = $ret;
		}

		private function get_import_params_default() {
			$import_params = array();
			{
				{
					{
						{
                            $amz_settings = $this->settings;
                            $import_params = array(
                                'spin_at_import'            => false,
                                'import_attributes'         => false,
                                'import_type'               => 'default',
                                'number_of_images'          => 'all',
                                'number_of_variations'      => 'no',
                                'prods_import_type'			=> 'default',
                            );
                            
                            // download images
                            $import_type = 'default';
                            if ( isset($amz_settings['import_type']) && $amz_settings['import_type']=='asynchronous' ) {
                                $import_type = $amz_settings['import_type' ];
                            }
                            $import_params['import_type'] = $import_type;
                                
                            // number of images
                            $number_of_images = (
                                isset($amz_settings["number_of_images"]) && (int) $amz_settings["number_of_images"] > 0
                                ? (int) $amz_settings["number_of_images"] : 'all'
                            );
                            if ( $number_of_images > 100 ) $number_of_images = 'all';
                            $import_params['number_of_images'] = $number_of_images;
                            
                            // number of variations
                            $variationNumber = isset( $amz_settings['product_variation'] ) ? $amz_settings['product_variation'] : 'no';
                            // convert $variationNumber into number
                            if( $variationNumber == 'yes_all' ){
                                $variationNumber = 'all'; // 100 variation is enough
                            }
                            elseif( $variationNumber == 'no' ){
                                $variationNumber = 0;
                            }
                            else{
                                $variationNumber = explode(  "_", $variationNumber );
                                $variationNumber = (int) end( $variationNumber );
                                if ( $variationNumber > 100 ) $variationNumber = 'all';
                            }
                            $import_params['number_of_variations'] = $variationNumber;
                            
                            // spin at import
                            $spin_at_import = isset($amz_settings['spin_at_import']) && $amz_settings['spin_at_import'] == 'yes' ? true : false;
                            $import_params['spin_at_import'] = $spin_at_import;
                            
                            // import attributes
                            $import_attributes = isset($amz_settings['item_attribute']) && $amz_settings['item_attribute'] == 'no' ? false : true;
                            $import_params['import_attributes'] = $import_attributes;
						}
					}
				}
			}			
			return $import_params;
		}

		private function get_import_params( $from ) {
			extract( $this->__parse_from($from) );
 
			// from database - saved for auto-import & search
			$import_params_db = array();
			if ( !empty($id) ) {
				// auto import
				if ( 'auto' == $from ) {
					$import_params_db = get_option('WooZone_ai_import_params', array());
					$import_params_db = is_array($import_params_db) ? $import_params_db : array();
					$import_params_db = isset($import_params_db["$id"]) ? $import_params_db["$id"] : array();
				}
				// search
				else {
					if ( isset($this->queue_chunk_search["$id"]) ) {
						$import_params_db = $this->queue_chunk_search["$id"]->params;
						$import_params_db = maybe_unserialize( $import_params_db );
						$import_params_db = is_array($import_params_db) ? $import_params_db : array();

						$import_params_db = isset($import_params_db['import_params'])
							? $import_params_db['import_params'] : array();
					}
				}
			}
			$def = $this->get_import_params_default();
			$default = array(
				'import_type' 		=> $def['import_type'],
				'nbimages' 			=> $def['number_of_images'],
				'nbvariations' 		=> $def['number_of_variations'],
				'spin' 				=> $def['spin_at_import'],
				'attributes' 		=> $def['import_attributes'],
				'prods_import_type' => 'asynchronous',
				'to_category' 		=> '-1',
			);
			$import_params = array_merge(
				array(),
				$default,
				$import_params_db
			);
			if ( !empty($import_params_db) ) {
				foreach (array('spin', 'attributes') as $val) {
					if ( !isset($import_params_db["$val"]) ) {
						unset( $import_params["$val"] );
					}
				}
			}
			return $import_params;
		}
	}
}

// Initialize the WooZoneAutoImport class
$WooZoneAutoImport = WooZoneAutoImport::getInstance();