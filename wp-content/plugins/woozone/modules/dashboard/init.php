<?php
/*
* Define class WooZoneDashboard
* Make sure you skip down to the end of this file, as there are a few
* lines of code that are very important.
*/
!defined('ABSPATH') and exit;
if (class_exists('WooZoneDashboard') != true) {
    class WooZoneDashboard
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
		
		public $ga = null;
		public $ga_params = array();
		
		public $boxes = array();

		static protected $_instance;

        /*
        * Required __construct() function that initalizes the AA-Team Framework
        */
        public function __construct()
        { 
        	global $WooZone;
 
        	$this->the_plugin = $WooZone;
			$this->module_folder = $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'modules/dashboard/';
			
			if (is_admin()) {
	            add_action( "admin_enqueue_scripts", array( &$this, 'admin_print_styles') );
				add_action( "admin_print_scripts", array( &$this, 'admin_load_scripts') );
			}
			 
			// load the ajax helper
			require_once( $this->the_plugin->cfg['paths']['plugin_dir_path'] . 'modules/dashboard/ajax.php' );
			new WooZoneDashboardAjax( $this->the_plugin );
			
			// add the boxes
			$this->addBox( 'plugin_dashboard_msg', '', $this->plugin_dashboard_msg(), array(
				'size' => 'grid_4'
			) );
			
			$this->addBox( 'dashboard_links', '', $this->links(), array(
				'size' => 'grid_4'
			) );

			$this->addBox( 'products_view', '', $this->total_number_for_products(), array(
				'size' => 'grid_1'
			) );

			$this->addBox( 'products_view2', '', $this->total_product_views(), array(
				'size' => 'grid_1'
			) );

			$this->addBox( 'products_view3', '', $this->total_add_cart(), array(
				'size' => 'grid_1'
			) );

			$this->addBox( 'products_view4', '', $this->total_amazon_redirect(), array(
				'size' => 'grid_1',
				'noright' => true
			) );
			
			$this->addBox( 'products_performances', 'Top 
				<select class="WooZone-numer-items-in-top">
					<option value="12">10</option>
					<option value="20">20</option>
					<option value="30">30</option>
					<option value="50">50</option>
					<option value="100">100</option>
					<option value="0">Show All</option>
				</select>
				&nbsp;WooZone Products Performances', $this->products_performances(), array(
				'size' => 'grid_4'
			) );
			
			/*$this->addBox( 'aateam_products', 'Other products by AA-Team:', $this->aateam_products(), array(
				'size' => 'grid_4'
			) );*/
			
			$this->addBox( 'support', 'Need AA-Team Support?', $this->support() );
			$this->addBox( 'changelog', 'Changelog', $this->changelog() );
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
	    
		public function admin_print_styles()
		{
        	wp_enqueue_style( 'WooZone-DashboardBoxes' );
		}
		
		public function admin_load_scripts()
		{
			wp_enqueue_script( 'WooZone-DashboardBoxes', $this->module_folder . 'app.dashboard.js', array(), '1.0', true );
		}
		
		public function getBoxes()
		{
			$ret_boxes = array();
			if( count($this->boxes) > 0 ){
				foreach ($this->boxes as $key => $value) { 
					$ret_boxes[$key] = $value;
				}
			}
			return $ret_boxes;
		}
		
		private function formatAsFreamworkBox( $html_content='', $atts=array() )
		{
			return array(
				'size' 		=> isset($atts['size']) ? $atts['size'] : 'grid_4', // grid_1|grid_2|grid_3|grid_4
	            'header' 	=> isset($atts['header']) ? $atts['header'] : false, // true|false
	            'toggler' 	=> false, // true|false
	            'buttons' 	=> isset($atts['buttons']) ? $atts['buttons'] : false, // true|false
	            'style' 	=> isset($atts['style']) ? $atts['style'] : 'panel-widget', // panel|panel-widget
	            
	            // create the box elements array
	            'elements' => array(
	                array(
	                    'type' => 'html',
	                    'html' => $html_content
	                )
	            )
			);
		}
		
		private function addBox( $id='', $title='', $html='', $atts=array() )
		{ 
			// check if this box is not already in the list
			if( isset($id) && trim($id) != "" && !isset($this->boxes[$id]) ){
				
				$box = array();
				
				$box[] = '<div class="WooZone-dashboard-status-box panel panel-default WooZone-panel WooZone-dashboard-box-' . ( $id ) . ' ' . ( isset($atts['size']) ? 'dashboard_' . $atts['size'] : '' ) . ' ' . ( isset($atts['noright']) ? 'dashboard_box_noright' : '' ) . '">';
				if( isset($title) && trim($title) != "" ){
					$box[] = 	'<div class="panel-heading WooZone-panel-heading">';
					$box[] = 		'<h2>' . ( $title ) . '</h2>';
					$box[] = 	'</div>';
				}
				$box[] = 	$html;
				$box[] = '</div>';

				$this->boxes[$id] = $this->formatAsFreamworkBox( implode("\n", $box), $atts );
				
			}
		}
		
		public function formatRow( $content=array() )
		{
			$html = array();
			
			$html[] = '<div class="WooZone-dashboard-status-box-row">';
			if( isset($content['title']) && trim($content['title']) != "" ){
				$html[] = 	'<h2>' . ( isset($content['title']) ? $content['title'] : 'Untitled' ) . '</h2>';
			}
			if( isset($content['ajax_content']) && $content['ajax_content'] == true ){
				$html[] = '<div class="WooZone-dashboard-status-box-content is_ajax_content">';
				$html[] = 	'{' . ( isset($content['id']) ? $content['id'] : 'error_id_missing' ) . '}';
				$html[] = '</div>';
			}
			else{
				$html[] = '<div class="WooZone-dashboard-status-box-content is_ajax_content">';
				$html[] = 	( isset($content['html']) && trim($content['html']) != "" ? $content['html'] : '!!! error_content_missing' );
				$html[] = '</div>';
			}
			$html[] = '</div>';
			
			return implode("\n", $html);
		}
		
		public function products_performances()
		{
			$html = array();
			
			$html[] = $this->formatRow( array( 
				'id' 			=> 'products_performances',
				'title' 		=> '',
				'html'			=> '',
				'ajax_content' 	=> true
			) );
			
			return implode("\n", $html);
		}

		public function plugin_dashboard_msg()
		{
			$html = array();
	
			$html[] = '<div class="panel-heading ' . ( WooZone()->alias ) . '-panel-heading">';
			$html[] = 	'<h1>Dashboard</h1>';
			$html[] = 	'Dashboard Area - Here you will find useful shortcuts to different modules inside the plugin.';
			$html[] = 	''; // extra content here ...
			$html[] = '</div>';
			$html[] = '<div class="panel-body ' . ( WooZone()->alias ) . '-panel-body ' . ( WooZone()->alias ) . '-no-padding" >
						<a href="' . ( WooZone()->cfg['modules']['dashboard']['dashboard']['help']['url'] ) . '" target="_blank" class="' . ( WooZone()->alias ) . '-tab"><i class="' . ( WooZone()->alias ) . '-icon-support"></i> Documentation</a>
						<a href="http://codecanyon.net/user/aa-team/portfolio" target="_blank" class="' . ( WooZone()->alias ) . '-tab"><i class="' . ( WooZone()->alias ) . '-icon-other_products"></i> More AA-Team Products</a>
					</div>';

			return implode("\n", $html);
		}

		public function support()
		{
			$html = array();
			$html[] = '<a href="http://support.aa-team.com" target="_blank"><img src="' . ( $this->module_folder ) . 'images/support_banner.jpg"></a>';
			
			return implode("\n", $html);
		}
		
		public function aateam_products()
		{
			$html = array();
			
			$html[] = '<ul class="WooZone-aa-products-tabs">';
			$html[] = 	'<li class="on">';
			$html[] = 		'<a href="javascript: void(0)" class="WooZone-aa-items-codecanyon">CodeCanyon</a>';
			$html[] = 	'</li>';
			$html[] = 	'<li>';
			$html[] = 		'<a href="javascript: void(0)" class="WooZone-aa-items-themeforest">ThemeForest</a>';
			$html[] = 	'</li>';
			$html[] = 	'<li>';
			$html[] = 		'<a href="javascript: void(0)" class="WooZone-aa-items-graphicriver">GraphicRiver</a>';
			$html[] = 	'</li>';
			$html[] = '</ul>';
			
			$html[] = $this->formatRow( array( 
				'id' 			=> 'aateam_products',
				'title' 		=> '',
				'html'			=> '',
				'ajax_content' 	=> true
			) );
 
			return implode("\n", $html);
		}
		
		public function changelog()
		{
			$html = array();

			$html[] = '<div class="panel-body WooZone-panel-body WooZone-changelog">';
			$html[] = 	'<article>';
			$changelog_file = 	file_get_contents( $this->the_plugin->cfg['paths']['plugin_dir_path'] . '/changelog.txt' );

			$re = "/(##.*\\n)/"; 
			preg_match_all($re, $changelog_file, $matches);
			if( isset($matches[0]) && count($matches) > 0 ){
				foreach ($matches[0] as $str) {
					//$str = trim($str);
					$changelog_file = str_replace( $str, "<h3>" . ( $str ) . "</h3>", $changelog_file );
				}
			}
			
			$html[] = nl2br($changelog_file);
			$html[] = 	'</article>';
			$html[] = '</div>';
			
			return implode("\n", $html);
		}

		public function total_number_for_products()
		{
			$html = array();
			$html[] = '<div class="WooZone-dynamic-status">';
			$html[] = 	'<h3><span class="WooZone-ds-value-nb_products"></span></h3>';
			$html[] = 	'<small>Total Number of products</small>';
			$html[] = 	'<img src="' . ( WooZone()->cfg['paths']['plugin_dir_url'] . 'icon_24.png' ) . '" />';
			$html[] = '</div>';

			return  implode("\n", $html);
		}

		public function total_product_views()
		{
			$html = array();
			$html[] = '<div class="WooZone-dynamic-status">';
			$html[] = 	'<h3><span class="WooZone-ds-value-total_hits"></span></h3>';
			$html[] = 	'<small>Total products views</small>';
			$html[] = 	'<i class="fa fa-users" aria-hidden="true"></i>';
			$html[] = '</div>';

			return  implode("\n", $html);
		}

		public function total_add_cart()
		{
			$html = array();
			$html[] = '<div class="WooZone-dynamic-status">';
			$html[] = 	'<h3><span class="WooZone-ds-value-total_addtocart"></span></h3>';
			$html[] = 	'<small>Products added to cart</small>';
			$html[] = 	'<i class="fa fa-cart-arrow-down" aria-hidden="true"></i>';
			$html[] = '</div>';

			return  implode("\n", $html);
		}

		public function total_amazon_redirect()
		{
			$html = array();
			$html[] = '<div class="WooZone-dynamic-status">';
			$html[] = 	'<h3><span class="WooZone-ds-value-amazon_redirect"></span></h3>';
			$html[] = 	'<small>Total redirected to Amazon</small>';
			$html[] = 	'<i class="fa fa-amazon" aria-hidden="true"></i>';
			$html[] = '</div>';

			return  implode("\n", $html);
		}
		
		public function audience_overview()
		{
			$html = array();
			$html[] = '<div class="WooZone-audience-graph" id="WooZone-audience-visits-graph" data-fromdate="' . ( date('Y-m-d', strtotime("-1 week")) ) . '" data-todate="' . ( date('Y-m-d') ) . '"></div>';

			return  implode("\n", $html);
		}
		
		public function links()
		{
			$html = array();
			$html[] = '<div class="panel-body WooZone-panel-body WooZone-dashboard-icons">';
			$html[] = 	'<ul class="WooZone-summary-links">';

			foreach ($this->the_plugin->cfg['modules'] as $key => $value) {
  
				if( !in_array( $key, array_keys($this->the_plugin->cfg['activate_modules'])) ) continue;
				//var_dump('<pre>',$value[$key],'</pre>');  
				$in_dashboard = isset($value[$key]['in_dashboard']) ? $value[$key]['in_dashboard'] : array();
  
				if( count($in_dashboard) > 0 ){
					
					if ( !isset($in_dashboard['url']) && is_array($in_dashboard) ) {
						$title = $value[$key]['menu']['title'];
						foreach ($in_dashboard as $key2 => $value2) {
							$title = isset($value2['title']) ? $value2['title'] : $title;
							$html[] = '
							<li>
								<a href="' . ( $value2['url'] ) . '">
									<span class="text">' . ( $title ) . '</span>
								</a>
							</li>';
						}
					}
					else {
						$html[] = '
						<li>
							<a href="' . ( $in_dashboard['url'] ) . '">
								<i class="WooZone-icon-' . ( $value[$key]['menu']['icon'] ) . '"></i>
								<span class="text">' . ( $value[$key]['menu']['title'] ) . '</span>
							</a>
						</li>';
					}
				}
			}
			
			$html[] = 	'</ul>';
			$html[] = '</div>';
			
			return implode("\n", $html);
		}
    }
}

// Initialize the WooZoneDashboard class
//$WooZoneDashboard = WooZoneDashboard::getInstance( isset($module) ? $module : array() );
//$WooZoneDashboard = new WooZoneDashboard( isset($module) ? $module : array() );
// $WooZone->cfg, ( isset($module) ? $module : array()) 
$WooZoneDashboard = WooZoneDashboard::getInstance();