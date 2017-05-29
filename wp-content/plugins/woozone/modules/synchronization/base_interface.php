<?php
/*
* Define class WooZoneBaseInterfaceSync
* Make sure you skip down to the end of this file, as there are a few
* lines of code that are very important.
*/
!defined('ABSPATH') and exit;
if (class_exists('WooZoneBaseInterfaceSync') != true) {
    class WooZoneBaseInterfaceSync
    {
        /*
        * Store some helpers config
        */
        public $the_plugin = null;
        public $alias = '';

        protected $module_folder = '';
        protected $module_folder_path = '';
        protected $module = '';

        static protected $_instance;
        
        protected static $sql_chunk_limit = 2000;
        
        static protected $sync_fields = array();
        static protected $sync_recurrence = array();
        static protected $sync_hour_start = array();
        static protected $sync_products_per_request = array();
        
        static protected $settings = array();
		static protected $sync_options = array();
        
        // pagination
		protected $items = array();
		protected $items_nr = 0;


        /*
        * Required __construct() function that initalizes the AA-Team Framework
        */
        public function __construct()
        {
            global $WooZone;

            $this->the_plugin = $WooZone;
            $this->alias = $this->the_plugin->alias;
            $this->module_folder = $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'modules/synchronization/';
            $this->module_folder_path = $this->the_plugin->cfg['paths']['plugin_dir_path'] . 'modules/synchronization/';
            $this->module = isset($this->the_plugin->cfg['modules']['synchronization']) ? $this->the_plugin->cfg['modules']['synchronization'] : array();

            $this->init_settings();
			$this->init_sync_options();

            // sync options
            self::$sync_fields = array(
                'price'                 => __('Price', $this->the_plugin->localizationName),
                'title'                 => __('Title', $this->the_plugin->localizationName),
                'url'                   => __('Buy URL', $this->the_plugin->localizationName),
                'desc'                  => __('Description', $this->the_plugin->localizationName),
                'sku'                   => __('SKU', $this->the_plugin->localizationName),
                'sales_rank'            => __('Sales Rank', $this->the_plugin->localizationName),
                'reviews'               => __('Reviews', $this->the_plugin->localizationName),
                'short_desc'            => __('Short description', $this->the_plugin->localizationName),
            );
            self::$sync_recurrence = array(
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
            self::$sync_hour_start = $this->the_plugin->doRange( range(0, 23) );
            self::$sync_products_per_request = $this->the_plugin->doRange( range(5, 50, 5) );

            // ajax helper
			add_action('wp_ajax_WooZoneSyncAjax', array( $this, 'ajax_request' ));
        }

        protected function init_settings() {
            $ss = get_option($this->alias . '_sync', array());
            $ss = maybe_unserialize($ss);
            self::$settings = $ss !== false ? $ss : array();
			self::$settings = array_merge(array(
				'sync_products_per_request'				=> 10, // Products to sync per each cron request
				'sync_hour_start'								=> '',
				'sync_recurrence'								=> 24,
				'sync_fields'										=> array(),
			), self::$settings);
            return self::$settings;
        }
		
        protected function init_sync_options() {
            $ss = get_option($this->alias . '_sync_options', array());
            $ss = maybe_unserialize($ss);
            self::$sync_options = $ss !== false ? $ss : array();
			self::$sync_options = array_merge(array(
				'interface_max_products'				=> 'all',
			), self::$sync_options);
            return self::$sync_options;
        }

        /**
        * Singleton pattern
        *
        * @return WooZoneBaseInterfaceSync Singleton instance
        */
        static public function getInstance()
        {
            if (!self::$_instance) {
                self::$_instance = new self;
            }

            return self::$_instance;
        }

        /*
        * printBaseInterface, method
        * --------------------------
        *
        * this will add the base DOM code for you options interface
        */
        public function printBaseInterface( $module='synchronization' ) {
            global $wpdb;
            
            $ss = self::$settings;

            $mod_vars = array();

            // Sync
            $mod_vars['mod_menu'] = 'info|synchronization_log';
            $mod_vars['mod_title'] = __('Synchronization logs', $this->the_plugin->localizationName);

            // Products Stats
            if ( $module == 'stats_prod' ) {
                $mod_vars['mod_menu'] = 'info|stats_prod';
                $mod_vars['mod_title'] = __('Products stats', $this->the_plugin->localizationName);
            }
            extract($mod_vars);

            $module_data = $this->the_plugin->cfg['modules']["$module"];
            $module_folder = $this->the_plugin->cfg['paths']['plugin_dir_url'] . "modules/$module/";
?>
        <script type="text/javascript" src="<?php echo $this->module_folder;?>app.synchronization.js" ></script>
        <?php /*
        <script type="text/javascript" src="<?php echo $this->module_folder;?>js/jquery.tipsy.js" ></script>
        <link rel='stylesheet' href='<?php echo $this->module_folder;?>css/sync-log.css' type='text/css' media='all' />
		*/ ?>
        
        <div id="<?php echo WooZone()->alias?>">
        	<div id="WooZone-wrapper" class="<?php echo WooZone()->alias?>-content">
        		
	            <?php
	            // show the top menu
	            WooZoneAdminMenu::getInstance()->make_active($mod_menu)->show_menu(); 
	            ?>
	            
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
	                                    <div id="WooZone-sync-log" class="WooZone-panel-content" data-module="<?php echo $module; ?>">
	
	                                        <?php
	                                           $lang = array(
	                                               'no_products'          => __('No products available.', 'WooZone'),
	                                               'sync_now'             => __('Sync now (<span>{nb}</span> remained)', 'WooZone'),
	                                               'sync_now_msgformat'   => __('(ASIN: {1} / ID: #{2}): {3}', 'WooZone'),
	                                               'sync_now_finished'    => __('Sync now is finished.', 'WooZone'),
	                                               'sync_now_inwork'      => __('Sync all now in progress. Please be patient till it\'s finished.', 'WooZone'),
	                                               'sync_now_stop_btn'    => __('stop processing.', 'WooZone'),
	                                               'sync_now_stopped'     => __('Sync now is stopped.', 'WooZone'),
	                                               'sync_now_stopping'     => __('Sync now will be stopped...', 'WooZone'),
	                                               'loading'              => __('Loading..', 'WooZone'),
	                                           );
	                                        ?>
	                                        <div id="WooZone-lang-translation" style="display: none;"><?php echo htmlentities(json_encode( $lang )); ?></div>
	
	                                        <!-- Main loading box >
	                                        <div id="WooZone-main-loading">
	                                            <div id="WooZone-loading-overlay"></div>
	                                            <div id="WooZone-loading-box">
	                                                <div class="WooZone-loading-text"><?php _e('Loading', $this->the_plugin->localizationName);?></div>
	                                                <div class="WooZone-meter WooZone-animate" style="width:86%; margin: 34px 0px 0px 7%;"><span style="width:100%"></span></div>
	                                            </div>
	                                        </div-->
	            
	                                        <!--<div class="WooZone-sync-filters">
	                                            <select>
	                                                <option>Show All</option>
	                                                <option>Show None</option>
	                                                <option>Show What</option>
	                                            </select>   
	                                            <select>
	                                                <option>Show All</option>
	                                                <option>Show None</option>
	                                                <option>Show What</option>
	                                            </select>
	                                            <a>Published <span class="count">(27)</span></a>
	                                        </div>-->
	                                        <?php if ( $module == 'synchronization' ) { ?>
	                                            <div class="WooZone-sync-stats">
	                                              <h3><?php _e('Synchronisation Cronjob Stats', $this->the_plugin->localizationName);?></h3>
	                                              <?php echo $this->sync_stats(); ?>
	                                            </div>
	                                        <?php } ?>
	                                        <?php if ( $module == 'synchronization' ) { ?>
	                                            <div class="WooZone-sync-info">
	                                              <h3><?php _e('Synchronisation Settings', $this->the_plugin->localizationName);?></h3>
	                                              <?php echo $this->sync_settings(array()); ?>
												  <?php echo $this->get_pagination(array(
	                                              		'position' 		=> 'top',
	                                              		'with_wrapp' 	=> true
	                                              )); ?>
	                                              <div class="WooZone-panel-sync-all"></div>
	                                              <div class="WooZone-sync-inprogress WooZone-sync-inprogress-bottom"></div>
	                                            </div>
	                                        <?php } else { ?>
	                                        <div class="WooZone-sync-info WooZone-box-stats">
												  <?php echo $this->get_pagination(array(
	                                              		'position' 		=> 'top',
	                                              		'with_wrapp' 	=> true
	                                              )); ?>
	                                        </div>
	                                        <?php } ?>
	                                        <div class="WooZone-sync-filters">
	                                            <span>
	                                                <?php _e('Total products', $this->the_plugin->localizationName);?>: <span class="count"></span> (<span class="countv"></span> variations)
	                                                <!-- | <?php _e('Synchronized products', $this->the_plugin->localizationName);?>: <span class="count">(27)</span>-->
	                                            </span>
	                                            <span class="right">
	                                                <?php if ( $module == 'synchronization' ) { ?>
	                                                    <label for="sync_stop_reload"><?php _e('stop auto reload', $this->the_plugin->localizationName); ?></label>
	                                                    <input type="checkbox" name="sync_stop_reload" id="sync_stop_reload"<?php echo isset($ss['sync_stop_reload']) && !empty($ss['sync_stop_reload']) ? ' checked="checked"' : ''; ?>/>
	                                                    <strong>0</strong> <?php _e('seconds', $this->the_plugin->localizationName); ?>
	                                                <?php } ?>
	                                                <button class="load_prods"><?php _e('Reload products list', $this->the_plugin->localizationName);?></button>
	                                                <?php if ( $module == 'synchronization' ) { ?>
	                                                <button class="sync-all"><?php _e('Sync all now', $this->the_plugin->localizationName);?></button>
	                                                <?php } ?>
	                                            </span>
	                                        </div>
	                                        <div class="WooZone-sync-table <?php echo ( $module == 'synchronization' ? 'synchronization' : 'stats_prod' ); ?>">
	                                          <table cellspacing="0">
	                                            <thead>
	                                                <tr class="WooZone-sync-table-header">
	                                                    <?php if ( $module == 'synchronization' ) { ?>
	                                                    <th style="width:1.83%;"><i class="fa fa-flag" title="<?php _e('SYNC STATUS', $this->the_plugin->localizationName);?>"></i></th>
	                                                    <th style="width:18.11%;"><?php _e('Image', $this->the_plugin->localizationName);?></th>
	                                                    <th style="width:48.44%;"><?php _e('Title', $this->the_plugin->localizationName);?></th>
	                                                    <th style="width:14.83%;"><?php _e('Number of Synchronisations', $this->the_plugin->localizationName);?></th>
	                                                    <th style="width:8.19%;" class="wz-uppercase"><?php _e('Last Sync', $this->the_plugin->localizationName);?></th>
	                                                    <th style="width:8.46%;"><?php _e('Action', $this->the_plugin->localizationName);?></th>
	                                                    <?php } else { ?>
	                                                    <th style="width:3%;"><?php _e('ID', $this->the_plugin->localizationName);?></th>
	                                                    <th style="width:16%;"><?php _e('Image', $this->the_plugin->localizationName);?></th>
	                                                    <th style="width:50%;"><?php _e('Title', $this->the_plugin->localizationName);?></th>
	                                                    <th style="width:7%;"><?php _e('Hits', $this->the_plugin->localizationName);?></th>
	                                                    <th style="width:7%;"><?php _e('Added to cart', $this->the_plugin->localizationName);?></th>
	                                                    <th style="width:7%;" class="wz-uppercase"><?php _e('Redirected to Amazon', $this->the_plugin->localizationName);?></th>
	                                                    <th style="width:10%;"><?php _e('Date Added', $this->the_plugin->localizationName);?></th>
	                                                    <?php } ?>
	                                                </tr>
	                                            </thead>
	                                            <tbody>
	                                            <?php
	                                                //require_once( $this->module_folder_path . '_html.php');
	                                            ?>
	                                            </tbody>
	                                          </table>
	                                        </div>
	                                        <?php if ( $module == 'synchronization' ) { ?>
	                                            <div class="WooZone-sync-info">
	                                            	<div class="WooZone-panel-sync-all"></div>
	                                            	<div class="WooZone-sync-inprogress WooZone-sync-inprogress-top"></div>
												  <?php echo $this->get_pagination(array(
	                                              		'position' 		=> 'bottom',
	                                              		'with_wrapp' 	=> true
	                                              )); ?>
	                                              <h3><?php _e('Synchronisation Settings', $this->the_plugin->localizationName);?></h3>
	                                              <?php echo $this->sync_settings(array('position' => 'bottom')); ?>
	                                            </div>
	                                        <?php } else { ?>
	                                        <div class="WooZone-sync-info WooZone-box-stats">
												  <?php echo $this->get_pagination(array(
	                                              		'position' 		=> 'bottom',
	                                              		'with_wrapp' 	=> true
	                                              )); ?>
	                                        </div>
	                                        <?php } ?>
	                                    </div>
	                                </div>
	                            </div>
	                            <div class="clear"></div>
	                        </div>
	                    </div>

<?php } // end demo keys ?>

					</div>
				</section>
            </div>
        </div>
        <?php /*<script type='text/javascript'>
            jQuery('i').tipsy({gravity: 'n'});
            
            jQuery(document).ready(function($){
                jQuery("tbody a.wz-show-variations").click(function(){
                  jQuery( this ).parents().eq(1).siblings().toggleClass("wz-hide-me");
                });
            });
        </script>*/ ?>

<?php
        }

        protected function sync_settings( $pms=array() ) {
        	extract($pms);
            ob_start();

            $ss = self::$settings;
?>
                                        <form class="WooZone-sync-settings">
                                          <p><?php _e('Each product has to sync the following', $this->the_plugin->localizationName);?>:
                                            <?php
                                            foreach (self::$sync_fields as $key => $val) {
                                                $is_checked = 'checked="checked"';
                                                if ( !isset($ss['sync_fields'])
                                                    || ( isset($ss['sync_fields']) && !in_array($key, $ss['sync_fields']) ) ) {
                                                    $is_checked = '';
                                                }
                                            ?>
                                            <span>
                                            <label for="sync_fields[<?php echo $key; ?>]"><?php echo $val; ?></label>:
                                            <input type="checkbox" id="sync_fields[<?php echo $key; ?>]" name="sync_fields[<?php echo $key; ?>]" <?php echo $is_checked; ?> />
                                            </span>
                                            <?php
                                            }
                                            ?>
                                          </p>
                                          <p>
                                            <!--Recurrence : <span>24h</span>
                                            First start a hour <span>10</span>-->
                                            <span>
                                            <?php _e('Recurrence', $this->the_plugin->localizationName);?>:
                                            </span>
                                            <select id="sync_recurrence" name="sync_recurrence" class="WooZone-filter-general_field">
                                            <?php
                                            foreach (self::$sync_recurrence as $key => $val) {
                                                $is_checked = '';
                                                if ( isset($ss['sync_recurrence']) && $key == $ss['sync_recurrence'] ) {
                                                    $is_checked = 'selected="selected"';
                                                }
                                            ?>
                                                <option value="<?php echo $key; ?>" <?php echo $is_checked; ?>><?php echo $val; ?></option>
                                            <?php
                                            }
                                            ?>
                                            </select>
                                            
                                            <span>
                                            <?php _e('Products per request', $this->the_plugin->localizationName);?>:
                                            </span>
                                            <select id="sync_products_per_request" name="sync_products_per_request" class="WooZone-filter-general_field">
                                            <?php
                                            foreach (self::$sync_products_per_request as $key => $val) {
                                                $is_checked = '';
                                                if ( isset($ss['sync_products_per_request']) && $key == $ss['sync_products_per_request'] ) {
                                                    $is_checked = 'selected="selected"';
                                                }
                                            ?>
                                                <option value="<?php echo $key; ?>" <?php echo $is_checked; ?>><?php echo $val; ?></option>
                                            <?php
                                            }
                                            ?>
                                            </select>
                                            
                                            <?php /*
                                            <span>
                                            <?php _e('First start at hour', $this->the_plugin->localizationName);?>:
                                            </span>
                                            <select id="sync_hour_start" name="sync_hour_start">
                                            <?php
                                            foreach (self::$sync_hour_start as $key => $val) {
                                                $is_checked = '';
                                                if ( isset($ss['sync_hour_start']) && $key == $ss['sync_hour_start'] ) {
                                                    $is_checked = 'selected="selected"';
                                                }
                                            ?>
                                                <option value="<?php echo $key; ?>" <?php echo $is_checked; ?>><?php echo $val; ?></option>
                                            <?php
                                            }
                                            ?>
                                            </select>
                                            */ ?>
                                          </p>
                                          <p>
                                              <button><?php _e('Save settings', $this->the_plugin->localizationName);?></button>
                                          </p>
                                        </form>
                                        
                                        <?php //echo $this->get_pagination($pms); ?>
<?php
            return ob_get_clean();
        }

        protected function sync_stats() {
            ob_start();

            $ss = self::$settings;

            // last report
            $report_last_date = get_option('WooZone_report_last_date', false);

            // last sync cycle
            $recurrence = $ss['sync_recurrence'];
            $recurrence2 = self::$sync_recurrence["$recurrence"];
            $sync_start_time = get_option('WooZone_sync_first_updated_date', false);
            
            $sync_duration = get_option('WooZone_sync_cycle_stats', array());
            $sync_duration2 = 0;
            if ( isset($sync_duration['start_time'], $sync_duration['end_time'])
                && $sync_duration['end_time'] > $sync_duration['start_time'] ) {
                //$sync_duration2 = $sync_duration['end_time'] - $sync_duration['start_time'];
                $sync_duration2 = $this->time_since($sync_duration['start_time'], $sync_duration['end_time']);
            }
                
            $sync_currentlist_last_product = get_option('WooZone_sync_currentlist_last_product', 0);
            $sync_last_updated_product = get_option('WooZone_sync_last_updated_product', 0);

            $sync_status = 0; // in progress
            $sync_status_text = __('in progress', $this->the_plugin->localizationName);
            if ( empty($sync_currentlist_last_product) ) {

                $sync_status = 2; // not initialized yet.
                $sync_status_text = __('to be initialized', $this->the_plugin->localizationName);
            } else if ( $sync_last_updated_product >= $sync_currentlist_last_product ) {

                $sync_status = 1; // success
                $sync_status_text = __('completed', $this->the_plugin->localizationName);                
            }
            
            // next sync cycle
            $recurrence_sec = (int) ( $ss['sync_recurrence'] * 3600 );
            $nextsync_start_time = !empty($sync_start_time) ? $sync_start_time + $recurrence_sec : false;
            
            // estimated time to sync all products in the cycle based on products number and products per request setting
            $sync_currentlist_nb_products = get_option('WooZone_sync_currentlist_nb_products', 0);
            $sync_products_per_request = (int) $ss['sync_products_per_request'];
            if ( !empty($sync_currentlist_nb_products) && !empty($sync_products_per_request) ) {

                $nextsync_start_time2 = ceil( $sync_currentlist_nb_products / $sync_products_per_request );
                // 2 minutes * 60 seconds per minute - WooZone_sync_products
                $nextsync_start_time2 = $nextsync_start_time2 * 2 * 60;
                
                $nextsync_start_time2 = $sync_start_time + $nextsync_start_time2;
                if ( $nextsync_start_time2 > $nextsync_start_time ) {
                    $nextsync_start_time = $nextsync_start_time2;
                }
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
                            <span class="title"><?php _e('Last Sync Cycle', $this->the_plugin->localizationName);?></span>
                            <span class="WooZone-message <?php echo $sync_status == 1 ? 'WooZone-success' : 'WooZone-info'; ?>"><?php echo $sync_status_text; ?></span>
                            <ul>
                                <li>
                                    <?php _e('Recurrence', $this->the_plugin->localizationName);?>:
                                    <span><?php echo $recurrence2; ?></span>
                                </li>
                                <?php if ( !empty($sync_start_time) ) { ?>
                                <li>
                                    <?php _e('Start time', $this->the_plugin->localizationName);?>:
                                    <span><?php echo $this->the_plugin->last_update_date('true', $sync_start_time); ?></span>
                                </li>
                                <?php } ?>
                                <?php if ( !empty($sync_duration2) ) { ?>
                                <li>
                                    <?php _e('Duration', $this->the_plugin->localizationName);?>:
                                    <span><?php echo $sync_duration2; ?></span>
                                </li>
                                <?php } ?>
                                <li>
                                    <?php _e('ID Last product to be synced in the cycle', $this->the_plugin->localizationName);?>:
                                    <span><?php echo $sync_currentlist_last_product; ?></span>
                                </li>
                                <li>
                                    <?php _e('ID Last product synced', $this->the_plugin->localizationName);?>:
                                    <span><?php echo $sync_last_updated_product; ?></span>
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
                    <tr>
                        <td>
                            <span class="title"><?php _e('Next Sync Cycle', $this->the_plugin->localizationName);?></span>
                            <ul>
                                <?php if ( !empty($nextsync_start_time) ) { ?>
                                <li>
                                    <?php _e('Estimated Start time (depends on last sync cycle start time and recurrence settings and also on products per request sync setting)', $this->the_plugin->localizationName);?>:
                                    <br /><span><?php
                                        echo $this->the_plugin->last_update_date('true', $nextsync_start_time);
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

        protected function get_products( $pms=array() ) {
            global $wpdb;

			$prod_key = '_amzASIN';

			$pms = array_merge(array(
				'module'			=> 'synchronization',
				'paged'				=> 1,
				'posts_per_page'	=> 'all',
			), $pms);
        	extract($pms);

			//$max_prods = $this->get_interface_max_products();
			//$q_limit = $max_prods !== 'all' ? "LIMIT 0, $max_prods" : '';
			$q_limit = '';
			
			//$posts_per_page = 1; //DEBUG
			
			$__limitClause = 'ORDER BY p.ID ASC';
			$__limitClause .= $posts_per_page!='all' && $posts_per_page>0
				? " LIMIT " . (($paged - 1) * $posts_per_page) . ", " . $posts_per_page : '';
            
            // get products (simple or just parents without variations)
            //$sqlTpl = "SELECT p.ID, p.post_title, p.post_parent, p.post_date FROM $wpdb->posts as p WHERE 1=1 AND p.post_status = 'publish' AND p.post_parent = 0 AND p.post_type = 'product' ORDER BY p.ID ASC $q_limit;";
            $__fields = 'p.ID, p.post_title, p.post_parent, p.post_date, pm.post_id, pm.meta_value';
			$sqlTpl = "SELECT {FIELDS} FROM $wpdb->posts as p RIGHT JOIN $wpdb->postmeta as pm ON p.ID = pm.post_id WHERE 1=1 AND p.post_parent = 0 AND p.post_type = 'product' AND pm.meta_key='$prod_key' AND ( !isnull(p.ID) AND p.post_status = 'publish' ) {LIMIT};";
			
			$sql = $sqlTpl;
			$sql = str_replace("{FIELDS}", $__fields, $sql);
			$sql = str_replace("{LIMIT}", $__limitClause, $sql);
            //var_dump('<pre>', $sql, '</pre>'); die('debug...');
            $res = $wpdb->get_results( $sql, OBJECT_K );
            if ( empty($res) ) return array();
			
			// total items
			$sqlTotal = $sqlTpl;
			$sqlTotal = str_replace("{FIELDS}", 'count(p.ID) as nb', $sqlTotal);
			$sqlTotal = str_replace("{LIMIT}", '', $sqlTotal);
			$this->items_nr = (int) $wpdb->get_var( $sqlTotal );
			
			// total variations
			$sqlTotalVar = "SELECT count(p.ID) as nb FROM $wpdb->posts as p RIGHT JOIN $wpdb->postmeta as pm ON p.ID = pm.post_id LEFT JOIN $wpdb->posts as p2 ON p.post_parent = p2.ID WHERE 1=1 AND p.post_parent > 0 AND p.post_type = 'product_variation' AND pm.meta_key='$prod_key'  AND ( !isnull(p.ID) AND p.post_status = 'publish' ) AND ( !isnull(p2.ID) AND p2.post_status = 'publish' );";
			$totalVariations = (int) $wpdb->get_var( $sqlTotalVar );
			
			$res_childs = array();
            $parent2child = array();
			//--------------------------
			//-- NOT USED
			if (0) {

            // get product variations (only childs, no parents)
            $sql_childs = "SELECT p.ID, p.post_title, p.post_parent, p.post_date FROM $wpdb->posts as p WHERE 1=1 AND p.post_status = 'publish' AND p.post_parent > 0 AND p.post_type = 'product_variation' ORDER BY p.ID ASC $q_limit;";
            $res_childs = $wpdb->get_results( $sql_childs, OBJECT_K );
            
            //var_dump('<pre>', $sql, $sql_childs, '</pre>'); die('debug...'); 
            if ( empty($res) && empty($res_childs) ) return array();
            
            // array with parents and their associated childrens
            foreach ($res_childs as $id => $val) {
                $parent = $val->post_parent;
                
                if ( !isset($parent2child["$parent"]) ) {
                    $parent2child["$parent"] = array();
                }
                $parent2child["$parent"]["$id"] = $val; 
            }

			}
			//--------------------------
			//-- end NOT USED

            // products IDs
            $prods = array_merge(array(), array_keys($res), array_keys($res_childs));
            $prods = array_unique($prods);
            
            // get product variations (only childs, no parents)
            foreach (array_chunk($prods, self::$sql_chunk_limit, true) as $current) {

                $currentP = implode(',', array_map(array($this->the_plugin, 'prepareForInList'), $current));
                $sql_childnb = "SELECT p.post_parent, count(p.ID) as nb FROM $wpdb->posts as p WHERE 1=1 AND p.post_status = 'publish' AND p.post_parent > 0 AND p.post_type = 'product_variation' AND p.post_parent IN ($currentP) GROUP BY p.post_parent ORDER BY p.post_parent ASC;";
                $res_childnb = $wpdb->get_results( $sql_childnb, OBJECT_K );
                $parent2child = $parent2child + $res_childnb; //array_replace($parent2child, $res_childnb);
            }

            // get ASINs
            $prods2asin = array();
			//--------------------------
			//-- NOT USED
			if (0) {

            foreach (array_chunk($prods, self::$sql_chunk_limit, true) as $current) {

                $currentP = implode(',', array_map(array($this->the_plugin, 'prepareForInList'), $current));
                $sql_getasin = "SELECT pm.post_id, pm.meta_value FROM $wpdb->postmeta as pm WHERE 1=1 AND pm.meta_key = '$prod_key' AND pm.post_id IN ($currentP) ORDER BY pm.post_id ASC;";
                $res_getasin = $wpdb->get_results( $sql_getasin, OBJECT_K );
                $prods2asin = $prods2asin + $res_getasin; //array_replace($prods2asin, $res_getasin);
            }

			}
			//--------------------------
			//-- end NOT USED

            $__meta_toget = array();
            if ( $module == 'synchronization' ) {
            	// synchronization
                $__meta_toget = array('_amzaff_sync_last_date', '_amzaff_sync_hits', '_amzaff_sync_last_status_msg');
            }
            else if ( $module == 'speed_optimization' ) {
            }
            else {
            	// stats products
                $__meta_toget = array('_amzaff_hits', '_amzaff_addtocart', '_amzaff_redirect_to_amazon');
            }
            // get sync last date & sync hits
            $prods2meta = array();
            foreach ( (array) $__meta_toget as $meta) {
                $prods2meta["$meta"] = array();

                foreach (array_chunk($prods, self::$sql_chunk_limit, true) as $current) {
    
                    $currentP = implode(',', array_map(array($this->the_plugin, 'prepareForInList'), $current));
    
                    $sql_getmeta = "SELECT pm.post_id, pm.meta_value FROM $wpdb->postmeta as pm WHERE 1=1 AND pm.meta_key = '$meta' AND pm.post_id IN ($currentP) ORDER BY pm.post_id ASC;";
                    $res_getmeta = $wpdb->get_results( $sql_getmeta, OBJECT_K );
                    $prods2meta["$meta"] = $prods2meta["$meta"] + $res_getmeta; //array_replace($prods2meta["$meta"], $res_getmeta);
                }
            }
 
            // get Thumbs
            //$thumbs = $this->get_thumbs();
            $thumbs = array();
            foreach (array_chunk($prods, self::$sql_chunk_limit, true) as $current) {

                //$currentP = implode(',', array_map(array($this->the_plugin, 'prepareForInList'), $current));
                $thumbs_ = $this->get_thumbs( $current );
                $thumbs = $thumbs + $thumbs_; //array_replace($thumbs, $thumbs_);
            }
 
            // build html table with products rows
            $last_updated_product = (int) get_option('WooZone_sync_last_updated_product', true);
            $next_updated_product = $this->get_next_product( $last_updated_product );
            $default = array(
                'module'        => $module,
                'last_id'       => $last_updated_product,
                'next_id'       => $next_updated_product,
                'is_open'		=> false,
            );

            $ret = array('status' => 'valid', 'html' => array(), 'nb' => 0, 'nbv' => 0);
            $nbprod = 0;
            $nbprodv = 0;
            $isBreakLimit = false;
            foreach ($res as $id => $val) {
                    
                $prods2asin["$id"] = (object) array(
                	'post_id'		=> $id,
                	'meta_value'	=> $val->meta_value,
				);
                
                if ( !isset($prods2asin["$id"]) ) continue 1; // exclude products without ASIN

                $__p = $this->row_build(array_merge($default, array(
                    'id'            => $id,
                    'val'           => $val,
                    'prods2asin'    => $prods2asin,
                    'thumbs'        => $thumbs,
                    'prods2meta'    => $prods2meta,
                )));
                $__p = array_merge($__p, array(
                    'id'            => $id,
                ));
 
                $is_open = ( !empty($default['next_id'])
					&& $default['next_id']->post_type == 'product_variation'
                    && ( $id == (int) $default['next_id']->post_parent ) ? 1 : 0 );
                $childs_btn = '';
   
                // product variations if it has
                $childs_html = array();
                if ( isset($parent2child["$id"], $parent2child["$id"]->nb)
					&& $parent2child["$id"]->nb > 0 ) {

					$childs_nb = $parent2child["$id"]->nb;

					//--------------------------
					//-- NOT USED
					if (0) {

                    $childs = $parent2child["$id"];
                    $childs_nb = count($childs);
                    $cc = 0;
                    foreach ($childs as $childId => $childVal) {

                        $__pc = $this->row_build(array_merge($default, array(
                            'id'            => $childId,
                            'val'           => $childVal,
                            'prods2asin'    => $prods2asin,
                            'thumbs'        => $thumbs,
                            'prods2meta'    => $prods2meta,
                            'is_open'       => $is_open,
                        )));
                        $__pc = array_merge($__pc, array(
                            'id'            => $childId,
                            'parent_id'     => $id,
                            'cc'            => $cc,
                            'childs_nb'     => $childs_nb,
                        ));
 
                        $childs_html[] = $this->row_view_html($__pc, true);
                        
                        $cc++;
                    }
					
	                }
					//--------------------------
					//-- end NOT USED

                    $childs_btn = '<a href="#" class="wz-show-variations' . ($is_open ? ' sign-minus' : ' sign-plus') . '">(<span>' . ($is_open ? '-' : '+') . '</span>' . $childs_nb . ')</a>';
                    $nbprodv += $childs_nb;
                } // end product variations loop

                $__p['childs_btn'] = $childs_btn;
				
				$this->items["$id"] = $__p;
                
                // product
                $ret['html'][] = $this->row_view_html($__p);
                
                if ( isset($childs_html) && !empty($childs_html) ) {
                    $ret['html'][] = implode(PHP_EOL, $childs_html);
                }

                $nbprod++;
            } // end products loop
            
            $nbprod = $this->items_nr;
			$nbprodv = $totalVariations;
            
            $ret = array_merge($ret, array(
                'nb'        => $nbprod,
                'nbv'       => $nbprodv,
            ));
            if ( $isBreakLimit ) {
                $ret = array_merge_recursive($ret, array(
                    'estimate'  => array(
                        'nb'        => count($res),
                        'nbv'       => count($res_childs),
                    ),
                ));
            }
            
            return $ret;
        }

        protected function get_product_variations( $pms=array() ) {
            global $wpdb;
			
			$prod_key = '_amzASIN';
			
			$pms = array_merge(array(
				'module'			=> 'synchronization',
				'prodid'			=> 0,
			), $pms);
        	extract($pms);
			
			if ( !$prodid ) return array();
			
            // get product variations
			$__fields = 'p.ID, p.post_title, p.post_parent, p.post_date, pm.post_id, pm.meta_value';
			$sqlTpl = "SELECT {FIELDS} FROM $wpdb->posts as p RIGHT JOIN $wpdb->postmeta as pm ON p.ID = pm.post_id WHERE 1=1 AND p.post_parent = '$prodid' AND p.post_parent > 0 AND p.post_type = 'product_variation' AND pm.meta_key='$prod_key' AND ( !isnull(p.ID) AND p.post_status = 'publish' );";
			
			$sql = $sqlTpl;
			$sql = str_replace("{FIELDS}", $__fields, $sql);
            //var_dump('<pre>', $sql, '</pre>'); die('debug...');
            $res = $wpdb->get_results( $sql, OBJECT_K );
            if ( empty($res) ) return array();
			
			$res_childs = array();
            $parent2child = array();

            // products IDs
            $prods = array_merge(array(), array_keys($res), array_keys($res_childs));
            $prods = array_unique($prods);
            
            // get ASINs
            $prods2asin = array();
            
            if ( $module == 'synchronization' ) {
                $__meta_toget = array('_amzaff_sync_last_date', '_amzaff_sync_hits', '_amzaff_sync_last_status_msg');
            }
            else if ( $module == 'speed_optimization' ) {

            }
            else {
                $__meta_toget = array('_amzaff_hits', '_amzaff_addtocart', '_amzaff_redirect_to_amazon');
            }
            // get sync last date & sync hits
            foreach ( (array) $__meta_toget as $meta) {
                $prods2meta["$meta"] = array();

                foreach (array_chunk($prods, self::$sql_chunk_limit, true) as $current) {
    
                    $currentP = implode(',', array_map(array($this->the_plugin, 'prepareForInList'), $current));
    
                    $sql_getmeta = "SELECT pm.post_id, pm.meta_value FROM $wpdb->postmeta as pm WHERE 1=1 AND pm.meta_key = '$meta' AND pm.post_id IN ($currentP) ORDER BY pm.post_id ASC;";
                    $res_getmeta = $wpdb->get_results( $sql_getmeta, OBJECT_K );
                    $prods2meta["$meta"] = $prods2meta["$meta"] + $res_getmeta; //array_replace($prods2meta["$meta"], $res_getmeta);
                }
            }
 
            // get Thumbs
            //$thumbs = $this->get_thumbs();
            $thumbs = array();
            foreach (array_chunk($prods, self::$sql_chunk_limit, true) as $current) {

                //$currentP = implode(',', array_map(array($this->the_plugin, 'prepareForInList'), $current));
                $thumbs_ = $this->get_thumbs( $current );
                $thumbs = $thumbs + $thumbs_; //array_replace($thumbs, $thumbs_);
            }
 
            // build html table with products rows
            $last_updated_product = (int) get_option('WooZone_sync_last_updated_product', true);
            $next_updated_product = $this->get_next_product( $last_updated_product );
            $default = array(
                'module'        => $module,
                'last_id'       => $last_updated_product,
                'next_id'       => $next_updated_product,
                'is_open'		=> false,
            );

            $ret = array('status' => 'valid', 'html' => array(), 'nb' => 0, 'nbv' => 0);
            $nbprod = 0;
			$cc = 0;
			$childs_nb = count($res);
            foreach ($res as $id => $val) {
                    
                $prods2asin["$id"] = (object) array(
                	'post_id'		=> $id,
                	'meta_value'	=> $val->meta_value,
				);
                
                if ( !isset($prods2asin["$id"]) ) continue 1; // exclude products without ASIN
                
                $is_open = false;

                $__p = $this->row_build(array_merge($default, array(
                    'id'            => $id,
                    'val'           => $val,
                    'prods2asin'    => $prods2asin,
                    'thumbs'        => $thumbs,
                    'prods2meta'    => $prods2meta,
                    'is_open'       => $is_open,
                )));
                $__p = array_merge($__p, array(
                    'id'            => $id,
                    'parent_id'     => $prodid,
                    'cc'            => $cc,
                    'childs_nb'     => $childs_nb,
                ));
 
                // product
                $ret['html'][] = $this->row_view_html($__p, true);
                
                $nbprod++;
				$cc++;
            } // end products loop
            
            $nbprod = $this->items_nr;
            
            $ret = array_merge($ret, array(
                'nb'        => $nbprod,
                //'nbv'       => $nbprodv,
            ));
            
            return $ret;
        }

        protected function get_next_product( $last_id=0 ) {
            global $wpdb;
			
			$prod_key = '_amzASIN';
            
            $sql = "select p.ID, p.post_type, p.post_parent from $wpdb->posts as p left join $wpdb->postmeta as pm on p.ID = pm.post_id where 1=1 and p.ID > $last_id and p.post_type in ('product', 'product_variation') and p.post_status = 'publish' and pm.meta_key = '$prod_key' and !isnull(pm.meta_value) order by p.ID asc limit 1;";
            $ret = $wpdb->get_row( $sql );
            return is_null($ret) || $ret === false
                ? (object) array(
                    'ID'            => $last_id+1,
                    'post_type'     => 'product',
                    'post_parent'   => 0
                ) : $ret;
        }

        protected function row_build( $pms ) {
            extract($pms);

            $title = $val->post_title;
            $asin = isset($prods2asin["$id"]) ? $prods2asin["$id"]->meta_value : __('missing', $this->the_plugin->localizationName);
                
            //$thumb = 'http://ecx.images-amazon.com/images/I/41mVXvLfOtL._SL75_.jpg';
            $thumb = isset($thumbs["$id"]) && !empty($thumbs["$id"]) ? $thumbs["$id"] : $this->get_thumb_src_default();
                
            $link_edit = sprintf( admin_url('post.php?post=%s&action=edit'), $id);
            
            $add_data = $val->post_date;
            $add_data = $this->the_plugin->last_update_date('true', strtotime($add_data));

            if ( $module == 'synchronization' ) {
	            // statuses: NOT SYNCED | ALREADY SYNCED | NEXT TO BE SYNCED
	            $sst = array(
	                'not_synced'            => __('NOT SYNCED', $this->the_plugin->localizationName),
	                'already_synced'        => __('ALREADY SYNCED', $this->the_plugin->localizationName),
	                'next_to_synced'        => __('NEXT TO BE SYNCED', $this->the_plugin->localizationName),
	            );
	            $next_id = $next_id->ID;
	            $sync_status = array(
	                'css'       => ( $id < $next_id ? 'wz-synced' : ( $id == $next_id ? 'wz-next-sync' : '' ) ),
	                'text'      => ( $id < $next_id ? $sst['already_synced'] : ( $id == $next_id ? $sst['next_to_synced'] : $sst['not_synced'] ) ),
	            );
	            if ( $is_open ) {
	                $sync_status['css'] .= ' wz-hide-me';
	                $sync_status['css'] = trim($sync_status['css']);
	            }
	            
	            $sync_nb = isset($prods2meta['_amzaff_sync_hits']["$id"]) ? $prods2meta['_amzaff_sync_hits']["$id"]->meta_value : 0;

	            $sync_data = isset($prods2meta['_amzaff_sync_last_date']["$id"]) ? $prods2meta['_amzaff_sync_last_date']["$id"]->meta_value : '';
	            $sync_data = $this->the_plugin->last_update_date('true', $sync_data);
	            
	            $sync_last_status = isset($prods2meta['_amzaff_sync_last_status_msg']["$id"]) ? strtoupper('last sync status: ' . $prods2meta['_amzaff_sync_last_status_msg']["$id"]->meta_value) : '';

	            $ret = compact('module', 'add_data', 'title', 'asin', 'thumb', 'link_edit', 'sync_status', 'sync_nb', 'sync_data', 'sync_last_status');
            }
            else if ( $module == 'speed_optimization' ) {
	            $sync_status = array(
	                'css'       => ( '' ),
	                'text'      => ( '' ),
	            );
	            
	            $ret = compact('module', 'add_data', 'title', 'asin', 'thumb', 'link_edit', 'sync_status');
            }
            else {
	            $sync_status = array(
	                'css'       => ( '' ),
	                'text'      => ( '' ),
	            );
	            
	            $stats_hits = isset($prods2meta['_amzaff_hits']["$id"]) ? $prods2meta['_amzaff_hits']["$id"]->meta_value : 0;
	            $stats_added_to_cart = isset($prods2meta['_amzaff_addtocart']["$id"]) ? $prods2meta['_amzaff_addtocart']["$id"]->meta_value : 0;
	            $stats_redirected_to_amazon = isset($prods2meta['_amzaff_redirect_to_amazon']["$id"]) ? $prods2meta['_amzaff_redirect_to_amazon']["$id"]->meta_value : 0;
	            
	            $ret = compact('module', 'add_data', 'title', 'asin', 'thumb', 'link_edit', 'sync_status', 'stats_hits', 'stats_added_to_cart', 'stats_redirected_to_amazon');
            }
            return $ret;
        }

        protected function row_view_html( $row, $is_child=false ) {

            $tr_css = ' ' . $row['sync_status']['css']
                . ($is_child ? ' wz-variation' . ($row['cc'] == 0 ? ' first' : ($row['cc'] == $row['childs_nb'] - 1 ? ' last' : '')) : '');
            $data_parent_id = ($is_child ? ' data-parent_id=' . $row['parent_id'] : '');
            $childs_btn = (!$is_child ? ' ' . $row['childs_btn'] : '');
            
            $text_id = __('ID', $this->the_plugin->localizationName) . ': #';
            $text_asin = __('ASIN', $this->the_plugin->localizationName) . ': ';
            
            if ( $row['module'] == 'synchronization' ) {
	            $text_syncs_nb = sprintf( __('<span>%s</span> SYNCS', $this->the_plugin->localizationName), $row['sync_nb'] );
	            $text_syncs_last_status = ''; //(!empty($row['sync_last_status']) ? '<i class="fa fa-comment-o" title="' . $row['sync_last_status'] . '"></i>' : '');
	            $text_sync_now = __('SYNC NOW', $this->the_plugin->localizationName);
			}
			else if ( $row['module'] == 'speed_optimization' ) {
			}
			else {
	            $text_hits = '<i class="WooZone-prod-stats-number hits">' . ( $row['stats_hits'] ) . '</i>';
	            $text_added_to_cart = '<i class="WooZone-prod-stats-number add-to-cart">' . ( $row['stats_added_to_cart'] ) . '</i>';
	            $text_redirected_to_amazon = '<i class="WooZone-prod-stats-number redirect-to-amazon">' . ( $row['stats_redirected_to_amazon'] ) . '</i>';
            }
            
            if ( $row['module'] == 'synchronization' ) {
            	$ret = '
                    <tr class="WooZone-sync-table-row' . $tr_css . '" data-id=' . $row['id'] . ' data-asin=' . $row['asin'] . $data_parent_id . '>
                        <td><i class="fa fa-flag" title="' . $row['sync_status']['text'] . '"></i></td>
                        <td>' . ($row['thumb'] == '##default##' ? '<i class="WooZone-icon-assets_dwl"></i>' : '<img src="' . $row['thumb'] . '" alt="' . $row['title'] . '" />') . ' ' . $text_asin . $row['asin'] . ' / ' . $text_id . $row['id'] . $childs_btn . '</td>
                        <td><a href="' . $row['link_edit'] . '" target="_blank">' . $row['title'] . '</a></td>
                        <td><a href="#" title="' . $row['sync_last_status'] . '">' . $text_syncs_nb . '</a>' . $text_syncs_last_status . '</td>
                        <td>' . $row['sync_data'] . '</td>
                        <td class="WooZone-sync-now"><button>' . $text_sync_now . '</button></td>
                    </tr>
                ';
            }
            else if ( $row['module'] == 'speed_optimization' ) {
            	
            	$speed_ptimizator = WooZoneSpeedOptimizator::getInstance();
            	$ret = '
                    <tr class="WooZone-sync-table-row' . $tr_css . '" data-id=' . $row['id'] . ' data-asin=' . $row['asin'] . $data_parent_id . '>
                        <td><span>' . $row['id'] . '</span></td>
                        <td>' . ($row['thumb'] == '##default##' ? '<i class="WooZone-icon-assets_dwl"></i>' : '<img src="' . $row['thumb'] . '" alt="' . $row['title'] . '" />') . ' ' . $text_asin . $row['asin'] . $childs_btn . '</td>
                        <td><a href="' . $row['link_edit'] . '" target="_blank">' . $row['title'] . '</a></td>
                        <td>' . $speed_ptimizator->print_stats( $row ) . '</td>
                        <td>' . $speed_ptimizator->print_actions( $row )  . '</td>
                        <td>' . $row['add_data'] . '</td>
                    </tr>
                ';
            }
            else {
            	$ret = '
                    <tr class="WooZone-sync-table-row' . $tr_css . '" data-id=' . $row['id'] . ' data-asin=' . $row['asin'] . $data_parent_id . '>
                        <td><span>' . $row['id'] . '</span></td>
                        <td>' . ($row['thumb'] == '##default##' ? '<i class="WooZone-icon-assets_dwl"></i>' : '<img src="' . $row['thumb'] . '" alt="' . $row['title'] . '" />') . ' ' . $text_asin . $row['asin'] . $childs_btn . '</td>
                        <td><a href="' . $row['link_edit'] . '" target="_blank">' . $row['title'] . '</a></td>
                        <td>' . $text_hits . '</td>
                        <td>' . $text_added_to_cart . '</td>
                        <td>' . $text_redirected_to_amazon . '</td>
                        <td>' . $row['add_data'] . '</td>
                    </tr>
                ';
            }
            return $ret;
        }

		protected function get_interface_max_products( $use_pag=false ) {
			$max_prods = self::$sync_options['interface_max_products'];
			$max_prods = $max_prods !== 'all' ? (int) $max_prods : 'all';
			
			// when with pagination
			if ( $use_pag && 'all' != $max_prods ) {
				$__ = floor( $max_prods / 5 );
				$__ = $__ > 0 ? (int) ($__ * 5) : 1;
				
				if ( $__ > 50 && $__ < 100 ) {
					$__ = 50;
				}
				else if ( $__ > 100 && $__ < 500 ) {
					$__ = floor( $__ / 100 ) * 100;
				}
				else if ( $__ > 500 ) {
					$__ = 500;
				}
				$max_prods = $__;
			}
			
			return $max_prods;
		}

        
        /**
         * Get Thumbnails / Thumbnail - based on WP functionality
         */
        protected function get_thumb_src_default() {
            return '##default##';
        }

        protected function get_thumbs( $currentIds=array(), $size='thumbnail' ) {
            global $wpdb;
			
			$currentP = '';
			if ( !empty($currentIds) ) {
				$currentP = implode(',', array_map(array($this->the_plugin, 'prepareForInList'), $currentIds));
			}
            
            // get post & associated thumbnails id
            $sql = "select p.ID, pm.meta_value from $wpdb->posts as p left join $wpdb->postmeta as pm on p.ID = pm.post_id where 1=1 and pm.post_id IN ($currentP) and pm.meta_key = '_thumbnail_id' and !isnull(pm.meta_id) order by p.ID;";
            $res = $wpdb->get_results( $sql, OBJECT_K );
            if ( empty($res) ) return array();
            
            // get unique thumbnails id
            $sql_thumb = "select distinct(pm.meta_value) from $wpdb->posts as p left join $wpdb->postmeta as pm on p.ID = pm.post_id where 1=1 and pm.post_id IN ($currentP) and pm.meta_key = '_thumbnail_id' and !isnull(pm.meta_id) order by p.ID;";
            $res_thumb = $wpdb->get_results( $sql_thumb, OBJECT_K );
            $thumbsId = array_keys($res_thumb);

            // get meta fields for thumbnails
            $thumb2meta = array('_wp_attachment_metadata' => array(), '_wp_attached_file' => array());
            foreach (array_chunk($thumbsId, self::$sql_chunk_limit, true) as $current) {

                $currentP = implode(',', array_map(array($this->the_plugin, 'prepareForInList'), $current));

                $sql_getmeta = "select p.ID, pm.meta_value from $wpdb->posts as p left join $wpdb->postmeta as pm on p.ID = pm.post_id where 1=1 and pm.meta_key = '_wp_attachment_metadata' and !isnull(pm.meta_id) and pm.post_id IN ($currentP) order by p.ID;";
                $res_getmeta = $wpdb->get_results( $sql_getmeta, OBJECT_K );
                //array_replace($prods2asin, $res_getmeta);
                $thumb2meta['_wp_attachment_metadata'] = $thumb2meta['_wp_attachment_metadata'] + $res_getmeta;
                
                $sql_getmeta = "select p.ID, pm.meta_value, p.guid from $wpdb->posts as p left join $wpdb->postmeta as pm on p.ID = pm.post_id where 1=1 and pm.meta_key = '_wp_attached_file' and !isnull(pm.meta_id) and pm.post_id IN ($currentP) order by p.ID;";
                $res_getmeta = $wpdb->get_results( $sql_getmeta, OBJECT_K );
                //array_replace($prods2asin, $res_getmeta);
                $thumb2meta['_wp_attached_file'] = $thumb2meta['_wp_attached_file'] + $res_getmeta;
            }
 
            $default_meta = array(
                'uploads'       => wp_upload_dir(), // cache this wp function!
            );
            $thumbs = array();
            foreach ($thumbsId as $key) {

                $meta = array_merge($default_meta, array());
                $meta['file'] = isset($thumb2meta['_wp_attached_file']["$key"])
                    ? $thumb2meta['_wp_attached_file']["$key"] : '';
  
                $meta['sizes'] = isset($thumb2meta['_wp_attachment_metadata']["$key"]->meta_value)
                    ? $thumb2meta['_wp_attachment_metadata']["$key"]->meta_value : '';
                if ( !empty($meta['sizes']) ) {
                    $meta['sizes'] = maybe_unserialize($meta['sizes']);
                }
                
                $thumbs["$key"] = $this->get_thumb_src( $meta, 'shop_thumbnail' );
                $thumbs["$key"] = isset($thumbs["$key"][0]) ? $thumbs["$key"][0] : '';
                $thumbs["$key"] = !empty($thumbs["$key"]) ? $thumbs["$key"] : $this->get_thumb_src_default();
				if ( strpos( $thumbs["$key"], $this->the_plugin->get_amazon_images_path() ) ) {
					$thumbs["$key"] = str_replace( $default_meta['uploads']['baseurl'] . '/', '', $thumbs["$key"] );
					$thumbs["$key"] = $this->the_plugin->amazon_url_to_ssl( $thumbs["$key"] );
				}
            }
    
            $post2thumb = array();
            foreach ( $res as $key => $val ) {
                $thumb_id = $val->meta_value;
                $post2thumb["$key"] = isset($thumbs["$thumb_id"]) && !empty($thumbs["$thumb_id"])
                    ? $thumbs["$thumb_id"] : $this->get_thumb_src_default();
            }
            return $post2thumb;
        }

        protected function get_thumb( $meta, $size='medium', $pms=array() ) {
            $image = $this->get_thumb_src( $meta, $size );
            
            $html = '';
            if ( $image ) {
                list($src, $width, $height) = $image;
                $hwstring = image_hwstring($width, $height);
                $size_class = $size;
                if ( is_array( $size_class ) ) {
                    $size_class = join( 'x', $size_class );
                }
                //$attachment = get_post($attachment_id);
                $default_attr = array(
                    'src'   => $src,
                    'class' => "attachment-$size_class",
                    'alt'   => isset($pms['alt']) ? $pms['alt'] : "$size_class", //trim(strip_tags( get_post_meta($attachment_id, '_wp_attachment_image_alt', true) )), // Use Alt field first
                );
                //if ( empty($default_attr['alt']) )
                //    $default_attr['alt'] = trim(strip_tags( $attachment->post_excerpt )); // If not, Use the Caption
                //if ( empty($default_attr['alt']) )
                //    $default_attr['alt'] = trim(strip_tags( $attachment->post_title )); // Finally, use the title
 
                $attr = wp_parse_args($attr, $default_attr);
 
                $attr = array_map( 'esc_attr', $attr );
                $html = rtrim("<img $hwstring");
                foreach ( $attr as $name => $value ) {
                    $html .= " $name=" . '"' . $value . '"';
                }
                $html .= ' />';
            }
            return $html;
        }

        protected function get_thumb_src( $meta, $size='medium' ) {
            $img_url = $this->wp_get_attachment_url($meta);
  
            $width = $height = 0;
            $img_url_basename = wp_basename($img_url);
                
            // try for a new style intermediate size
            if ( $intermediate = $this->image_get_intermediate_size($meta, $size) ) {
                $img_url = str_replace($img_url_basename, $intermediate['file'], $img_url);
                $width = $intermediate['width'];
                $height = $intermediate['height'];
            }
            elseif ( $size == 'thumbnail' ) {
                // fall back to the old thumbnail
                $file = isset($meta['file']->meta_value) ? $meta['file']->meta_value : '';

                if ( ($thumb_file = $file) && $info = getimagesize($thumb_file) ) {
                    $img_url = str_replace($img_url_basename, wp_basename($thumb_file), $img_url);
                    $width = $info[0];
                    $height = $info[1];
                }
            }
            
            if ( !$width && !$height && isset( $meta['sizes']['width'], $meta['sizes']['height'] ) ) {
                // any other type: use the real image
                $width = $meta['sizes']['width'];
                $height = $meta['sizes']['height'];
            }
            if ( $img_url) {
                // we have the actual image size, but might need to further constrain it if content_width is narrower
                list( $width, $height ) = image_constrain_size_for_editor( $width, $height, $size );
                return array( $img_url, $width, $height );
            }
            return false;
        }

        protected function wp_get_attachment_url( $meta ) {
            $url = '';

            $uploads = $meta['uploads'];
            $file = isset($meta['file']->meta_value) ? $meta['file']->meta_value : '';
            if ( !empty($file) ) {
                // Get upload directory.
                if ( $uploads && false === $uploads['error'] ) {
                    // Check that the upload base exists in the file location.
                    if ( 0 === strpos( $file, $uploads['basedir'] ) ) {
                        // Replace file location with url location.
                        $url = str_replace($uploads['basedir'], $uploads['baseurl'], $file);
                    } elseif ( false !== strpos($file, 'wp-content/uploads') ) {
                        $url = $uploads['baseurl'] . substr( $file, strpos($file, 'wp-content/uploads') + 18 );
                    } else {
                        // It's a newly-uploaded file, therefore $file is relative to the basedir.
                        $url = $uploads['baseurl'] . "/$file";
                    }
                }
            }

            if ( empty($url) ) {
                $url = isset($meta['sizes']->guid) ? $meta['sizes']->guid : '';
            }
            return $url;
        }

        protected function image_get_intermediate_size( $meta, $size='thumbnail' ) {
            if ( !is_array( $imagedata = $meta['sizes'] ) )
                return false;

            // get the best one for a specified set of dimensions
            if ( is_array($size) && !empty($imagedata['sizes']) ) {
                foreach ( $imagedata['sizes'] as $_size => $data ) {
                    // already cropped to width or height; so use this size
                    if ( ( $data['width'] == $size[0] && $data['height'] <= $size[1] ) || ( $data['height'] == $size[1] && $data['width'] <= $size[0] ) ) {
                        $file = $data['file'];
                        list($width, $height) = image_constrain_size_for_editor( $data['width'], $data['height'], $size );
                        return compact( 'file', 'width', 'height' );
                    }
                    // add to lookup table: area => size
                    $areas[$data['width'] * $data['height']] = $_size;
                }
                if ( !$size || !empty($areas) ) {
                    // find for the smallest image not smaller than the desired size
                    ksort($areas);
                    foreach ( $areas as $_size ) {
                        $data = $imagedata['sizes'][$_size];
                        if ( $data['width'] >= $size[0] || $data['height'] >= $size[1] ) {
                            // Skip images with unexpectedly divergent aspect ratios (crops)
                            // First, we calculate what size the original image would be if constrained to a box the size of the current image in the loop
                            $maybe_cropped = image_resize_dimensions($imagedata['width'], $imagedata['height'], $data['width'], $data['height'], false );
                            // If the size doesn't match within one pixel, then it is of a different aspect ratio, so we skip it, unless it's the thumbnail size
                            if ( 'thumbnail' != $_size && ( !$maybe_cropped || ( $maybe_cropped[4] != $data['width'] && $maybe_cropped[4] + 1 != $data['width'] ) || ( $maybe_cropped[5] != $data['height'] && $maybe_cropped[5] + 1 != $data['height'] ) ) )
                                continue;
                            // If we're still here, then we're going to use this size
                            $file = $data['file'];
                            list($width, $height) = image_constrain_size_for_editor( $data['width'], $data['height'], $size );
                            return compact( 'file', 'width', 'height' );
                        }
                    }
                }
            }
 
            if ( is_array($size) || empty($size) || empty($imagedata['sizes'][$size]) )
                return false;
 
            $data = $imagedata['sizes'][$size];
            // include the full filesystem path of the intermediate file
            if ( empty($data['path']) && !empty($data['file']) ) {
                $file_url = $this->wp_get_attachment_url($meta);
                $data['path'] = path_join( dirname($imagedata['file']), $data['file'] );
                $data['url'] = path_join( dirname($file_url), $data['file'] );
            }
            return $data;
        }


        /**
         * Pretty-prints the difference in two times.
         *
         * @param time $older_date
         * @param time $newer_date
         * @return string The pretty time_since value
         * @original link http://binarybonsai.com/code/timesince.txt
         */
        public function time_since( $older_date, $newer_date ) {
            return $this->interval( $newer_date - $older_date );
        }
        public function interval( $since ) {
            // array of time period chunks
            $chunks = array(
                array(60 * 60 * 24 * 365 , _n_noop('%s year', '%s years', 'WooZone')),
                array(60 * 60 * 24 * 30 , _n_noop('%s month', '%s months', 'WooZone')),
                array(60 * 60 * 24 * 7, _n_noop('%s week', '%s weeks', 'WooZone')),
                array(60 * 60 * 24 , _n_noop('%s day', '%s days', 'WooZone')),
                array(60 * 60 , _n_noop('%s hour', '%s hours', 'WooZone')),
                array(60 , _n_noop('%s minute', '%s minutes', 'WooZone')),
                array( 1 , _n_noop('%s second', '%s seconds', 'WooZone')),
            );
    
    
            if( $since <= 0 ) {
                return __('now', 'WooZone');
            }
    
            // we only want to output two chunks of time here, eg:
            // x years, xx months
            // x days, xx hours
            // so there's only two bits of calculation below:
    
            // step one: the first chunk
            for ($i = 0, $j = count($chunks); $i < $j; $i++)
                {
                $seconds = $chunks[$i][0];
                $name = $chunks[$i][1];
    
                // finding the biggest chunk (if the chunk fits, break)
                if (($count = floor($since / $seconds)) != 0)
                    {
                    break;
                    }
                }
    
            // set output var
            $output = sprintf(_n($name[0], $name[1], $count, 'WooZone'), $count);
    
            // step two: the second chunk
            if ($i + 1 < $j)
                {
                $seconds2 = $chunks[$i + 1][0];
                $name2 = $chunks[$i + 1][1];
    
                if (($count2 = floor(($since - ($seconds * $count)) / $seconds2)) != 0)
                    {
                    // add to output var
                    $output .= ' '.sprintf(_n($name2[0], $name2[1], $count2, 'WooZone'), $count2);
                    }
                }
    
            return $output;
        }
    
	
		/**
		 * Pagination
		 */
		protected function build_pagination_vars() {
			$ses = isset($_SESSION['WooZone_sync']) ? $_SESSION['WooZone_sync'] : array();
			$max_prods = $this->get_interface_max_products(true);

			$posts_per_page = isset($ses['posts_per_page']) ? $ses['posts_per_page'] : $max_prods;
			$paged = isset($ses['paged']) ? $ses['paged'] : 1;
			
			return compact('ses', 'max_prods', 'posts_per_page', 'paged');
		}
		protected function get_pagination( $pms=array() )
		{
			extract($pms);
			extract( $this->build_pagination_vars() );

			$html = array();

			$items_nr = $this->items_nr;
			$total_pages = $posts_per_page == 'all' ? 1 : ceil( $items_nr / $posts_per_page );
			
			$with_wrapp = isset($with_wrapp) && $with_wrapp ? true : false;
			
			{
				if ($with_wrapp) {
					$css_pag = 'WooZone-sync-pagination ';
					$css_pag .= ($position == 'bottom' ? 'WooZone-sync-top' : 'WooZone-sync-bottom');
					$html[] = 	'<div class="' . $css_pag . '">';
				}

				// pages
				$html[] = 		'<div class="WooZone-list-table-pagination tablenav">';

				$html[] = 			'<div class="tablenav-pages">';
				$html[] = 				'<span class="displaying-num">' . ( $items_nr ) . ' items</span>';
				if( $total_pages > 1 ){
					$html[] = 				'<span class="pagination-links"><a class="first-page ' . ( $paged <= 1 ? 'disabled' : '' ) . ' WooZone-jump-page" title="Go to the first page" href="#paged=1">&laquo;</a>';
					$html[] = 				'<a class="prev-page ' . ( $paged <= 1 ? 'disabled' : '' ) . ' WooZone-jump-page" title="Go to the previous page" href="#paged=' . ( $paged > 2 ? ($paged - 1) : '' ) . '">&lsaquo;</a>';
					$html[] = 				'<span class="paging-input"><input class="current-page" title="Current page" type="text" name="paged" value="' . ( $paged ) . '" size="2" style="width: 45px;"> of <span class="total-pages">' . ( ceil( $items_nr / $posts_per_page ) ) . '</span></span>';
					$html[] = 				'<a class="next-page ' . ( $paged >= ($total_pages - 1) ? 'disabled' : '' ) . ' WooZone-jump-page" title="Go to the next page" href="#paged=' . ( $paged >= ($total_pages - 1) ? $total_pages : $paged + 1 ) . '">&rsaquo;</a>';
					$html[] = 				'<a class="last-page ' . ( $paged >=  ($total_pages - 1) ? 'disabled' : '' ) . ' WooZone-jump-page" title="Go to the last page" href="#paged=' . ( $total_pages ) . '">&raquo;</a></span>';
				}
				$html[] = 			'</div>';
				$html[] = 		'</div>';
				
				// per page
				$html[] = 		'<div class="WooZone-box-show-per-pages">';
				$html[] = 			'<select name="WooZone-post-per-page" id="WooZone-post-per-page" class="WooZone-post-per-page WooZone-filter-general_field">';

                $_range = array_merge( array(), range(5, 50, 5), range(100, 500, 100) );
				foreach( $_range as $nr => $val ){
					$html[] = 			'<option value="' . ( $val ) . '" ' . ( $posts_per_page == $val ? 'selected' : '' ). '>' . ( $val ) . '</option>';
				}

				$html[] = 				'<option value="all"' . ( $posts_per_page == 'all' ? 'selected' : '' ) . '>';
				$html[] =				__('Show All', $this->the_plugin->localizationName);
				$html[] = 				'</option>';
				$html[] =			'</select>';
				$html[] = 			'<label for="WooZone-post-per-page" style="width:57px">' . __('per page', $this->the_plugin->localizationName) . '</label>';
				$html[] = 		'</div>';

				if ($with_wrapp) {
					$html[] = 	'</div>';
				}
			}

			return implode("\n", $html);
		}
	
	
        /**
         * Ajax requests
         */
        public function ajax_request()
        {
            global $wpdb;
            $request = array(
                'action'                        => isset($_REQUEST['subaction']) ? $_REQUEST['subaction'] : '',
                'module'                        => isset($_REQUEST['module']) ? $_REQUEST['module'] : 'synchronization',
                'sync_fields'                   => isset($_REQUEST['sync_fields']) ? $_REQUEST['sync_fields'] : array(),
                'sync_recurrence'               => isset($_REQUEST['sync_recurrence']) ? $_REQUEST['sync_recurrence'] : '',
                'sync_hour_start'               => isset($_REQUEST['sync_hour_start']) ? $_REQUEST['sync_hour_start'] : '',
                'sync_products_per_request'     => isset($_REQUEST['sync_products_per_request']) ? (int) $_REQUEST['sync_products_per_request'] : 10,
                
                'sync_stop_reload'              => isset($_REQUEST['sync_stop_reload']) ? (int) $_REQUEST['sync_stop_reload'] : 0,
                
                'id'                            => isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0,
                'asin'                          => isset($_REQUEST['asin']) ? (string) $_REQUEST['asin'] : '',

                'paged'     					=> isset($_REQUEST['paged']) ? (int) $_REQUEST['paged'] : 1,
                'posts_per_page'				=> isset($_REQUEST['post_per_page']) ? (string) $_REQUEST['post_per_page'] : 0,
            );
            extract($request);
            
            $ret = array(
                'status'        => 'invalid',
                'msg'           => '<div class="WooZone-sync-settings-msg WooZone-message WooZone-error">' . __('Invalid action!', $this->the_plugin->localizationName) . '</div>',
            );
            
            if ( empty($action) || !in_array($action, array(
            	'save_settings', 'load_products', 'sync_prod', 'auto_reload', 'paged', 'post_per_page', 'open_variations'
			)) ) {
                die(json_encode($ret));
            }
			
            if ( $action == 'save_settings' ) {
                if ( !empty($request['sync_fields']) ) {
                    $request['sync_fields'] = array_keys($request['sync_fields']);
                }
                
                $request = array_diff_key($request, array_fill_keys(array('action', 'id', 'asin'), 1));
                update_option($this->alias . '_sync', (array) $request);
                
                $this->init_settings();
                $ret = array_merge($ret, array(
                    'status'    => 'valid',
                    'msg'       => '<div class="WooZone-sync-settings-msg WooZone-message WooZone-success">' . __('Sync settings saved successfully.', $this->the_plugin->localizationName) . '</div>',
                    'form'      => $this->sync_settings(),
                ));
 
            } else if ( $action == 'load_products' ) {
                
				$new_paged = $paged < 1 ? 1 : $paged;
				$_SESSION['WooZone_sync']['paged'] = $new_paged;

				extract( $this->build_pagination_vars() );

                $productsList = $this->get_products(array(
                	'module' 			=> $module,
                	'paged'				=> $paged,
                	'posts_per_page'	=> $posts_per_page,
				));
                $html = isset($productsList['html']) ? implode(PHP_EOL, $productsList['html']) : '';
				
				$pagination = $this->get_pagination(array(
					//'position' 		=> 'bottom',
					'with_wrapp' 	=> false
				));

                $ret = array_replace_recursive($ret, $productsList, array(
                    'status'    	=> 'valid',
                    'msg'       	=> '',
                    'html'      	=> $html,
                    'pagination'	=> $pagination,
                ));
				
            } else if ( in_array($action, array('paged', 'post_per_page')) ) {
            	
				if ( 'post_per_page' == $action ) {
					$new_post_per_page = $posts_per_page;
	
					if ( $new_post_per_page == 'all' ){
						$_SESSION['WooZone_sync']['posts_per_page'] = 'all';
					}
					else if ( (int)$new_post_per_page == 0 ){
						$max_prods = $this->get_interface_max_products(true);
						$_SESSION['WooZone_sync']['posts_per_page'] = $max_prods;
					}
					else {
						$_SESSION['WooZone_sync']['posts_per_page'] = (int) $new_post_per_page;
					}
	
					// reset the paged as well
					$_SESSION['WooZone_sync']['paged'] = 1;
				}
				else {
					$new_paged = $paged < 1 ? 1 : $paged;
					$_SESSION['WooZone_sync']['paged'] = $new_paged;
				}
				
				extract( $this->build_pagination_vars() );
                
                $productsList = $this->get_products(array(
                	'module' 			=> $module,
                	'paged'				=> $paged,
                	'posts_per_page'	=> $posts_per_page,
				));
                $html = isset($productsList['html']) ? implode(PHP_EOL, $productsList['html']) : '';
				
				$pagination = $this->get_pagination(array(
					//'position' 		=> 'bottom',
					'with_wrapp' 	=> false
				));

                $ret = array_replace_recursive($ret, $productsList, array(
                    'status'    	=> 'valid',
                    'msg'       	=> '',
                    'html'      	=> $html,
                    'pagination'	=> $pagination,
                ));

            } else if ( $action == 'open_variations' ) {
                
                $productsList = $this->get_product_variations(array(
                	'module' 			=> $module,
                	'prodid'			=> $id,
				));
                $html = isset($productsList['html']) ? implode(PHP_EOL, $productsList['html']) : '';
				
                $ret = array_replace_recursive($ret, $productsList, array(
                    'status'    	=> 'valid',
                    'msg'       	=> '',
                    'html'      	=> $html,
                ));

            } else if ( $action == 'sync_prod' ) {
                if ( empty($asin) ) {
                    $asin = get_post_meta($id, '_amzASIN', true);
                }

                // sync product!
                require_once( $this->the_plugin->cfg['paths']['plugin_dir_path'] . '/modules/synchronization/init.php' );
                $sync = new wwcAmazonSyncronize( $this->the_plugin );
                $syncStat = $sync->updateTheProduct( $asin, 'return' );

                $last_date = get_post_meta($id, '_amzaff_sync_last_date', true);
                $last_status = get_post_meta($id, '_amzaff_sync_last_status_msg', true);
                $last_status = !empty($last_status) ? strtoupper('last sync status: ' . $last_status) : '';
                //$last_status = !empty($last_status) ? '<i class="fa fa-comment-o" title="' . $last_status . '"></i>' : '';

                $ret = array_merge($ret, $syncStat, array(
                    'status'            => 'valid',
                    'sync_hits'         => (int) get_post_meta($id, '_amzaff_sync_hits', true),
                    'sync_last_date'    => $this->the_plugin->last_update_date('true', $last_date),
                    'sync_last_status'  => $last_status,
                ));

            } else if ( $action == 'auto_reload' ) {
                $ss = get_option($this->alias . '_sync');
                $ss = $ss !== false ? $ss : array();
                $ss['sync_stop_reload'] = $request['sync_stop_reload'];

                update_option($this->alias . '_sync', $ss);
                
                $ret = array_merge($ret, array(
                    'status'    => 'valid',
                    'msg'       => '',
                ));
 
            }
            die(json_encode($ret));
        }
	}
}

// Initialize the WooZoneBaseInterfaceSync class
//$WooZoneBaseInterfaceSync = WooZoneBaseInterfaceSync::getInstance();