<?php
/*
* Define class WooZoneCronjobs
* Make sure you skip down to the end of this file, as there are a few
* lines of code that are very important.
*/
!defined('ABSPATH') and exit;
if (class_exists('WooZoneCronjobs') != true) {
    class WooZoneCronjobs
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
		
        public $is_admin = false;
        
        public $alias = '';
        public $localizationName = '';
        
        static private $cron_config_alias = '';
        public $custom_schedules = array();
        public $config = array();


        /*
        * Required __construct() function that initalizes the AA-Team Framework
        */
        public function __construct($WooZone)
        {
        	//global $WooZone;
   
        	$this->the_plugin = $WooZone;
			$this->module_folder = $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'modules/cronjobs/';
            $this->module_folder_path = $this->the_plugin->cfg['paths']['plugin_dir_path'] . 'modules/cronjobs/';
			$this->module = isset($this->the_plugin->cfg['modules']['cronjobs']) ? $this->the_plugin->cfg['modules']['cronjobs'] : array();
            
            $this->alias = $this->the_plugin->alias;
            $this->localizationName = $this->the_plugin->localizationName;
 
            $this->is_admin = $this->the_plugin->is_admin;
            
            self::$cron_config_alias = $this->alias.'_cronjobs';

			$this->build_config();
            if ( empty($this->config) ) return;
            
            $this->init();
        }
        
        /**
        * Singleton pattern
        *
        * @return WooZoneCronjobs Singleton instance
        */
        static public function getInstance()
        {
            if (!self::$_instance) {
                self::$_instance = new self;
            }

            return self::$_instance;
        }
        
        
        /**
         * Init...
         */
        public function init() {
            add_filter('cron_schedules', array($this, 'add_custom_schedules'));
            $this->add_cron_actions();
            add_action('init', array($this, 'add_cron_events'));
        }
        
		public function build_config() {
            // Initialize the wwcAmazonSyncronize class
            require_once( $this->the_plugin->cfg['paths']['plugin_dir_path'] . '/modules/cronjobs/cronjobs.config.php' );
            $cronObj = new WooZoneCronjobsConfig($this->the_plugin);
 
		    $this->custom_schedules = $cronObj->get_custom_schedules();
            $this->config = $cronObj->get_config();
 
            foreach ($this->config as $cron_id => $cron) {
                if ( isset($cron["recurrence_wp"])
                    && in_array($cron["recurrence_wp"], array_keys($this->custom_schedules)) ) {

                    $this->config["$cron_id"]["recurrence_wp"] = $this->alias . '_' . $cron["recurrence_wp"]; 
                }
            }
		}

        public function get_config($cron_id='all', $include_dynamic=true) {
            $cfg = $this->config;

            if ( empty($cfg) || !is_array($cfg) ) return array();
     
            if ( $cron_id!=='all' ) {
                if ( !isset($cfg["$cron_id"]) || !is_array($cfg["$cron_id"]) || empty($cfg["$cron_id"]) ) {
                    return array();
                } else {
                    $cfg = $cfg["$cron_id"];
                }
            }
 
            $ret = $cfg;
            if ( $include_dynamic ) {
                $opt = get_option(self::$cron_config_alias, array());
                if ( $cron_id!=='all' ) {
                    if ( !empty($opt) && isset($opt["$cron_id"]) ) {
                        $opt = (array) $opt["$cron_id"];
                    }
                }
                $ret = array_merge_recursive($ret, (array) $opt);
            }
            
            // keep only those that exists in config too!
            if ( $cron_id!=='all' ) {
            	if ( !isset($cfg) || empty($cfg) ) {
            		unset( $ret );
            	}
            } else {
            	foreach ($ret as $__cron_id => $__cron) {
            		if ( !isset($cfg["$__cron_id"]) || empty($cfg["$__cron_id"]) ) {
            			unset( $ret["$__cron_id"] );
            		}
				}
            }
            
            if ( $cron_id!=='all' ) {
                $ret = array_merge_recursive($ret, (array) $this->set_config_extra($ret));
            } else {
                foreach ($ret as $__cron_id => $__cron) {
                    if ( empty($__cron) || !is_array($__cron) ) continue 1;
                    $ret["$__cron_id"] = array_merge_recursive($ret["$__cron_id"],
                        (array) $this->set_config_extra($ret["$__cron_id"])
                    );
                }
            }

            return $ret;
        }

        private function set_config_extra( $cron ) {
            $ret = array();
            if ( !isset($cron['status']) || empty($cron['status']) ) {
                $ret['status'] = $cron['status_default'];
            }
            if ( !isset($cron['is_active']) || empty($cron['is_active'])
                || !in_array($cron['is_active'], array('no', 'yes')) ) {
                $ret['is_active'] = isset($cron['is_active_default']) ? $cron['is_active_default'] : 'no';
            }
            if ( isset($cron['end_time'], $cron['start_time']) ) {
                $ret['run_duration'] = $cron['end_time'] - $cron['start_time'];
                $ret['run_duration'] = number_format($ret['run_duration'], 2);
            }
            return $ret;
        }


        /**
         * Run cron
         */
        public function run_core( $cron_id, $cron ) {
            $cfg = isset($this->config) ? $this->config : array();
            if ( empty($cfg) || !is_array($cfg) ) return array();

            $external_func_status = array();
            {
                switch ($cron_id) {
                    case 'sync_products':
                        // Initialize the wwcAmazonSyncronize class
                        require_once( $this->the_plugin->cfg['paths']['plugin_dir_path'] . '/modules/synchronization/init.php' );
                        $sync = new wwcAmazonSyncronize($this->the_plugin);

                        $external_func_status = $sync->cron_small_bulk( $cron, 'return' );
                        break;
                        
                    case 'sync_products_cycle':
                        // Initialize the wwcAmazonSyncronize class
                        require_once( $this->the_plugin->cfg['paths']['plugin_dir_path'] . '/modules/synchronization/init.php' );
                        $sync = new wwcAmazonSyncronize($this->the_plugin);

                        $__cron_status_all = $this->get_cron('sync_products');
                        $__cron_status = isset($__cron_status_all['status']) ? $__cron_status_all['status'] : $cfg['sync_products']['status_default'];
                        $cron = array_merge((array) $cron, array(
                            'verify'    => array(
                                'sync_products'     => $__cron_status
                            )
                        ));
                        $external_func_status = $sync->cron_full_cycle( $cron, 'return' );
                        break;
                    
                    case 'assets_download':
                        // Initialize the WooZoneAssetDownload class
                        require_once( $this->the_plugin->cfg['paths']['plugin_dir_path'] . '/modules/assets_download/init.php' );
                        $amzaffAssetDownload = new WooZoneAssetDownload();
                        
                        $external_func_status = $amzaffAssetDownload->cronjob( $cron, 'return' );
                        break;
                        
                    case 'report':
                        // Initialize the WooZoneReport class
                        require_once( $this->the_plugin->cfg['paths']['plugin_dir_path'] . '/modules/report/init.php' );
                        $WooZoneReport = new WooZoneReport();
                        
                        $external_func_status = $WooZoneReport->cronjob( $cron, 'return' );
                        break;
						
                    case 'autoimport_queue':
                        // Initialize the WooZoneAutoImport class
                        require_once( $this->the_plugin->cfg['paths']['plugin_dir_path'] . '/modules/auto_import/init.php' );
                        $WooZoneAutoImport = new WooZoneAutoImport(true);
                        
                        $external_func_status = $WooZoneAutoImport->cronjob_queue( $cron, 'return' );
                        break;
						
                    case 'autoimport_search':
                        // Initialize the WooZoneAutoImport class
                        require_once( $this->the_plugin->cfg['paths']['plugin_dir_path'] . '/modules/auto_import/init.php' );
                        $WooZoneAutoImport = new WooZoneAutoImport(true);
                        
                        $external_func_status = $WooZoneAutoImport->cronjob_search( $cron, 'return' );
                        break;
                }
            }
            return $external_func_status;
        }
        public function run( $cron_id ) {
            // debug
            //update_option($this->alias.'_'.$cron_id.'_cron', array('cron_id' => $cron_id, 'time' => time()));
            
            // unblock crons!
            if ( $cron_id == 'unblock_crons' ) {
                return $this->unblock( $cron_id );
            }

            $cfg = isset($this->config["$cron_id"]) ? $this->config["$cron_id"] : array();
            if ( empty($cfg) || !is_array($cfg) ) return array();

            // default init...
            $cron_status_all = $this->get_cron($cron_id);
            $cron_status = isset($cron_status_all['status']) ? $cron_status_all['status'] : $cfg['status_default'];
            
            // verify if current cron is blocked: status is running or stop
            ///*debug
            if ( in_array($cron_status, array('running', 'stop')) ) {
                return true;
            }
            //*/
            $this->set_cron($cron_id, array('status' => 'running', 'start_time' => time(), 'end_time' => time())); // current cron status become running!
            
            $ret = array('status' => 'done');
            if ( /*debug 1 ||*/ in_array($cron_status, array('new', 'failed', 'done')) ) {

                // run core...
                $external_func_status = $this->run_core($cron_id, $cron_status_all);

                //var_dump('<pre>external_func_status:',$external_func_status,'</pre>');
                $ret = array_merge($ret, (array) $external_func_status);
            }
            $cron_new_status = isset($ret['status']) ? $ret['status'] : 'done';
            $this->set_cron($cron_id, array('status' => $cron_new_status, 'end_time' => time())); // current cron status become done | failed!
 
            // depedency
            if ( isset($ret['depedency']) && !empty($ret['depedency']) ) {
                foreach ( $ret['depedency'] as $_cronid => $_cronstatus) {
                    $this->set_cron($_cronid, array('status' => $_cronstatus)); // depedency cron new status!
                }
            }
            
            //var_dump('<pre>', $cron_id, $ret, '</pre>');
            return $cron_new_status;
        }

        private function unblock( $cron_id ) {
            // debug
            //update_option($this->alias.'_'.$cron_id.'_cron', array('cron_id' => $cron_id, 'time' => time()));
			   
            $cfg = isset($this->config["$cron_id"]) ? $this->config["$cron_id"] : array();
            if ( empty($cfg) || !is_array($cfg) ) return array();

            // default init...
            $cron_status_all = $this->get_cron($cron_id);
            $cron_status = isset($cron_status_all['status']) ? $cron_status_all['status'] : $cfg['status_default'];
            
            // verify if current cron is blocked: status is running or stop
            ///*debug
            if ( in_array($cron_status, array('running', 'stop')) ) {
                return true;
            }
            //*/
            $this->set_cron($cron_id, array('status' => 'running', 'start_time' => time())); // current cron status become running!
            
            $ret = array('status' => 'done');
            if ( /*debug 1 ||*/ in_array($cron_status, array('new', 'failed', 'done')) ) {
                $current_time = time();

                foreach ($this->config as $__cron_id => $__cron) {
                    if ( $__cron_id == 'unblock_crons' ) continue 1;

                    $__cron_status_all = $this->get_cron($__cron_id);
                    $__cron_status = isset($__cron_status_all['status']) ? $__cron_status_all['status'] : $__cron['status_default'];

                    if ( $__cron_status == 'running' && ( $current_time > (int) ( $__cron_status_all['start_time'] + $__cron['max_execution_time'] ) ) ) {
                        $__cron_new_status = 'failed';
                        $this->set_cron($__cron_id, array('status' => $__cron_new_status, 'end_time' => time()));
                    }
                }
            }
            $cron_new_status = isset($ret['status']) ? $ret['status'] : 'done';
            $this->set_cron($cron_id, array('status' => $cron_new_status, 'end_time' => time())); // current cron status become done | failed!
            
            //var_dump('<pre>', $cron_id, $ret, '</pre>');
            return $cron_new_status;
        }


        /**
         * Get / Set single cron details 
         */
        public function get_cron( $cron_id, $fields='all' ) {
            $cfg = $this->get_config($cron_id);
            if ( empty($cfg) ) return array();

            if ( $fields === 'all' ) {
                return $cfg;
            } else if ( !is_array($fields) && isset($cfg["$fields"]) ) {
                return $cfg["$fields"];
            } else if ( is_array($fields) ) {
                return array_intersect_key($cfg, array_fill_keys($fields, 1));
            }
            return array();
        }
        public function set_cron( $cron_id, $fields=array() ) {
            $opt = (array) get_option(self::$cron_config_alias, array());
            if ( !isset($opt["$cron_id"]) || !is_array($opt["$cron_id"]) ) {
                $opt["$cron_id"] = array();
            }
            $opt["$cron_id"] = array_merge($opt["$cron_id"], $fields);

            //var_dump('<pre>set_cron:',self::$cron_config_alias, $opt,'</pre>');  
            update_option(self::$cron_config_alias, $opt);
            return $opt;
        }


        /**
         * Schedule crons
         */
        public function add_custom_schedules( $schedules ) {
            $new = array();
            foreach ($this->custom_schedules as $key => $val) {
                $_key = $this->alias . '_' . $key;
                $new["$_key"] = $val;
            }

            $schedules = array_merge($schedules, $new);
            return $schedules;
        }

        public function add_cron_events() {
            $cfg = $this->get_config();
            foreach ($cfg as $cron_id => $cron) {
                if ( $cron['is_active'] != 'yes' ) continue 1;
                $this->schedule_cron( $cron_id );
            }
        }
        
        public function schedule_cron( $cron_id ) {
            $cron = $this->get_cron($cron_id);
            $_cron_id = $this->alias . '_' . $cron_id;

            if ( !wp_next_scheduled($_cron_id, array($cron_id)) ) {
                
                $recurrence = $cron['recurrence_wp'];
                if ( $cron['start_hour'] === 'now' ) {
                    $start_time = time();
                } else {
                    $start_time = strtotime( date('y-m-d') ) + ( 3600 * $cron['start_hour'] );
                }
                //$start_hour += 30; // add a small delay of 30 seconds
 
                if ( $cron['is_active'] == 'yes' ) {
                    wp_schedule_event($start_time, $recurrence, $_cron_id, array($cron_id));
                }
            }
        }

        public function add_cron_actions() {
            $cfg = $this->get_config();
            foreach ($cfg as $cron_id => $cron) {
                
                $hook_func = 'run';
                $_cron_id = $this->alias . '_' . $cron_id;
                add_action($_cron_id, array($this, $hook_func), 10, 1);

                if ( $cron['is_active'] != 'yes' ) {
                    wp_clear_scheduled_hook( $_cron_id, array($cron_id) );
                }
            }
        }
    }
}

//$WooZoneCronjobs = new WooZoneCronjobs();
//$WooZoneCronjobs = WooZoneCronjobs::getInstance();