<?php
/*
* Define class WooZoneReport
* Make sure you skip down to the end of this file, as there are a few
* lines of code that are very important.
*/
!defined('ABSPATH') and exit;
      
if (class_exists('WooZoneReport') != true) {
    class WooZoneReport
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
        
        static private $report_alias = '';
        static private $report_alias_act = '';
        
        static private $settings = array();
        
        static private $sql_chunk_limit = 2000;
        static private $current_time = null;
		
		private $device = '';
		private $view_in_browser = '';
		
		private $log_ids = array();
		private $log_actions = array();
		
		// auto import
		private static $max_nb_tries = 3; // maximum number or retries to import product / execute search
		private $ai_status_values = array(); // see class Constructor for values


        /*
        * Required __construct() function that initalizes the AA-Team Framework
        */
        public function __construct()
        {
        	global $WooZone;

            $this->the_plugin = $WooZone;

            $this->module_folder = $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'modules/report/';
            $this->module_folder_path = $this->the_plugin->cfg['paths']['plugin_dir_path'] . 'modules/report/';
            //$this->module = $module; // gives warning undefined variable.
            
            $this->alias = $this->the_plugin->alias;
            $this->localizationName = $this->the_plugin->localizationName;
 
            $this->is_admin = $this->the_plugin->is_admin;
            
            self::$report_alias = $this->alias.'%s_report';
            self::$report_alias_act = $this->alias.'%s_report_act';
            
            $ss = get_option($this->alias . '_report', array());
            $ss = maybe_unserialize($ss);
            self::$settings = $ss !== false ? $ss : array();

            self::$current_time = time();
			
			$this->device = isset($_REQUEST['device']) ? $_REQUEST['device'] . "_" : '';
			
			$this->log_ids = array(
				'report' 			=> array('title' => __('Report', $this->the_plugin->localizationName)),
				//'testing' 			=> array('title' => __('Testing', $this->the_plugin->localizationName)),
			);
			$this->log_actions = array(
				'products_status' 	=> array('title' => __('Products status', $this->the_plugin->localizationName)),
				'auto_import'		=> array('title' => __('Auto import stats', $this->the_plugin->localizationName)),
			);
			
			$this->ai_status_values = array(
				'new'		=> __('New', $this->the_plugin->localizationName),
				'done'		=> __('Done (success)', $this->the_plugin->localizationName),
				'error'		=> __('Error', $this->the_plugin->localizationName),
				'already'	=> __('Already imported', $this->the_plugin->localizationName),
			);
			
            if (is_admin()) {
                add_action('admin_menu', array( &$this, 'adminMenu' ));
            }

            // ajax helper
            add_action('wp_ajax_WooZone_report', array( &$this, 'ajax_request' ));
            
            // ajax helper
            // ...see also /utils/action_admin_ajax.php
        }

		/**
	    * Singleton pattern
	    *
	    * @return WooZoneReport Singleton instance
	    */
	    static public function getInstance()
	    {
	        if (!self::$_instance) {
	            self::$_instance = new self;
	        }
            //self::$_instance->debug();
	        return self::$_instance;
	    }
        
        private function debug() {
            $this->build_current_report();
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
                $this->the_plugin->alias . " " . __('Report logs', $this->the_plugin->localizationName),
                __('Report logs'),
                'manage_options',
                $this->the_plugin->alias . "_report",
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
        public function printBaseInterface( $module='report' ) {
            global $wpdb;
            
            $ss = self::$settings;

            $mod_vars = array();

            // Sync
            $mod_vars['mod_menu'] = 'info|report';
            $mod_vars['mod_title'] = __('Report logs', $this->the_plugin->localizationName);

            extract($mod_vars);
            
            $module_data = $this->the_plugin->cfg['modules']["$module"];
            $module_folder = $this->the_plugin->cfg['paths']['plugin_dir_url'] . "modules/$module/";
?>
        <script type="text/javascript" src="<?php echo $this->module_folder;?>app.report.js" ></script>
        
        <div id="<?php echo WooZone()->alias?>" class="WooZone-report-log">
            
            <div class="<?php echo WooZone()->alias?>-content">
            	
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
						<div class="panel-heading WooZone-panel-heading">
							<h2><?php echo $mod_title; ?></h2>
						</div>
						
						<div class="panel-body WooZone-panel-body">
		            
                            <div id="WooZone-report" class="WooZone-panel-content" data-module="<?php echo $module; ?>">

                                <?php
                                   $lang = array(
                                       'no_products'          => __('No report logs available.', 'WooZone'),
                                       'loading'              => __('Loading..', 'WooZone'),
                                   );
                                ?>
                                <div id="WooZone-lang-translation" style="display: none;"><?php echo htmlentities(json_encode( $lang )); ?></div>

                                <!-- Main loading box -->
                                <div id="WooZone-main-loading">
                                    <div id="WooZone-loading-overlay"></div>
                                    <div id="WooZone-loading-box">
                                        <div class="WooZone-loading-text"><?php _e('Loading', $this->the_plugin->localizationName);?></div>
                                        <div class="WooZone-meter WooZone-animate" style="width:86%; margin: 34px 0px 0px 7%;"><span style="width:100%"></span></div>
                                    </div>
                                </div>
    
                                <div class="WooZone-sync-filters">
									<?php
										$__pms = array(
											'log_id'		=> isset($_SESSION['WooZone_report']["log_id"])
												? $_SESSION['WooZone_report']["log_id"] : '',
											'log_action'	=> isset($_SESSION['WooZone_report']["log_action"])
												? $_SESSION['WooZone_report']["log_action"] : '',
										);
										if ( count($this->log_ids) > 1 ) {
											$html = array();
											$html[] = 	'<select name="WooZone-filter-log_id" class="WooZone-filter-log_id">';
											$html[] = 		'<option value="" disabled="disabled">';
											$html[] =			__('Log id', $this->the_plugin->localizationName);
											$html[] = 		'</option>';
											$html[] = 		'<option value="" >';
											$html[] =			__('Show All', $this->the_plugin->localizationName);
											$html[] = 		'</option>';
						
								            foreach ( $this->log_ids as $id => $row ){
												$html[] = 	'<option ' . ( $id == $__pms['log_id'] ? 'selected' : '' ) . ' value="' . ( $id ) . '">';
												$html[] = 		( $row['title'] );
												$html[] = 	'</option>';
								            }
						
											$html[] =	'</select>';
											echo implode(PHP_EOL, $html);
										}

										if ( count($this->log_actions) > 1 ) {
											$html = array();
											$html[] = 	'<select name="WooZone-filter-log_action" class="WooZone-filter-log_action">';
											$html[] = 		'<option value="" disabled="disabled">';
											$html[] =			__('Log action', $this->the_plugin->localizationName);
											$html[] = 		'</option>';
											$html[] = 		'<option value="" >';
											$html[] =			__('Show All', $this->the_plugin->localizationName);
											$html[] = 		'</option>';
						
								            foreach ( $this->log_actions as $id => $row ){
												$html[] = 	'<option ' . ( $id == $__pms['log_action'] ? 'selected' : '' ) . ' value="' . ( $id ) . '">';
												$html[] = 		( $row['title'] );
												$html[] = 	'</option>';
								            }
						
											$html[] =	'</select>';
											echo implode(PHP_EOL, $html);
										}
									?>
                                    <span>
                                        <?php _e('Total report logs', $this->the_plugin->localizationName);?>: <span class="count"></span>
                                    </span>
                                    <span class="right">
                                        <button class="load_rows"><?php _e('Reload report logs list', $this->the_plugin->localizationName);?></button>
                                    </span>
                                </div>
                                <div class="WooZone-sync-table <?php echo ( $module == 'report' ? 'report' : '' ); ?>">
                                  <table cellspacing="0">
                                    <thead>
                                        <tr class="WooZone-sync-table-header">
                                            <th style="width:3%;"><?php _e('ID', $this->the_plugin->localizationName);?></th>
                                            <th style="width:10%;"><?php _e('Log Id', $this->the_plugin->localizationName);?></th>
                                            <th style="width:10%;"><?php _e('Log Action', $this->the_plugin->localizationName);?></th>
                                            <th style="width:53%;"><?php _e('Log Desc', $this->the_plugin->localizationName);?></th>
                                            <th style="width:14%;"><?php _e('Date Added', $this->the_plugin->localizationName);?></th>
                                            <th style="width:10%;"><?php _e('Action', $this->the_plugin->localizationName);?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        //require_once( $this->module_folder_path . '_html.php');
                                    ?>
                                    </tbody>
                                  </table>
                                </div>
                                <?php /*if ( $module == 'report' ) { ?>
                                    <div class="WooZone-sync-info">
                                      <h3><?php _e('Settings', $this->the_plugin->localizationName);?></h3>
                                      <?php //echo $this->report_settings(); ?>
                                    </div>
                                <?php }*/ ?>
                            </div>
		            	</div>
		        	</div>
		        </section>
			</div>
        </div>

<?php
        }


		/**
		 * General Report methods - build row listing interface & other utils
		 */
        private function get_rows( $pms=array() ) {
            global $wpdb;
           
            $table_name_report = $wpdb->prefix . "amz_report_log";
            $sql = "SELECT p.ID, p.log_id, p.log_action, p.desc, p.date_add FROM $table_name_report as p WHERE 1=1 %s ORDER BY p.ID DESC;";
			
			// dropdown filter fields
			$filter_where = '';
			$filter_fields = array('log_id', 'log_action');
			foreach ($filter_fields as $field) {
				$field_val = isset($pms["$field"]) && trim($pms["$field"]) != "" ? $pms["$field"] : '';
				if ( $field_val != '' ) {
					$filter_where .= " AND $field = '" . esc_sql($field_val) . "' ";
				}
			}
			$sql = sprintf( $sql, $filter_where );

            $res = $wpdb->get_results( $sql, OBJECT_K );
            
            if ( empty($res) ) return array();
            
            // build html table with products rows
            $default = array(
			//	'module'        => $module,
            );
 
            $ret = array('status' => 'valid', 'html' => array(), 'nb' => 0);
            $nbprod = 0;
            foreach ($res as $id => $val) {
                
                $__p = $this->row_build(array_merge($default, array(
                    'id'            => $id,
                    'val'           => $val,
                )));
                $__p = array_merge($__p, array(
                    'id'            => $id,
                ));
                
                // product
                $ret['html'][] = $this->row_view_html($__p);
                
                $nbprod++;
            } // end products loop
            
            $ret = array_merge($ret, array(
                'nb'        => $nbprod,
            ));
            
            return $ret;
        }

        private function row_build( $pms ) {
            extract($pms);

            $log_id = $val->log_id;
            $log_action = $val->log_action;
            $desc = $val->desc;
                
            $add_data = $val->date_add;
            $add_data = $this->the_plugin->last_update_date('true', strtotime($add_data), true);

			$module = '';
            //if ( $module == 'report' ) {
                $ret = compact('module', 'add_data', 'log_id', 'log_action', 'desc');
            //}
            return $ret;
        }

        private function row_view_html( $row ) {
            $tr_css = '';
            
            //if ( $row['module'] == 'report' ) {
                $text_log_id = $this->log_nice_format( $row['log_id'] );
                $text_log_action = $this->log_nice_format( $row['log_action'] );
                $text_viewlog = __('View log', $this->the_plugin->localizationName);
            //}
            
            //if ( $row['module'] == 'report' ) {
            $ret = '
                    <tr class="WooZone-sync-table-row' . $tr_css . '" data-id=' . $row['id'] . ' data-log_id=' . $row['log_id'] . ' data-log_action=' . $row['log_action'] . '>
                        <td><span>' . $row['id'] . '</span></td>
                        <td>' . $text_log_id . '</td>
                        <td>' . $text_log_action . '</td>
                        <td>' . $row['desc'] . '</td>
                        <td>' . $row['add_data'] . '</td>
                        <td class="WooZone-sync-now"><button>' . $text_viewlog . '</button></td>
                    </tr>
                ';
            //}
            return $ret;
        }

        private function get_view_log( $pms=array() ) {
            extract($pms);

            $row_data = (array) $this->get_log_data( $id );
            extract($row_data);

            $log_id = $this->log_nice_format( $log_id );
            $log_action = $this->log_nice_format( $log_action );
            $date_add = $this->the_plugin->last_update_date('true', strtotime($date_add), true);

            $html = array();
            $html[] = '<div class="WooZone-report-log-lightbox">';
            $html[] =   '<div class="WooZone-download-in-progress-box">';
            $html[] =       '<h1>' . __('View log', $this->localizationName ) . '<a href="#" id="WooZone-close-btn"><i class="fa fa-times-circle" aria-hidden="true"></i></a></h1>';
            $html[] =       '<p class="WooZone-callout WooZone-callout-info">';
            $html[] =       sprintf( __('Log id: <strong>%s</strong> | Log action: <strong>%s</strong> | Date: <em>%s</em>', $this->localizationName ), $log_id, $log_action, $date_add );
            $html[] =       '</p>';

            /*
            $html[] =       '<table class="WooZone-table WooZone-debug-info">';
            $html[] =           '<tr>';
            $html[] =               '<td width="150">' . __('Total Images:', $this->localizationName ) . '</td>';
            $html[] =               '<td>' . ( count($assets) ) . '</td>';
            $html[] =           '</tr>';
            $html[] =       '</table>';
            */
            $html[] = 		'<div class="WooZone-report-wrapper">';
			
				            $log_code = "{$row_data['log_id']}|{$row_data['log_action']}";
				            switch ($log_code) {
				                case 'report|products_status':
				                    $html[] = $this->_get_report_products_status($row_data, 'view_log');
				                    break;
									
				                case 'report|auto_import':
				                	$html[] = $this->_ai_get_report_products_status($row_data, 'view_log');
				                    break;
				            }
							
			$html[] =   	'</div>';
            $html[] =   '</div>';
            $html[] = '</div>';

            return implode("\n", $html);
        }

        private function get_log_data($id) {
            global $wpdb;
            
            $table_name_report = $wpdb->prefix . "amz_report_log";
            $sql = "SELECT p.log_id, p.log_action, p.desc, p.date_add, p.log_data_type, p.log_data FROM $table_name_report as p WHERE 1=1 AND p.ID = '%s';";
            $sql = sprintf($sql, $id);
            $ret = $wpdb->get_row( $sql );
            if ( is_null($ret) || $ret === false ) {
                return array();
            }
            
            $ret = (array) $ret;
            
            // get report data - products
            $log_data = array();
            switch ( $ret['log_data_type'] ) {
                case 'serialize':
                    $log_data = !empty($ret['log_data']) ? (array) maybe_unserialize($ret['log_data']) : array();
                    break;
            }
            $ret['log_data'] = (array) $log_data;

            return (array) $ret;
        }

        private function save_current_report( $pms ) {
            global $wpdb;
            
            extract($pms);
            
            $table_name_report = $wpdb->prefix . "amz_report_log";
            {
                $log_data = serialize($log_data);
                $log_data_type = 'serialize';

                $wpdb->insert( 
                    $table_name_report, 
                    array( 
                        'log_id'            => $log_id,
                        'log_action'        => $log_action,
                        'desc'              => $desc,
                        'log_data_type'     => $log_data_type,
                        'log_data'          => $log_data,
                        //'source'            => '',
                        //'date_add'          => $date_add,
                    ), 
                    array( 
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        //'%s',
                        //'%s',
                    )
                );
                $insert_id = $wpdb->insert_id;
                return $insert_id;
            }
        }

        private function report_send_mail( $pms=array() ) {
            extract($pms);
			if ( isset($data) ) extract($data);
 			
			// NOTICE!!! HERE log_id represents <id> field from table, NOT the <log_id> field
			$log_id = $new_id;
			$this->view_in_browser = admin_url( 'admin-ajax.php?action=WooZone_report_settings&subaction=view_in_browser&log_id=' . $log_id );
			
            // send email
            add_filter('wp_mail_content_type', array($this->the_plugin, 'set_content_type'));
            //add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
            
            $email_to = isset(self::$settings["email_to{$module_}"]) ? self::$settings["email_to{$module_}"] : '';
            if ( empty($email_to) ) {
                return array(
                    'mailStat'          => false,
                    'mailFields'        => array(),
                );
            }
			
            $subject = isset(self::$settings["email_subject{$module_}"]) ? __(self::$settings["email_subject{$module_}"], $this->the_plugin->localizationName) : __('WooZone Report', $this->the_plugin->localizationName);
            
            $details = array('plugin_name' => 'WooZone');
            $from_name = __($details['plugin_name'].' Report module | ', $this->the_plugin->localizationName) . get_bloginfo('name');
            $from_email = get_bloginfo('admin_email');
            $headers = array();
            $headers[] = __('From: ', $this->the_plugin->localizationName) . $from_name . " <" . $from_email . ">";
            $headers[] = "MIME-Version: 1.0";
            
            //$html = '<p>The <em>HTML</em> message</p>';
            //$html = $this->_get_report_products_status( $data, 'email' );

            // wordpress mail function
            $sendStat = wp_mail( $email_to, $subject, $html, $headers );
			
            // reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
            remove_filter('wp_mail_content_type', array($this->the_plugin, 'set_content_type'));

            // phpmailer fallback
            if ( !$sendStat ) {
                require_once( $this->the_plugin->cfg['paths']['scripts_dir_path'] . '/PHPMailer_5.2.9/class.phpmailer.php' );
            
                $mail = new PHPMailer();
                
                $mail->SetFrom( $from_email, $from_name );
                
                $mail->AddAddress( $email_to, $email_to );
                
                // add us as BCC of reply
                $mail->AddBCC( $from_email, $from_name );
        
                $mail->Subject = $subject;
                $mail->AltBody    = __("To view the message, please use an HTML compatible email viewer!", $this->the_plugin->localizationName); // optional, comment out and test
                
                // load the header 
                $body  = $html;     
                
                // append body html to email transporter
                $mail->MsgHTML( $body );
                
                $sendStat = (bool) $mail->Send();
                
                // Clear Addresses
                $mail->ClearAddresses();
            }
            
            return array(
                'mailStat'          => $sendStat,
                'mailFields'        => compact( 'email_to', 'subject' ), //compact( 'email_to', 'subject', 'html' ),
            );
        }


        /**
         * Get Report Products Sync & Performance Status
         */
        private function get_report_products( $module='synchronization' ) {
            global $wpdb;
            
			$prod_key = '_amzASIN';

            $ret = array('status' => 'valid', 'products' => array(), 'nb' => 0, 'nbv' => 0);

            $report_last_date = (int) get_option('WooZone_report_last_date', 0);
             
            $clause = array();
            if ( $module == 'synchronization' ) {
                $clause[] = " AND ( pm.meta_key = '_amzaff_sync_last_date' AND pm.meta_value > $report_last_date ) ";
            } else if ( $module == 'performance' ) {
            }
            $clause = implode('', $clause);
            
            // get products (simple or just parents without variations)
            $sql = "SELECT p.ID, p.post_title, p.post_parent, p.post_date FROM $wpdb->posts as p LEFT JOIN $wpdb->postmeta as pm ON p.ID = pm.post_id WHERE 1=1 AND p.post_status = 'publish' AND p.post_parent = 0 AND p.post_type = 'product' %s ORDER BY p.ID ASC;";
            $sql = sprintf($sql, $clause);
            $res = $wpdb->get_results( $sql, OBJECT_K );
            
            // get product variations (only childs, no parents)
            $sql_childs = "SELECT p.ID, p.post_title, p.post_parent, p.post_date FROM $wpdb->posts as p LEFT JOIN $wpdb->postmeta as pm ON p.ID = pm.post_id WHERE 1=1 AND p.post_status = 'publish' AND p.post_parent > 0 AND p.post_type = 'product_variation' %s ORDER BY p.ID ASC;";
            $sql_childs = sprintf($sql_childs, $clause);
            $res_childs = $wpdb->get_results( $sql_childs, OBJECT_K );
            
            //var_dump('<pre>', $sql, $sql_childs, '</pre>'); die('debug...'); 
            if ( empty($res) && empty($res_childs) ) return $ret;
            
            // array with parents and their associated childrens
            $parent2child = array();
            foreach ($res_childs as $id => $val) {
                $parent = $val->post_parent;
                
                if ( !isset($parent2child["$parent"]) ) {
                    $parent2child["$parent"] = array();
                }
                $parent2child["$parent"]["$id"] = $val; 
            }
 
            // products IDs
            $prods = array_merge(array(), array_keys($res), array_keys($res_childs));
            $prods = array_unique($prods);

            // get ASINs
            $prods2asin = array();
            foreach (array_chunk($prods, self::$sql_chunk_limit, true) as $current) {

                $currentP = implode(',', array_map(array($this->the_plugin, 'prepareForInList'), $current));
                $sql_getasin = "SELECT pm.post_id, pm.meta_value FROM $wpdb->postmeta as pm WHERE 1=1 AND pm.meta_key = '$prod_key' AND pm.post_id IN ($currentP) ORDER BY pm.post_id ASC;";
                $res_getasin = $wpdb->get_results( $sql_getasin, OBJECT_K );
                $prods2asin = $prods2asin + $res_getasin; //array_replace($prods2asin, $res_getasin);
            }
            
            if ( $module == 'synchronization' ) {
                $__meta_toget = array(
                    '_amzaff_sync_last_date', '_amzaff_sync_hits', '_amzaff_sync_last_status',
                    '_amzaff_sync_hits_prev'
                );
            } else if ( $module == 'performance' ) {
                $__meta_toget = array(
                    '_amzaff_hits', '_amzaff_addtocart', '_amzaff_redirect_to_amazon',
                    '_amzaff_hits_prev', '_amzaff_addtocart_prev', '_amzaff_redirect_to_amazon_prev'
                );
            }
            // get sync last date & sync hits
            $prods2meta = array();
            //foreach ( (array) $__meta_toget as $meta) {
                //$prods2meta["$meta"] = array();

                foreach (array_chunk($prods, self::$sql_chunk_limit, true) as $current) {
    
                    $currentP = implode(',', array_map(array($this->the_plugin, 'prepareForInList'), $current));
                    $currentMeta = implode(',', array_map(array($this->the_plugin, 'prepareForInList'), $__meta_toget));
    
                    $sql_getmeta = "SELECT pm.post_id, pm.meta_key, pm.meta_value FROM $wpdb->postmeta as pm WHERE 1=1 AND pm.meta_key IN ($currentMeta) AND pm.post_id IN ($currentP) ORDER BY pm.post_id ASC;";
                    $res_getmeta = $wpdb->get_results( $sql_getmeta );
                    foreach ((array) $res_getmeta as $k => $v) {
                        $_post_id = $v->post_id;
                        $_meta_key = $v->meta_key;
                        $_meta_value = $v->meta_value;
                        $prods2meta["$_post_id"]["$_meta_key"] = $_meta_value;
                    }
                    //$prods2meta["$meta"] = $prods2meta["$meta"] + $res_getmeta; //array_replace($prods2meta["$meta"], $res_getmeta);
                }
            //}
 
            // init report
            $prods2meta = $this->report_init($prods, $prods2asin, $prods2meta);

            if ( $module == 'synchronization' ) {
                $nb_success = 0;
                $nb_error = 0;
            } else if ( $module == 'performance' ) {
                $total_nb = 0;
                $total_hits = 0;
                $total_addtocart = 0;
                $total_redirect_to_amazon = 0;
            }
  
            $default = array(
                'module'        => $module,
            );
            $nbprod = 0;
            $nbprodv = 0;
            foreach ($res as $id => $val) {
  
                // exclude products without ASIN
                if ( !isset($prods2asin["$id"]) ) continue 1;

                // product meta is invalid
                if ( !$this->is_valid_prod($module, $id, $prods2meta) ) continue 1;

                $ret['products']["$id"] = $this->row_build_report(array_merge($default, array(
                    'id'            => $id,
                    'val'           => $val,
                    'prods2asin'    => $prods2asin,
                    'prods2meta'    => $prods2meta,
                )));
                if ( $module == 'synchronization' ) {
                    if ( $ret['products']["$id"]['sync_last_status'] ) $nb_success++;
                    else $nb_error++;
                } else if ( $module == 'performance' ) {
                    $total_nb++;
                    $total_hits += $ret['products']["$id"]['hits'];
                    $total_addtocart += $ret['products']["$id"]['addtocart'];
                    $total_redirect_to_amazon += $ret['products']["$id"]['redirect_to_amazon'];
                }

                if ( isset($parent2child["$id"]) ) {
                    $childs = $parent2child["$id"];
                    $childs_nb = count($childs);
                    $cc = 0;
                    foreach ($childs as $childId => $childVal) {
                        // exclude products without ASIN
                        if ( !isset($prods2asin["$childId"]) ) continue 1;
        
                        // product meta is invalid
                        if ( !$this->is_valid_prod($module, $childId, $prods2meta) ) continue 1;
 
                        $ret['products']["$childId"] = $this->row_build_report(array_merge($default, array(
                            'id'            => $childId,
                            'val'           => $childVal,
                            'prods2asin'    => $prods2asin,
                            'prods2meta'    => $prods2meta,
                        )));
                        if ( $module == 'synchronization' ) {
                            if ( $ret['products']["$childId"]['sync_last_status'] ) $nb_success++;
                            else $nb_error++;
                        } else if ( $module == 'performance' ) {
                            $total_nb++;
                            $total_hits += $ret['products']["$childId"]['hits'];
                            $total_addtocart += $ret['products']["$childId"]['addtocart'];
                            $total_redirect_to_amazon += $ret['products']["$childId"]['redirect_to_amazon'];
                        }

                        $cc++;
                    }
                    
                    $nbprodv += $cc;
                } // end product variations loop
                
                $nbprod++;
            } // end products loop
            
            // no products found!
            if ( empty($ret['products']) ) return $ret;
 
            $ret = array_merge($ret, array(
                'nb'        => $nbprod,
                'nbv'       => $nbprodv,
            ));
            if ( $module == 'synchronization' ) {
                $ret = array_merge($ret, array(
                    'nb_success'        => $nb_success,
                    'nb_error'          => $nb_error,
                ));
            } else if ( $module == 'performance' ) {
                if ( !empty($ret['products']) ) {
                    $ret['products'] = $this->sort_hight_to_low( $ret['products'], 'score' );
                }

                $ret = array_merge($ret, array(
                    'total_nb'                      => $total_nb,
                    'total_hits'                    => $total_hits,
                    'total_addtocart'               => $total_addtocart,
                    'total_redirect_to_amazon'      => $total_redirect_to_amazon,
                ));
            }
            //var_dump('<pre>', $ret, '</pre>'); die('debug...'); 
            return $ret;
        }

        private function report_init($prods, $prods2asin, $prods2meta) {
            $is_first = (int) get_option('WooZone_report_first_time', 0);
            $is_first = !empty($is_first) ? false : true;
            
            if (!$is_first || empty($prods)) return $prods2meta;
 
            $metas = array('_amzaff_sync_hits_prev', '_amzaff_hits_prev', '_amzaff_addtocart_prev', '_amzaff_redirect_to_amazon_prev');
            foreach ($prods as $id) {
  
                // exclude products without ASIN
                if ( !isset($prods2asin["$id"]) ) continue 1;
                
                foreach ($metas as $meta) {
                    $_meta = str_replace('_prev', '', $meta);
 
                    if ( isset($prods2meta["$id"], $prods2meta["$id"]["$_meta"]) ) {
                        update_post_meta($id, $meta, (int) $prods2meta["$id"]["$_meta"]);
                        $prods2meta["$id"]["$meta"] = (int) $prods2meta["$id"]["$_meta"];
                    }
                }
            } // end foreach
            return $prods2meta;
        }

        private function is_valid_prod($module, $id, $prods2meta) {
            {
                if ( $module == 'synchronization' ) {
                    // debug...
                    //update_post_meta($id, '_amzaff_sync_hits_prev', (int) get_post_meta($id, '_amzaff_sync_hits', true));
                    
                    if ( !isset($prods2meta["$id"], $prods2meta["$id"]['_amzaff_sync_hits_prev'])
                        || empty($prods2meta["$id"]['_amzaff_sync_hits_prev']) ) {
                        if ( !isset($prods2meta["$id"], $prods2meta["$id"]['_amzaff_sync_hits_prev']) ) {
                            update_post_meta($id, '_amzaff_sync_hits_prev', 0);
                        }
                        return false;
                    }
                    return true;
                } else if ( $module == 'performance' ) {
                    // debug...
                    //update_post_meta($id, '_amzaff_hits_prev', (int) get_post_meta($id, '_amzaff_hits', true));
                    //update_post_meta($id, '_amzaff_addtocart_prev', (int) get_post_meta($id, '_amzaff_addtocart', true));
                    //update_post_meta($id, '_amzaff_redirect_to_amazon_prev', (int) get_post_meta($id, '_amzaff_redirect_to_amazon', true));

                    $has_hits = true;
                    if ( !isset($prods2meta["$id"], $prods2meta["$id"]['_amzaff_hits_prev'])
                        || empty($prods2meta["$id"]['_amzaff_hits_prev']) ) {
                        if ( !isset($prods2meta["$id"], $prods2meta["$id"]['_amzaff_hits_prev']) ) {
                            update_post_meta($id, '_amzaff_hits_prev', 0);
                        }
                        $has_hits = false;
                    }
                    $has_addtocart = true;
                    if ( !isset($prods2meta["$id"], $prods2meta["$id"]['_amzaff_addtocart_prev'])
                        || empty($prods2meta["$id"]['_amzaff_addtocart_prev']) ) {
                        if ( !isset($prods2meta["$id"], $prods2meta["$id"]['_amzaff_addtocart_prev']) ) {
                            update_post_meta($id, '_amzaff_addtocart_prev', 0);
                        }
                        $has_addtocart = false;
                    }
                    $has_redirect_to_amazon = true;
                    if ( !isset($prods2meta["$id"], $prods2meta["$id"]['_amzaff_redirect_to_amazon_prev'])
                        || empty($prods2meta["$id"]['_amzaff_redirect_to_amazon_prev']) ) {
                        if ( !isset($prods2meta["$id"], $prods2meta["$id"]['_amzaff_redirect_to_amazon_prev']) ) {
                            update_post_meta($id, '_amzaff_redirect_to_amazon_prev', 0);
                        }
                        $has_redirect_to_amazon = false;
                    }
                    $has = $has_hits || $has_addtocart || $has_redirect_to_amazon;
                    return $has;
                }
                return false;
            }
        }

        private function row_build_report( $pms ) {
            extract($pms);

            $title = $val->post_title;
            $asin = isset($prods2asin["$id"]) ? $prods2asin["$id"]->meta_value : 0;
            
            $post_date = $val->post_date;
            $post_parent = $val->post_parent;
            
            if ( $module == 'synchronization' ) {

                $sync_hits = isset($prods2meta["$id"], $prods2meta["$id"]['_amzaff_sync_hits_prev']) ? $prods2meta["$id"]['_amzaff_sync_hits_prev'] : 0;

                $sync_last_date = isset($prods2meta["$id"], $prods2meta["$id"]['_amzaff_sync_last_date']) ? $prods2meta["$id"]['_amzaff_sync_last_date'] : '';

                $sync_last_status = isset($prods2meta["$id"], $prods2meta["$id"]['_amzaff_sync_last_status']) ? $prods2meta["$id"]['_amzaff_sync_last_status'] : 0;

                $ret = compact('id', 'title', 'asin', 'post_date', 'post_parent', 'sync_hits', 'sync_last_date', 'sync_last_status');
            } else if ( $module == 'performance' ) {

                $hits = isset($prods2meta["$id"], $prods2meta["$id"]['_amzaff_hits_prev']) ? $prods2meta["$id"]['_amzaff_hits_prev'] : 0;

                $addtocart = isset($prods2meta["$id"], $prods2meta["$id"]['_amzaff_addtocart_prev']) ? $prods2meta["$id"]['_amzaff_addtocart_prev'] : 0;

                $redirect_to_amazon = isset($prods2meta["$id"], $prods2meta["$id"]['_amzaff_redirect_to_amazon_prev']) ? $prods2meta["$id"]['_amzaff_redirect_to_amazon_prev'] : 0;
                
                $score = ($redirect_to_amazon * 3) + ($addtocart * 2) + ($hits * 1);

                $ret = compact('id', 'title', 'asin', 'post_date', 'post_parent', 'hits', 'addtocart', 'redirect_to_amazon', 'score');
            }
            unset($ret['title']);
            return $ret;
        }
        
        private function set_report_products_meta_prev() {
            global $wpdb;
            
            $__meta_toget = array(
                '_amzaff_hits_prev', '_amzaff_addtocart_prev', '_amzaff_redirect_to_amazon_prev',
                '_amzaff_sync_hits_prev'
            );
            
            $currentMeta = implode(',', array_map(array($this->the_plugin, 'prepareForInList'), $__meta_toget));

            $sql = "UPDATE $wpdb->postmeta as pm SET pm.meta_value = '0' WHERE 1=1 AND pm.meta_key IN ($currentMeta);";
            return $wpdb->query( $sql );
        }
        
        private function build_current_report() {
            $now = self::$current_time;

            $ret = array(
                'log_id'            => 'report',
                'log_action'        => 'products_status',
                'desc'              => 'report products synchronization and performance status',
                'date_add'          => $now,
            );
            
            // get report data - products
            $ret['log_data'] = array();
            $ret['log_data']['synchronization'] = (array) $this->get_report_products('synchronization');
            $ret['log_data']['performance'] = (array) $this->get_report_products('performance');
 
            // set old meta for report data - products
            $this->set_report_products_meta_prev();
            
            // update last report date
            update_option('WooZone_report_last_date', $now);
            update_option('WooZone_report_first_time', $now);
            
            // save report
            $ret['new_id'] = $this->save_current_report( $ret );
            
            return $ret;
        }
        
        private function _get_report_products_status( $data, $view_type ) {
            extract($data);
			
            // get the email template
            /*ob_start();
            require_once( $this->module_folder_path . 'tpl/products_status/index.html' );
            $html = ob_get_contents();
            ob_end_clean();*/

            $lang = array(
                'no_products'       => __('no products', $this->localizationName),
            );
			
            $parts = array(
                'header'                    => file_get_contents( $this->module_folder_path . 'tpl/products_status/parts_header.html' ),
                'content'                   => file_get_contents( $this->module_folder_path . 'tpl/products_status/parts_content.html' ),
                'content_synchronization'   => file_get_contents( $this->module_folder_path . 'tpl/products_status/' . ( $this->device ) . 'parts_content_synchronization.html' ),
                'content_performance'       => file_get_contents( $this->module_folder_path . 'tpl/products_status/' . ( $this->device ) . 'parts_content_performance.html' ),
            );

            if ( $view_type == 'email' ) {
                $html = file_get_contents( $this->module_folder_path . 'tpl/products_status/index.html' );
                $html = str_replace("{{__parts_header__}}", $parts['header'], $html);
                $html = str_replace("{{__parts_content__}}", $parts['content'], $html);

            } else if ( $view_type == 'view_log' ) {
                $html = $parts['header'] . "\n" . $parts['content'];
            }
             
            $resContent = $this->_products_status_content($data, $view_type);

            // synchronization
            $has_prods_sync = false;
            if ( isset($log_data['synchronization'], $log_data['synchronization']['products'])
                && !empty($log_data['synchronization']) && !empty($log_data['synchronization']['products']) ) {
                $has_prods_sync = true;
                $html = str_replace("{{__parts_content_synchronization__}}", $parts['content_synchronization'], $html);                
            } else {
                $html = str_replace("{{__parts_content_synchronization__}}", "<tr><td style='text-align: center;'>{$lang['no_products']}</td></tr>", $html);
            }

            $html = str_replace("{{sync_title}}", __('WooZone Synchronisation Status', $this->localizationName), $html);
            if ( $has_prods_sync ) {
            $html = str_replace("{{sync_success_text}}", __('Successfully synchronised :', $this->localizationName), $html);
            $html = str_replace("{{sync_success_nb}}", sprintf( __('%s products', $this->localizationName), $resContent['nb_success'] ), $html);
            $html = str_replace("{{sync_error_text}}", __('Errors occured :', $this->localizationName), $html);
            $html = str_replace("{{sync_error_nb}}", sprintf( __('%s products', $this->localizationName), $resContent['nb_error'] ), $html);

            $html = str_replace("{{sync_table_head}}", $resContent['sync_head'], $html);
            $html = str_replace("{{sync_table_body}}", $resContent['sync_body'], $html);
            }

            // performance
            $has_prods_perf = false;
            if ( isset($log_data['performance'], $log_data['performance']['products'])
                && !empty($log_data['performance']) && !empty($log_data['performance']['products']) ) {
                $has_prods_perf = true;
                $html = str_replace("{{__parts_content_performance__}}", $parts['content_performance'], $html);                
            } else {
                $html = str_replace("{{__parts_content_performance__}}", "<tr><td style='text-align: center;'>{$lang['no_products']}</td></tr>", $html);
            }

            $html = str_replace("{{perf_title}}", __('WooZone Performance', $this->localizationName), $html);
            if ( $has_prods_perf ) {
	            $html = str_replace("{{perf_total_nb}}", sprintf( __('<span>%s</span> <span>total</span>', $this->localizationName), $resContent['total_nb'] ), $html); //  <span>products</span>
	            $html = str_replace("{{perf_total_nb_text}}", __('<span>Number of products</span>', $this->localizationName), $html);
	            $html = str_replace("{{perf_total_views}}", sprintf( __('<span>%s</span> <span>total</span>', $this->localizationName), $resContent['total_hits'] ), $html);
	            $html = str_replace("{{perf_total_views_text}}", __('<span>Views</span>', $this->localizationName), $html);
	            $html = str_replace("{{perf_total_addtocart}}", sprintf( __('<span>%s</span> <span>total</span>', $this->localizationName), $resContent['total_addtocart'] ), $html);
	            $html = str_replace("{{perf_total_addtocart_text}}", __('<span>Added to cart</span>', $this->localizationName), $html);
	            $html = str_replace("{{perf_total_redtoamz}}", sprintf( __('<span>%s</span> <span>total</span>', $this->localizationName), $resContent['total_redirect_to_amazon'] ), $html);
	            $html = str_replace("{{perf_total_redtoamz_text}}", __('<span>Redirected to Amazon</span>', $this->localizationName), $html);
	
	            $html = str_replace("{{perf_table_head}}", $resContent['perf_head'], $html);
	            $html = str_replace("{{perf_table_body}}", $resContent['perf_body'], $html);
            }

            // header & general
            $date_add = $this->the_plugin->last_update_date('true', strtotime($date_add), true);
            $title = sprintf( __('WooZone Report - %s', $this->localizationName), $date_add );
            $html = str_replace("{{title}}", $title, $html);
            $html = str_replace("{{images_base_url_gen}}", $this->module_folder . 'tpl/', $html);
            $html = str_replace("{{images_base_url}}", $this->module_folder . 'tpl/products_status/', $html);

            // footer
            $html = str_replace("{{content_notice}}", __('<span>It contains all products status from the time of the last report.</span>', $this->localizationName), $html);
            $html = str_replace("{{aateam_notice}}", __('© AA-Team, 2016 <br />You are receiving this email because<br /> you\'re an awesome customer of AA-Team.', $this->localizationName), $html);

            return $html;
        }
        
        private function _products_status_content( $data, $view_type ) {
            extract($data);
 
            $s = isset($log_data['synchronization']) ? $log_data['synchronization'] : array();
            $p = isset($log_data['performance']) ? $log_data['performance'] : array();
            $limit = $this->device == 'email_' ? 5 : 0;
			
            // synchronize & performance header
            $sync_head = '<tr>
                <th style="width:35%;">' . __('Product (ASIN / ID)', $this->localizationName) . '</th>
                <th>' . __('Syncs number', $this->localizationName) . '</th>
                <th>' . __('Sync last status', $this->localizationName) . '</th>
                <th>' . __('Sync last date', $this->localizationName) . '</th>
            </tr>';

            $perf_head = '<tr>
                <th style="width:35%;">' . __('Product (ASIN / ID)', $this->localizationName) . '</th>
                <th>' . __('Views', $this->localizationName) . '</th>
                <th>' . __('Added to cart', $this->localizationName) . '</th>
                <th>' . __('Redirect to Amazon', $this->localizationName) . '</th>
            </tr>';

            // synchronize & performance body content
            $sync_body = array();
            $cc = 0;
            foreach ( (array) $s['products'] as $key => $val ) {
				if( $limit != 0 && $cc >= $limit ){
            		break; //continue; //fixed 2016-02-22
            	}
                $link_edit = sprintf( admin_url('post.php?post=%s&action=edit'), $val['id']);
                $is_child = $val['post_parent'] > 0 ? true : false;

                $sync_hits = sprintf( __('%s Syncs', $this->localizationName), $val['sync_hits'] );
                $sync_last_date = $this->the_plugin->last_update_date('true', $val['sync_last_date']);
                $sync_last_status = $val['sync_last_status'] ? __('Success', $this->localizationName) : __('Error', $this->localizationName);
                $sync_last_status_css = $val['sync_last_status'] ? 'success' : 'error';

                $sync_body[] = '<tr>
                    <td style="' . ($is_child ? 'padding-left: 20px;' : '') . '">
                        <a href="' . $link_edit . '" target="_blank" style="color: #b3b3b3;">' . $val['asin'] . '</a> / <a href="' . $link_edit . '" target="_blank" style="color: #b3b3b3;">#' . $val['id'] . '</a>
                    </td>
                    <td>' . $sync_hits . '</td>
                    <td><span class="' . $sync_last_status_css . '">' . $sync_last_status . '</span></td>
                    <td>' . $sync_last_date . '</td>
                </tr>';
                $cc++;
            }
			if( $limit != 0 ){
				$sync_body[] = '<tr>
                    <td colspan="5"><a href="' . ( $this->view_in_browser ) . '" style="background:#bdc3c7;padding: 2px 10px 2px 10px;color: #fff;text-decoration: none;border-radius: 4px;">View all statistics on Web Browser</a></td>
                </tr>';
			}
            $sync_body = implode("\n", $sync_body);

            $perf_body = array();
            $cc = 0;
            foreach ( (array) $p['products'] as $key => $val ) {
            	if( $limit != 0 && $cc >= $limit ){
            		break; //continue; //fixed 2016-02-22
            	}
                $link_edit = sprintf( admin_url('post.php?post=%s&action=edit'), $val['id']);
                $is_child = $val['post_parent'] > 0 ? true : false;

                $perf_body[] = '<tr>
                    <td style="' . ($is_child ? 'padding-left: 20px;' : '') . '">
                        <span style="width: 45px; height: 20px; position: relative;"><span style="background: #5A1977;width: 34px;height: 22px;line-height: 22px;border-radius: 5px;font-weight: bold;color: #fff;text-align: center;margin-top: -10px;vertical-align: center; padding: 2px 5px 2px 5px;margin-right: 5px;">#' . ($cc+1) . '</span></span>
                        <a href="' . $link_edit . '" target="_blank" style="color: #b3b3b3;">' . $val['asin'] . '</a> / <a href="' . $link_edit . '" target="_blank" style="color: #b3b3b3;">#' . $val['id'] . '</a>
                    </td>
                    <td><i style="padding: 2px 8px 2px 8px;border-radius: 4px;background: #f39c12;color: #fff;" original-title="">' . $val['hits'] . '</i></td>
                    <td><i style="padding: 2px 8px 2px 8px;border-radius: 4px;background: #1abc9c;color: #fff;" original-title="">' . $val['addtocart'] . '</i></td>
                    <td><i style="padding: 2px 8px 2px 8px;border-radius: 4px;background: #3498db;color: #fff;" original-title="">' . $val['redirect_to_amazon'] . '</i></td>
                </tr>';
                $cc++;
            }

			if( $limit != 0 ){
				$perf_body[] = '<tr>
                    <td colspan="5"><a href="' . ( $this->view_in_browser ) . '" style="background:#bdc3c7;padding: 2px 10px 2px 10px;color: #fff;text-decoration: none;border-radius: 4px;">View all statistics on Web Browser</a></td>
                </tr>';
			}
            $perf_body = implode("\n", $perf_body);

            $ret = array(
                // synchronization
                'nb_success'                    => isset($s['nb_success']) ? (int) $s['nb_success'] : 0,
                'nb_error'                      => isset($s['nb_error']) ? (int) $s['nb_error'] : 0,
                'sync_head'                     => $sync_head,
                'sync_body'                     => $sync_body,

                // performance
                'total_nb'                      => isset($p['total_nb']) ? (int) $p['total_nb'] : 0,
                'total_hits'                    => isset($p['total_hits']) ? (int) $p['total_hits'] : 0,
                'total_addtocart'               => isset($p['total_addtocart']) ? (int) $p['total_addtocart'] : 0,
                'total_redirect_to_amazon'      => isset($p['total_redirect_to_amazon']) ? (int) $p['total_redirect_to_amazon'] : 0,
                'perf_head'                     => $perf_head,
                'perf_body'                     => $perf_body,
            );
 
            return $ret;
        }


        /**
         * Get Report Auto Import Stats
         */
        private function ai_get_report_products( $module='auto_import' ) {
            global $wpdb;
			
            $ret = array('status' => 'valid', 'products' => array(), 'nb' => 0, 'nb_elem' => 0);
			$nb_elem = array('new' => 0, 'already' => 0, 'done' => 0, 'error' => 0);
			
			$table = $wpdb->prefix  . 'amz_queue';

            $report_last_date = (int) get_option('WooZone_ai_report_last_date', 0);
             
            $clause = array();
            $clause[] = " AND ( a.imported_date > $report_last_date ) ";
            $clause = implode('', $clause);
            
            // get products (simple or just parents without variations)
            $sql = "SELECT a.id, a.asin, a.status, a.from_op, a.imported_date, a.nb_tries, a.nb_tries_prev FROM $table as a WHERE 1=1 %s ORDER BY a.from_op ASC, a.id ASC;";
            $sql = sprintf($sql, $clause);
            $res = $wpdb->get_results( $sql, OBJECT_K );
			if ( empty($res) ) return $ret;
			
			// the number of new asins in queue remained
			$sql_new = "SELECT count(a.id) as nb FROM " . $table . " as a WHERE 1=1 AND a.status = 'new' AND a.nb_tries < %s;";
			$sql_new = $wpdb->prepare( $sql_new, self::$max_nb_tries );
			$res_new = $wpdb->get_var( $sql_new );
			$nb_elem['new'] = (int) $res_new;

			$_asins = array();
			foreach ($res as $key => $val) {
				$_asins[] = $val->asin;
			}
			$_asins = array_unique( array_filter( $_asins ) );
			//$_asins = implode(',', array_map(array($this->the_plugin, 'prepareForInList'), $_asins));
			
			//$asins_post = array();
			$asins_post = WooZone_product_by_asin( $_asins );
            
            // init report
            //$res = $this->report_init($res);

            $default = array(
                'module'        => $module,
            );
            $nbprod = 0;
            foreach ($res as $id => $val) {

                // product meta is invalid
                if ( !$this->ai_is_valid_prod($module, $val) ) continue 1;

                $ret['products']["$id"] = $this->ai_row_build_report(array_merge($default, array(
                    'id'            => $id,
                    'val'           => $val,
                    'asins_post'    => $asins_post,
                )));
				$status = $ret['products']["$id"]['status'];
				if ( !empty($status) && isset($nb_elem["$status"]) ) {
					$nb_elem["$status"]++;
				}

                $nbprod++;
            } // end products loop

            // no products found!
            if ( empty($ret['products']) ) return $ret;
 
            $ret = array_merge($ret, array(
                'nb'        => $nbprod,
                'nb_elem'	=> $nb_elem,
            ));
            //var_dump('<pre>', $ret, '</pre>'); die('debug...'); 
            return $ret;
        }

        private function ai_is_valid_prod($module, $prod) {
			if ( empty($prod->nb_tries_prev) ) {
				return false;
			}
			return true;
        }

        private function ai_row_build_report( $pms ) {
            extract($pms);
  
			$asin = $val->asin;
			$imported_date = $val->imported_date;
			$status = $val->status;
			$status_html = isset($this->ai_status_values["$status"]) ? $this->ai_status_values["$status"] : '';
			$from_op = $val->from_op;
			$nb_tries = $val->nb_tries_prev;
			$post_id = isset($asins_post["$asin"]) ? $asins_post["$asin"]->ID : 0;
            
			$ret = compact('id', 'asin', 'imported_date', 'status', 'status_html', 'from_op', 'nb_tries', 'post_id');
            return $ret;
        }

        private function ai_set_report_products_meta_prev() {
            global $wpdb;
			
			$table = $wpdb->prefix  . 'amz_queue';

            $report_last_date = (int) get_option('WooZone_ai_report_last_date', 0);
             
            $clause = array();
            $clause[] = " AND ( a.imported_date > $report_last_date ) ";
            $clause = implode('', $clause);
            
            // get products (simple or just parents without variations)
            $sql = "UPDATE $table as a SET a.nb_tries_prev = '0' WHERE 1=1 %s;";
            $sql = sprintf($sql, $clause);
            return $wpdb->query( $sql );
        }
        
        private function ai_build_current_report() {
            $now = self::$current_time;

            $ret = array(
                'log_id'            => 'report',
                'log_action'        => 'auto_import',
                'desc'              => 'report auto import products from queue status',
                'date_add'          => $now,
            );
            
            // get report data - products
            $ret['log_data'] = array();
            $ret['log_data']['auto_import'] = (array) $this->ai_get_report_products('auto_import');
 
            // set old meta for report data - products
            $this->ai_set_report_products_meta_prev();
            
            // update last report date
            update_option('WooZone_ai_report_last_date', $now);
            
            // save report
            $ret['new_id'] = $this->save_current_report( $ret );
            
            return $ret;
        }

        private function _ai_get_report_products_status( $data, $view_type ) {
            extract($data);
			
            // get the email template
            /*ob_start();
            require_once( $this->module_folder_path . 'tpl/products_status/index.html' );
            $html = ob_get_contents();
            ob_end_clean();*/

            $lang = array(
                'no_products'       => __('no products', $this->localizationName),
            );
			
            $parts = array(
                'header'                    => file_get_contents( $this->module_folder_path . 'tpl/auto_import/parts_header.html' ),
                'content'                   => file_get_contents( $this->module_folder_path . 'tpl/auto_import/parts_content.html' ),
                'content_auto_import'   => file_get_contents( $this->module_folder_path . 'tpl/auto_import/' . ( $this->device ) . 'parts_content_auto_import.html' ),
            );

            if ( $view_type == 'email' ) {
                $html = file_get_contents( $this->module_folder_path . 'tpl/auto_import/index.html' );
                $html = str_replace("{{__parts_header__}}", $parts['header'], $html);
                $html = str_replace("{{__parts_content__}}", $parts['content'], $html);

            } else if ( $view_type == 'view_log' ) {
                $html = $parts['header'] . "\n" . $parts['content'];
            }
             
            $resContent = $this->_ai_products_status_content($data, $view_type);

            // synchronization
            $has_prods_sync = false;
            if ( isset($log_data['auto_import'], $log_data['auto_import']['products'])
                && !empty($log_data['auto_import']) && !empty($log_data['auto_import']['products']) ) {
                $has_prods_sync = true;
                $html = str_replace("{{__parts_content_auto_import__}}", $parts['content_auto_import'], $html);                
            } else {
                $html = str_replace("{{__parts_content_auto_import__}}", "<tr><td style='text-align: center;'>{$lang['no_products']}</td></tr>", $html);
            }

            $html = str_replace("{{sync_title}}", __('WooZone Auto Import Status', $this->localizationName), $html);
            if ( $has_prods_sync ) {
            $html = str_replace("{{success_text}}", __('Successfully imported :', $this->localizationName), $html);
            $html = str_replace("{{success_nb}}", sprintf( __('%s products', $this->localizationName), $resContent['nb_success'] ), $html);
            $html = str_replace("{{error_text}}", __('Errors occured :', $this->localizationName), $html);
            $html = str_replace("{{error_nb}}", sprintf( __('%s products', $this->localizationName), $resContent['nb_error'] ), $html);
			$html = str_replace("{{already_text}}", __('Already imported :', $this->localizationName), $html);
            $html = str_replace("{{already_nb}}", sprintf( __('%s products', $this->localizationName), $resContent['nb_already'] ), $html);
            $html = str_replace("{{new_text}}", __('New remained :', $this->localizationName), $html);
            $html = str_replace("{{new_nb}}", sprintf( __('%s products', $this->localizationName), $resContent['nb_new'] ), $html);

            $html = str_replace("{{sync_table_head}}", $resContent['sync_head'], $html);
            $html = str_replace("{{sync_table_body}}", $resContent['sync_body'], $html);
            }

            // header & general
            $date_add = $this->the_plugin->last_update_date('true', strtotime($date_add), true);
            $title = sprintf( __('WooZone Report AI - %s', $this->localizationName), $date_add );
            $html = str_replace("{{title}}", $title, $html);
            $html = str_replace("{{images_base_url_gen}}", $this->module_folder . 'tpl/', $html);
            $html = str_replace("{{images_base_url}}", $this->module_folder . 'tpl/auto_import/', $html);

            // footer
            $html = str_replace("{{content_notice}}", __('<span>It contains all auto imported products from the time of the last report.</span>', $this->localizationName), $html);
            $html = str_replace("{{aateam_notice}}", __('© AA-Team, 2016 <br />You are receiving this email because<br /> you\'re an awesome customer of AA-Team.', $this->localizationName), $html);

            return $html;
        }
        
        private function _ai_products_status_content( $data, $view_type ) {
            extract($data);
 
            $s = isset($log_data['auto_import']) ? $log_data['auto_import'] : array();
            $limit = $this->device == 'email_' ? 5 : 0;
			
            // synchronize & performance header
            $sync_head = '<tr>
                <th style="width:28%;">' . __('Product (ASIN / ID)', $this->localizationName) . '</th>
                <th>' . __('Nb tries', $this->localizationName) . '</th>
                <th style="width:10%;">' . __('Import last status', $this->localizationName) . '</th>
                <th>' . __('Import last date', $this->localizationName) . '</th>
                <th>' . __('From', $this->localizationName) . '</th>
            </tr>';

            // synchronize & performance body content
            $sync_body = array();
            $cc = 0;
            foreach ( (array) $s['products'] as $key => $val ) {
				if( $limit != 0 && $cc >= $limit ){
            		break; //continue; //fixed 2016-02-22
            	}

				$asin = $val['asin'];
				$post_html = '';
				if ( !empty($val['post_id']) ) {
                	$link_edit = sprintf( admin_url('post.php?post=%s&action=edit'), $val['post_id']);
					$post_html = ' / <a href="' . $link_edit . '" target="_blank" style="color: #b3b3b3;">#' . $val['post_id'] . '</a>';
                }
                $is_child = false;

                $nb_tries = sprintf( __('%s Tries', $this->localizationName), $val['nb_tries'] );
                $imported_date = $this->the_plugin->last_update_date('true', strtotime($val['imported_date']));
                $status_html = $val['status_html'];
				$status_css = 'done' == $val['status'] ? 'success' : $val['status'];
				$from_op = $val['from_op'];

                $sync_body[] = '<tr>
                    <td style="' . ($is_child ? 'padding-left: 20px;' : '') . '">
                        <span style="color: #b3b3b3;">' . $asin . '</span>' . $post_html . '
                    </td>
                    <td>' . $nb_tries . '</td>
                    <td><span class="' . $status_css . '">' . $status_html . '</span></td>
                    <td>' . $imported_date . '</td>
                    <td>' . $from_op . '</td>
                </tr>';
                $cc++;
            }
			if( $limit != 0 ){
				$sync_body[] = '<tr>
                    <td colspan="5"><a href="' . ( $this->view_in_browser ) . '" style="background:#bdc3c7;padding: 2px 10px 2px 10px;color: #fff;text-decoration: none;border-radius: 4px;">View all statistics on Web Browser</a></td>
                </tr>';
			}
            $sync_body = implode("\n", $sync_body);

            $ret = array(
                // synchronization
                'nb_success'                    => isset($s['nb_elem']['done']) ? (int) $s['nb_elem']['done'] : 0,
                'nb_error'                      => isset($s['nb_elem']['error']) ? (int) $s['nb_elem']['error'] : 0,
                'nb_already'                    => isset($s['nb_elem']['already']) ? (int) $s['nb_elem']['already'] : 0,
                'nb_new'                      	=> isset($s['nb_elem']['new']) ? (int) $s['nb_elem']['new'] : 0,
                'sync_head'                     => $sync_head,
                'sync_body'                     => $sync_body,
            );
 
            return $ret;
        }


        /**
		 * Cronjobs
		 */
        public function cronjob( $pms, $return='die' ) {
            $ret = array('status' => 'failed');
            
            $current_cron_status = $pms['status']; //'new'; //
            $now = self::$current_time;
           
		   	//'report|products_status'
			{
				$module_ = '';
            	$now = time();
	            $recurrence = isset(self::$settings["recurrency{$module_}"]) ? (int) self::$settings["recurrency{$module_}"] : 12;
	            $recurrence = (int) ( $recurrence * 3600 );
	            $report_last_date = (int) get_option('WooZone_report_last_date', 0);
				//$diff  = (string)(( $report_last_date + $recurrence ) - $now);
				//var_dump('<pre>', $module_, $now, $recurrence, $report_last_date, $diff, (string) ($recurrence - $diff), '</pre>'); die('debug...'); 
	            
	            // recurrence interval fulfilled
	            if ( /*1 || */$now >= ( $report_last_date + $recurrence ) ) {
	                
	                // assurance verification: reset in any case after more than 3 times the current setted recurrence interval
	                //$do_reset = $now >= ( $report_last_date + $recurrence * 3 ) ? true : false;
	                
	                $report_data = $this->build_current_report();
					$this->view_in_browser = admin_url( 'admin-ajax.php?action=WooZone_report_settings&subaction=view_in_browser&log_id=' . $report_data['new_id'] );
	                $this->report_send_mail(array(
	                	'module_'	=> $module_,
	                	'data'		=> $report_data,
	                	'html'		=> $this->_get_report_products_status( $report_data, 'email' ),
					));
	            }
			}
   
			//'report|auto_import'
			{
				$module_ = '_ai';
				$now = time();
	            $recurrence = isset(self::$settings["recurrency{$module_}"]) ? (int) self::$settings["recurrency{$module_}"] : 12;
	            $recurrence = (int) ( $recurrence * 3600 );
				$report_last_date = (int) get_option('WooZone_ai_report_last_date', 0);
				//$diff  = (string)(( $report_last_date + $recurrence ) - $now);
				//var_dump('<pre>', $module_, $now, $recurrence, $report_last_date, $diff, (string) ($recurrence - $diff), '</pre>'); die('debug...');
				
	            // recurrence interval fulfilled
	            if ( /*1 || */$now >= ( $report_last_date + $recurrence ) ) {
	                
	                // assurance verification: reset in any case after more than 3 times the current setted recurrence interval
	                //$do_reset = $now >= ( $report_last_date + $recurrence * 3 ) ? true : false;
	                
	                $report_data = $this->ai_build_current_report();
					$this->view_in_browser = admin_url( 'admin-ajax.php?action=WooZone_report_settings&subaction=view_in_browser&log_id=' . $report_data['new_id'] );
	                $this->report_send_mail(array(
	                	'module_'	=> $module_,
	                	'data'		=> $report_data,
	                	'html'		=> $this->_ai_get_report_products_status( $report_data, 'email' ),
					));
	            }
			}

            $ret = array_merge($ret, array(
                'status'            => 'done',
            ));
            return $ret;
        }
         

        /**
         * Ajax requests
         */
		public function ajax_request_settings()
        {
            //global $wpdb;
            $request = array(
                'action'                        => isset($_REQUEST['subaction']) ? $_REQUEST['subaction'] : '',
                'module'                        => isset($_REQUEST['module']) ? $_REQUEST['module'] : '',
                'module_'                  		=> isset($_REQUEST['module_']) ? $_REQUEST['module_'] : '',
            );
            extract($request);
            
            $ret = array(
                'status'            => 'invalid',
                'current_date'      => date('Y-m-d H:i:s'),
                'html'              => '<span class="error">' . __('Invalid action!', $this->the_plugin->localizationName) . '</span>',
            );
            
            if ( empty($action) || !in_array($action, array('getStatus', 'send_report', 'view_in_browser')) ) {
                die(json_encode($ret));
            }
    
            if ( $action == 'getStatus' ) {
                
                $notifyStatus = get_option( sprintf( self::$report_alias_act, $module_ ), array() );
                if ( $notifyStatus === false || !isset($notifyStatus["report"]) ) {
                    $ret = array_merge($ret, array(
                        'html'      => '<span class="error">' . __('No status saved yet from Send Report Now!', $this->the_plugin->localizationName) . '</span>',
                    ));
                } else {
                    $ret = array_merge($ret, array(
                        'status'    => 'valid',
                        'html'      => $notifyStatus["report"]["html"],
                    ));
                }
                die(json_encode($ret));
			
			} else if ( $action == 'view_in_browser' ) {
				
				// NOTICE!!! HERE log_id represents <id> field from table, NOT the <log_id> field
				$log_id = isset($_REQUEST['log_id']) ? $_REQUEST['log_id'] : 0;
				$this->view_in_browser = admin_url( 'admin-ajax.php?action=WooZone_report_settings&subaction=view_in_browser&log_id=' . $log_id );
				
				$row_data = (array) $this->get_log_data( $log_id );
				
				// here we use the real <log_id> field from row
				$log_code = "{$row_data['log_id']}|{$row_data['log_action']}";
				if ( 'report|products_status' == $log_code ) {
					$html = $this->_get_report_products_status( $row_data, 'email' );
				}
				if ( 'report|auto_import' == $log_code ) {
					$html = $this->_ai_get_report_products_status( $row_data, 'email' );
				}
				die( $html );
				
			} else if ( $action == 'send_report' ) {

				$this->device = 'email_';
				
                // current report
                if ( 'report|products_status' == $module ) {
                	$report_data = $this->build_current_report();
					$this->view_in_browser = admin_url( 'admin-ajax.php?action=WooZone_report_settings&subaction=view_in_browser&log_id=' . $report_data['new_id'] );
                	$this->report_send_mail(array(
                		'module_'	=> $module_,
                		'data'		=> $report_data,
                		'html'		=> $this->_get_report_products_status( $report_data, 'email' ),
					));
				}
				if ( 'report|auto_import' == $module ) {
                	$report_data = $this->ai_build_current_report();
					$this->view_in_browser = admin_url( 'admin-ajax.php?action=WooZone_report_settings&subaction=view_in_browser&log_id=' . $report_data['new_id'] );
                	$this->report_send_mail(array(
                		'module_'	=> $module_,
                		'data'		=> $report_data,
                		'html'		=> $this->_ai_get_report_products_status( $report_data, 'email' ),
					));
				}

                $notifyStatus = get_option( sprintf( self::$report_alias_act, $module_ ), array() );
                {
                    $ret = array_merge($ret, array(
                        'status'    => 'valid',
                        'html'      => '<span class="success">' . sprintf( __('last operation: <em>'.str_replace('_', ' ', $action).'</em> | execution date: <em>%s</em>.', $this->the_plugin->localizationName), $ret['current_date'] ) . '</span>',
                    ));
                }
                
                $notifyStatus["report"] = $ret;
                update_option( sprintf( self::$report_alias_act, $module_ ), (array) $notifyStatus );
            }
            die(json_encode($ret));
        }

        public function ajax_request()
        {
            global $wpdb;
            $request = array(
                'action'                        => isset($_REQUEST['subaction']) ? $_REQUEST['subaction'] : '',
                //'module'                        => isset($_REQUEST['module']) ? $_REQUEST['module'] : 'synchronization',
                'filter'                        => isset($_REQUEST['filter']) ? $_REQUEST['filter'] : '',
                
                'id'                            => isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0,
            );
            extract($request);
            
            $ret = array(
                'status'        => 'invalid',
                'msg'           => '<div class="WooZone-sync-settings-msg WooZone-message WooZone-error">' . __('Invalid action!', $this->the_plugin->localizationName) . '</div>',
            );
            
            if ( empty($action) || !in_array($action, array('load_logs', 'view_log', 'log_id', 'log_action')) ) {
                die(json_encode($ret));
            }
   
            if ( in_array($action, array('load_logs', 'log_id', 'log_action')) ) {
                
				if ( in_array($action, array('log_id', 'log_action')) ) {
					$_SESSION['WooZone_report']["$action"] = $filter;
				}

				$__pms = array(
					'log_id'		=> isset($_SESSION['WooZone_report']["log_id"])
						? $_SESSION['WooZone_report']["log_id"] : '',
					'log_action'	=> isset($_SESSION['WooZone_report']["log_action"])
						? $_SESSION['WooZone_report']["log_action"] : '',
				);
                $productsList = $this->get_rows( $__pms );

                $ret = array_merge($ret, array(
                    'status'    => 'valid',
                    'msg'       => '',
                    'html'      => implode(PHP_EOL, isset($productsList['html']) ? $productsList['html'] : array()),
                    'nb'        => isset($productsList['nb']) ? $productsList['nb'] : 0,
                    'nbv'       => isset($productsList['nbv']) ? $productsList['nbv'] : 0,
                ));

            } else if ( $action == 'view_log' ) {
                
                $html = $this->get_view_log( $request );
                
                $ret = array_merge($ret, array(
                    'status'    => 'valid',
                    'msg'       => '',
                    'html'      => $html,
                ));
            }
            die(json_encode($ret));
        }


        /**
         * Utils
         */
        private function log_nice_format( $val ) {
            return ucwords( str_replace('_', ' ', $val) );
        }
        
        private function sort_hight_to_low( $a, $subkey ) {
            if ( empty($a) || !is_array($a) ) return array();

            $b = array();
            foreach($a as $k=>$v) {
                $b["$k"] = strtolower($v["$subkey"]);
            }
            arsort($b);
            foreach($b as $key=>$val) {
                $c["$key"] = $a["$key"];
            }
            return $c;
        }
    }
}
 
// Initialize the WooZoneReport class
$WooZoneReport = WooZoneReport::getInstance();