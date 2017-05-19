<?php
/*
* Define class WooZoneTailSyncMonitor
* Make sure you skip down to the end of this file, as there are a few
* lines of code that are very important.
*/
!defined('ABSPATH') and exit;

if (class_exists('WooZoneBaseInterfaceSync') != true) {
	global $WooZone;
	require( $WooZone->cfg['paths']['plugin_dir_path'] . 'modules/synchronization/base_interface.php' );
}

if (class_exists('WooZoneTailSyncMonitor') != true) {
    class WooZoneTailSyncMonitor extends WooZoneBaseInterfaceSync
    {
        /*
        * Some required plugin information
        */
        const VERSION = '1.0';

        static protected $_instance;
        

        /*
        * Required __construct() function that initalizes the AA-Team Framework
        */
        public function __construct()
        {
			parent::__construct();

            if (is_admin()) {
                add_action('admin_menu', array( &$this, 'adminMenu' ));
            }

            // ajax helper
			//add_action('wp_ajax_WooZoneTailSyncAjax', array( $this, 'here_ajax_request' ));
        }

        /**
        * Singleton pattern
        *
        * @return WooZoneTailSyncMonitor Singleton instance
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
                $this->the_plugin->alias . " " . __('Synchronization log', $this->the_plugin->localizationName),
                __('Synchronization log'),
                'manage_options',
                $this->the_plugin->alias . "_synclog",
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
        public function printBaseInterface( $module='synchronization' ) {
        	parent::printBaseInterface( $module );
        }


        /**
         * Ajax requests
         */
        public function here_ajax_request()
        {
            global $wpdb;
            $request = array(
                'action'                        => isset($_REQUEST['subaction']) ? $_REQUEST['subaction'] : '',
                'module'                        => isset($_REQUEST['module']) ? $_REQUEST['module'] : 'synchronization',

                'id'                            => isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0,
                'asin'                          => isset($_REQUEST['asin']) ? (string) $_REQUEST['asin'] : '',

                'paged'     					=> isset($_REQUEST['paged']) ? (int) $_REQUEST['paged'] : 1,
                'posts_per_page'				=> isset($_REQUEST['post_per_page']) ? (string) $_REQUEST['post_per_page'] : 0,
            );
            extract($request);

            if ( $action == '__my_custom_action__' ) {

                $ret = array_merge($ret, array(
                    'status'    => 'valid',
                    'msg'       => '',
                ));
 
            }
			// :: parent general actions
			//else {
			//	parent::ajax_request();            	
            //}
			
            die(json_encode($ret));
        }
	}
}

// Initialize the WooZoneTailSyncMonitor class
//$WooZoneTailSyncMonitor = new WooZoneTailSyncMonitor($this->cfg, $module);
$WooZoneTailSyncMonitor = WooZoneTailSyncMonitor::getInstance();