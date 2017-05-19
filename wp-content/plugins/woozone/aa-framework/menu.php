<?php
/**
 * AA-Team - http://www.aa-team.com
 * ===============================+
 *
 * @package		WooZoneAdminMenu
 * @author		Andrei Dinca
 * @version		1.0
 */
! defined( 'ABSPATH' ) and exit;

if(class_exists('WooZoneAdminMenu') != true) {
	class WooZoneAdminMenu {
		
		/*
        * Some required plugin information
        */
        const VERSION = '1.0';

        /*
        * Store some helpers config
        */
		public $the_plugin = null;
		private $the_menu = array();
		private $current_menu = '';
		private $ln = '';
		
		private $menu_depedencies = array();

		static protected $_instance;

        /*
        * Required __construct() function that initalizes the AA-Team Framework
        */
        public function __construct()
        {
        	global $WooZone;
        	$this->the_plugin = $WooZone;
			$this->ln = $this->the_plugin->localizationName;
			
			// update the menu tree
			$this->the_menu_tree();
			
			return $this;
        }

		/**
	    * Singleton pattern
	    *
	    * @return WooZoneDashboard Singleton instance
	    */
	    static public function getInstance()
	    {
	        if (!self::$_instance) {
	            self::$_instance = new self;
	        }

	        return self::$_instance;
	    }
		
		private function the_menu_tree()
		{
			if ( isset($this->the_plugin->cfg['modules']['depedencies']['folder_uri'])
				&& !empty($this->the_plugin->cfg['modules']['depedencies']['folder_uri']) ) {
				$this->menu_depedencies['depedencies'] = array( 
					'title' => __( 'Plugin depedencies', $this->ln ),
					'url' => admin_url("admin.php?page=WooZone"),
					'folder_uri' => $this->the_plugin->cfg['paths']['freamwork_dir_url'],
					'menu_icon' => 'dashboard'
				);
                
                $this->clean_menu();
				return true;
			}

			$this->the_menu['dashboard'] = array( 
				'title' => __( 'Dashboard', $this->ln ),
				'url' => admin_url("admin.php?page=WooZone#!/dashboard"),
				'folder_uri' => $this->the_plugin->cfg['paths']['freamwork_dir_url'],
				'menu_icon' => 'dashboard'
			);
			
			$this->the_menu['configuration'] = array( 
				'title' => __( 'Configuration', $this->ln ),
				'url' => "#!/",
				'folder_uri' => $this->the_plugin->cfg['paths']['freamwork_dir_url'],
				'menu_icon' => 'amazon',
				'submenu' => array(
					'amazon' => array(
						'title' => __( 'Amazon config', $this->ln ),
						'url' => admin_url("admin.php?page=WooZone#!/amazon"),
						'folder_uri' => $this->the_plugin->cfg['modules']['amazon']['folder_uri'],
						'menu_icon' => 'amazon'
					),
				)
			);
			
			$this->the_menu['import'] = array( 
				'title' => __( 'Import Products', $this->ln ),
				'url' => "#!/",
				'folder_uri' => $this->the_plugin->cfg['paths']['freamwork_dir_url'],
				'submenu' => array(
					'advanced_search' => array(
						'title' => __( 'Advanced Search', $this->ln ),
						'url' => admin_url("admin.php?page=WooZone#!/advanced_search"),
						'folder_uri' => $this->the_plugin->cfg['modules']['advanced_search']['folder_uri'],
						'menu_icon' => 'search'
					),
					
					'csv_products_import' => array(
						'title' => __( 'CSV Bulk Import', $this->ln ),
						'url' => admin_url("admin.php?page=WooZone#!/csv_products_import"),
						'folder_uri' => $this->the_plugin->cfg['modules']['csv_products_import']['folder_uri'],
						'menu_icon' => 'csv_import'
					),
					
					'asin_grabber' => array(
						'title' => __( 'ASIN Grabber', $this->ln ),
						'url' => admin_url("admin.php?page=WooZone_asin_grabber"),
						'folder_uri' => $this->the_plugin->cfg['modules']['asin_grabber']['folder_uri'],
						'menu_icon' => 'asin_grabber'
					),
					
                    
                    'insane_import' => array(
                        'title' => __( 'Insane Import Mode', $this->ln ),
                        'url' => admin_url('admin.php?page=' . $this->the_plugin->alias . "_insane_import"),
                        'folder_uri' => $this->the_plugin->cfg['modules']['insane_import']['folder_uri'],
                        'menu_icon' => 'insane_import',
                        'submenu' => array(
                            'report_Settings' => array(
                                'title' => __( 'Insane Import Settings', $this->ln ),
                                'url' => admin_url('admin.php?page=' . $this->the_plugin->alias . '#!/insane_import'),
                                'menu_icon' => 'sub_menu'
                            ),
                        )
                    ),
                    
                    'content_spinner' => array(
                        'title' => __( 'Amazon Content Spinner', $this->ln ),
                        'url' => admin_url("admin.php?page=WooZone_content_spinner"),
                        'folder_uri' => $this->the_plugin->cfg['modules']['content_spinner']['folder_uri'],
                        'menu_icon' => 'content_spinner'
                    ),
                    
                    'auto_import' => array(
                        'title' => __( 'Auto Import Products', $this->ln ),
                        'url' => admin_url('admin.php?page=' . $this->the_plugin->alias . "_auto_import_queue"),
                        'folder_uri' => $this->the_plugin->cfg['modules']['auto_import']['folder_uri'],
                        'menu_icon' => 'auto_import',
                        'submenu' => array(
                            'queue' => array(
                                'title' => __( 'Auto Import Queue', $this->ln ),
                                'url' => admin_url('admin.php?page=' . $this->the_plugin->alias . "_auto_import_queue"),
                                'menu_icon' => 'sub_menu'
                            ),
                            'search' => array(
                                'title' => __( 'Auto Import Search', $this->ln ),
                                'url' => admin_url('admin.php?page=' . $this->the_plugin->alias . '_auto_import_search'),
                                'menu_icon' => 'sub_menu'
                            ),
                        )
                    ),
				)
			);
			
			$this->the_menu['info'] = array( 
				'title' => __( 'Plugin Status', $this->ln ),
				'url' => "#!/",
				'folder_uri' => $this->the_plugin->cfg['paths']['freamwork_dir_url'],
				'menu_icon' => 'images/pluginstatus.png',
				'submenu' => array(
				
					'speed_optimization' => array(
						'title' => __( 'Speed Optimization', $this->ln ),
						'url' => admin_url("admin.php?page=WooZone_speed_optimization"),
						'folder_uri' => $this->the_plugin->cfg['modules']['assets_download']['folder_uri'],
						'menu_icon' => 'assets_dwl'
					),
					
					'assets_download' => array(
						'title' => __( 'Assets Download', $this->ln ),
						'url' => admin_url("admin.php?page=WooZone_assets_download"),
						'folder_uri' => $this->the_plugin->cfg['modules']['assets_download']['folder_uri'],
						'menu_icon' => 'assets_dwl'
					),
					
					'stats_prod' => array(
						'title' => __( 'Products Stats', $this->ln ),
						'url' => admin_url("admin.php?page=WooZone_stats_prod"),
						'folder_uri' => $this->the_plugin->cfg['modules']['stats_prod']['folder_uri'],
						'menu_icon' => 'products_statistics'
					),
					
                    'synchronization_log' => array(
						'title' => __( 'Synchronization log', $this->ln ),
						'url' => admin_url("admin.php?page=WooZone_synclog"),
						'folder_uri' => $this->the_plugin->cfg['modules']['synchronization']['folder_uri'],
						'menu_icon' => 'sync',
                        'submenu' => array(
                            'synchronization_log_View' => array(
                                'title' => __( 'Synchronization log', $this->ln ),
                                'url' => admin_url('admin.php?page=' . $this->the_plugin->alias . "_synclog"),
                                'menu_icon' => 'sub_menu'
                            ),
                            'synchronization_log_Settings' => array(
                                'title' => __( 'Synchronization log Settings', $this->ln ),
                                'url' => admin_url("admin.php?page=WooZone#!/synchronization"),
                                'menu_icon' => 'sub_menu'
                            ),
                        )
                    ),
					
                    'cronjobs' => array(
                        'title' => __( 'Plugin Cronjobs', $this->ln ),
                        'url' => admin_url("admin.php?page=WooZone#!/cronjobs"),
                        'folder_uri' => $this->the_plugin->cfg['modules']['cronjobs']['folder_uri'],
                        'menu_icon' => 'cronjobs'
                    ),
					
					'amazon_debug' => array(
						'title' => __( 'Amazon Debug', $this->ln ),
						'url' => admin_url("admin.php?page=WooZone#!/amazon_debug"),
						'folder_uri' => $this->the_plugin->cfg['modules']['amazon_debug']['folder_uri'],
						'menu_icon' => 'debug'
					),
					
                    'report' => array(
                        'title' => __( 'Woozone Report', $this->ln ),
                        'url' => admin_url('admin.php?page=' . $this->the_plugin->alias . "_report"),
                        'folder_uri' => $this->the_plugin->cfg['modules']['report']['folder_uri'],
                        'menu_icon' => 'woozone_report',
                        'submenu' => array(
                            'report_Settings' => array(
                                'title' => __( 'Woozone Report Settings', $this->ln ),
                                'url' => admin_url("admin.php?page=WooZone#!/report"),
                                'menu_icon' => 'sub_menu'
                            ),
                        )
                    ),
					
                    'server_status' => array(
                        'title' => __( 'Server Status', $this->ln ),
                        'url' => admin_url("admin.php?page=WooZone_server_status"),
                        'folder_uri' => $this->the_plugin->cfg['modules']['server_status']['folder_uri'],
                        'menu_icon' => 'server_status'
                    ),
				)
			);
			
			$this->the_menu['general'] = array( 
				'title' => __( 'Plugin Settings', $this->ln ),
				'url' => "#!/",
				'folder_uri' => $this->the_plugin->cfg['paths']['freamwork_dir_url'],
				'menu_icon' => '',
				'submenu' => array(
					'modules_manager' => array(
						'title' => __( 'Modules Manager', $this->ln ),
						'url' => admin_url("admin.php?page=WooZone#!/modules_manager"),
						'folder_uri' => $this->the_plugin->cfg['modules']['modules_manager']['folder_uri'],
						'menu_icon' => 'modules'
					),
					
					'setup_backup' => array(
						'title' => __( 'Setup / Backup', $this->ln ),
						'url' => admin_url("admin.php?page=WooZone#!/setup_backup"),
						'folder_uri' => $this->the_plugin->cfg['modules']['setup_backup']['folder_uri'],
						'menu_icon' => 'setup_backup'
					),
					
					/*'remote_support' => array(
						'title' => __( 'Remote Support', $this->ln ),
						'url' => admin_url("admin.php?page=WooZone_remote_support"),
						'folder_uri' => $this->the_plugin->cfg['modules']['remote_support']['folder_uri'],
						'menu_icon' => 'support'
					),*/
				)
			);
            
            $this->clean_menu();
		}

        public function clean_menu() {
            foreach ($this->the_menu as $key => $value) {
                if( isset($value['submenu']) ){
                    foreach ($value['submenu'] as $kk2 => $vv2) {
                        $kk2orig = $kk2;
                        // fix to support same module multiple times in menu
                        $kk2 = substr( $kk2, 0, (($t = strpos($kk2, '--'))!==false ? $t : strlen($kk2)) );
  
                        if( ($kk2 != 'synchronization_log')
                            && !in_array( $kk2, array_keys($this->the_plugin->cfg['activate_modules'])) ) {
                            unset($this->the_menu["$key"]['submenu']["$kk2orig"]);
                        }
                    }
                }
            }

            foreach ($this->the_menu as $k=>$v) { // menu
                if ( isset($v['submenu']) && empty($v['submenu']) ) {
                    unset($this->the_menu["$k"]);
                }
            }
        }
		
		public function show_menu( $pluginPage='' )
		{
			$plugin_data = WooZone_get_plugin_data();
  			
			$html = array();

			$html[] = '<aside class="' . ( WooZone()->alias ) . '-sidebar">';
			//$html[] = 	'<a href="' . ( admin_url( 'admin.php?page=WooZone' ) ) . '" class="' . ( WooZone()->alias ) . '-title">' . ( WooZone()->pluginName ) . ' <span><i>V</i> ' . ( $plugin_data['version'] ) . '</span></a>';
			$html[] = 	'<a href="' . ( admin_url( 'admin.php?page=WooZone' ) ) . '" class="' . ( WooZone()->alias ) . '-title"><img src="' . (  $this->the_plugin->cfg['paths']['freamwork_dir_url'] . 'images/logo.png' ) . '" /> <span><i>V</i> ' . ( $plugin_data['version'] ) . '</span></a>';
			$html[] = '<div class="' . ( WooZone()->alias ) . '-responsive-menu hide">Menu <i class="fa fa-bars" aria-hidden="true"></i></div>';	
			$html[] = 	'<nav class="' . ( WooZone()->alias ) . '-nav">';
					
			if ( $pluginPage == 'depedencies' ) {
				$menu = $this->menu_depedencies;
				$this->current_menu = array(
					0 => 'depedencies',
					1 => 'depedencies'
				);
			} else {
				$menu = $this->the_menu;
			}

			foreach ($menu as $key => $value) {
				if( $key == 'import' ) {
					//var_dump('<pre>',$value ,'</pre>'); die; 
				} 
				$html[] = '<ul>';
				if( $key != "dashboard" ){
					$html[] = '<li class="' . ( WooZone()->alias ) . '-nav-title">' . ( $value['title'] ) . '</li>';
				}
			
				$html[] = '<li id="' . ( WooZone()->alias ) . '-nav-' . ( $key ) . '" class="' . ( WooZone()->alias ) . '-section-' . ( $key ) . '">';
				
				if( $value['url'] != "#!/" ){

					$html[] = 	'<a href="' . ( $value['url'] ) . '" class="' . ( isset($this->current_menu[0]) && ( $key == $this->current_menu[0] ) ? 'active' : '' ) . '">';
					if( isset($value['menu_icon']) ){
						$html[] = 	'<i class="' . ( WooZone()->alias ) . '-icon-' . ( $value['menu_icon'] ) . '"></i>';
					}

					$html[] = $value['title'] . '</a>';
				}

				if( isset($value['submenu']) ){
					//$html[] = 	'<ul class="' . ( WooZone()->alias ) . '-sub-menu">';
					foreach ($value['submenu'] as $kk2 => $vv2) {

						if( ($kk2 != 'synchronization_log') && isset($this->the_plugin->cfg['activate_modules']) && is_array($this->the_plugin->cfg['activate_modules']) && !in_array( $kk2, array_keys($this->the_plugin->cfg['activate_modules'])) ) continue;

						$html[] = '<li class="' . ( WooZone()->alias ) . '-section-' . ( $kk2 ) . '" id="WooZone-nav-' . ( $kk2 ) . '">';
						
						$html[] = 	'<a href="' . ( $vv2['url'] ) . '" class="' . ( isset($this->current_menu[1]) && $kk2 == $this->current_menu[1] ? 'active' : '' ) . '">';
						if( isset($vv2['menu_icon']) ){
							$html[] = 	'<i class="' . ( WooZone()->alias ) . '-icon-' . ( $vv2['menu_icon'] ) . '"></i>';
						}
						$html[] = $vv2['title'] . '</a>'; 
						
						if( isset($vv2['submenu']) ){
							$html[] = 	'<ul class="' . ( WooZone()->alias ) . '-sub-sub-menu">';
							foreach ($vv2['submenu'] as $kk3 => $vv3) {
								$html[] = '<li id="' . ( WooZone()->alias ) . '-sub-sub-nav-' . ( $kk3 ) . '">';
								$html[] = 	'<a href="' . ( $vv3['url'] ) . '">';
								if( isset($vv3['menu_icon']) ){
									$html[] = 	'<i class="' . ( WooZone()->alias ) . '-icon-' . ( $vv3['menu_icon'] ) . '"></i>';
								}
								$html[] = 	$vv3['title'] . '</a>';
								$html[] = '</li>';
							}
							$html[] = 	'</ul>';
						}
						$html[] = '</li>';
					}
					//$html[] = 	'</ul>';
				}
				$html[] = '</li>';
				$html[] = '</ul>';
			}

			$html[] = 	'</nav>';
    		$html[] = '</aside>';



			echo implode("\n", $html);
		}

		public function make_active( $section='' )
		{
			$this->current_menu = explode("|", $section);
			return $this;
		}
	}
}