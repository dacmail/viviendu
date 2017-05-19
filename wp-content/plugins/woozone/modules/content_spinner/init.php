<?php
/*
* Define class WooZoneContentSpinner
* Make sure you skip down to the end of this file, as there are a few
* lines of code that are very important.
*/
!defined('ABSPATH') and exit;

if (class_exists('WooZoneContentSpinner') != true) {
    class WooZoneContentSpinner
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
		
		private $amz_settings;

        /*
        * Required __construct() function that initalizes the AA-Team Framework
        */
        public function __construct()
        {
        	global $WooZone;

        	$this->the_plugin = $WooZone;
			$this->module_folder = $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'modules/content_spinner/';
			$this->module = $this->the_plugin->cfg['modules']['content_spinner'];
			
			if (is_admin()) {
				$this->amz_settings = array(); // $WooZone->getAllSettings('array', 'amazon');

	            add_action('admin_menu', array( &$this, 'adminMenu' ));
			}

			// ajax  helper
			add_action('wp_ajax_WooZoneSpinContentRequest', array( &$this, 'ajax_request' ));
			add_action('wp_ajax_WooZone_rollback_content', array( &$this, 'ajax_request' ));
			add_action('wp_ajax_WooZone_save_content', array( &$this, 'ajax_request' ));
        }

		/**
	    * Singleton pattern
	    *
	    * @return WooZoneContentSpinner Singleton instance
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
    			$this->the_plugin->alias . " " . __('Content Spinner', $this->the_plugin->localizationName),
	            __('Content Spinner', $this->the_plugin->localizationName),
	            'manage_options',
	            $this->the_plugin->alias . "_content_spinner",
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
		<script type="text/javascript" src="<?php echo $this->module_folder;?>app.content_spinner.js" ></script>
		<div id="<?php echo WooZone()->alias?>">
			
			<div class="<?php echo WooZone()->alias?>-content">
				 
				<?php
				// show the top menu
				WooZoneAdminMenu::getInstance()->make_active('import|content_spinner')->show_menu(); 
				?>
	
				<!-- Content -->
				<section class="WooZone-main">
					
					<?php 
					echo WooZone()->print_section_header(
						$this->module['content_spinner']['menu']['title'],
						$this->module['content_spinner']['description'],
						$this->module['content_spinner']['help']['url']
					);
					?>
					
					<div class="panel panel-default WooZone-panel WooZone-content-spinner">
						<div class="panel-heading WooZone-panel-heading">
							<h2><?php _e('Synchronization logs', $this->the_plugin->localizationName);?></h2>
						</div>
						
						<div class="panel-body WooZone-panel-body">
									
							<!-- Content Area -->
							<div id="WooZone-content-area">
								<div class="WooZone-grid_4">
		                        	<div class="WooZone-panel">
										<div class="WooZone-panel-content">
											<form class="WooZone-form" action="#save_with_ajax">
												<div class="WooZone-form-row WooZone-table-ajax-list" id="WooZone-table-ajax-response">
												<?php
												WooZoneAjaxListTable::getInstance( $this->the_plugin )
													->setup(array(
														'id' 				=> 'WooZoneContentSpinner',
														'show_header' 		=> true,
														'search_box' 		=> false,
														'items_per_page' 	=> 5,
														'post_statuses' 	=> array(
															'publish'   => __('Published', $this->the_plugin->localizationName)
														),
														'list_post_types'	=> array('product'),
														'columns'			=> array(
															
															'preview'		=> array(
																'th'	=> __('Preview', $this->the_plugin->localizationName),
																'td'	=> '%preview%',
																'align' => 'left',
																'valign'=> 'top',
																'width' => '100'
															),
															
															'spinn_content'		=> array(
																'th'	=> __('Spinn Content', $this->the_plugin->localizationName),
																'td'	=> '%spinn_content%',
																'align' => 'left',
																'valign'=> 'top'
															),
	
														)
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
							
						</div>
					</div>
					
				</section>
			</div>
		</div>

<?php
		}

		/*
		* ajax_request, method
		* --------------------
		*
		* this will create requesto to 404 table
		*/
		public function ajax_request()
		{
			$request = array(
				'prodID' 		=> isset($_REQUEST['prodID']) ? $_REQUEST['prodID'] : 0,
				'replacements' 	=> isset($_REQUEST['replacements']) ? $_REQUEST['replacements'] : '',
				'sub_action' 	=> isset($_REQUEST['sub_action']) ? $_REQUEST['sub_action'] : '',
				'post_content' 	=> isset($_REQUEST['post_content']) ? $_REQUEST['post_content'] : '',
				'spinned_content' 	=> isset($_REQUEST['spinned_content']) ? $_REQUEST['spinned_content'] : '',
				'reorder_content' 	=> isset($_REQUEST['reorder_content']) ? $_REQUEST['reorder_content'] : '',
			);
			
			$return = array();
			$return[$request['sub_action']] = array(
				'status' => 'invalid',
				'data' => array()
			);
			
			// rollback content action
			if( $request['sub_action'] == 'rollback_content' ){

				// first check if you have the original content saved into DB
				$post_content = get_post_meta( $request['prodID'], 'WooZone_old_content', true );
				
				// if not, retrive from DB
				if( $post_content == false ){
					// make the final return
					die(json_encode($return));
				}
				
				delete_post_meta( $request['prodID'], 'WooZone_spinned_content' );
				delete_post_meta( $request['prodID'], 'WooZone_reorder_content' );
				delete_post_meta( $request['prodID'], 'WooZone_finded_replacements' );
				
				// Update the post into the database
				wp_update_post( array(
				      'ID'           => $request['prodID'],
				      'post_content' => $post_content
				) );
  
				$return[$request['sub_action']] = array(
					'status' => 'valid',
					'data' => array(
						'reorder_content' => ''
					)
				);

				// make the final return
				die(json_encode($return));
			}
			
			// save content action
			if( $request['sub_action'] == 'save_content' ){
				
				update_post_meta( $request['prodID'], 'WooZone_spinned_content', $request['spinned_content'] );
				update_post_meta( $request['prodID'], 'WooZone_reorder_content', $request['reorder_content'] );
				
				// Update the post into the database
				wp_update_post( array(
				      'ID'           => $request['prodID'],
				      'post_content' => $request['post_content']
				) );
  
				$return[$request['sub_action']] = array(
					'status' => 'valid',
					'data' => array(
						'reorder_content' => $request['reorder_content']
					)
				);  

				// make the final return
				die(json_encode($return));				
			}
			
			if( $request['sub_action'] == 'spin_content' ){
				
				/*// spin content action
				require_once( $this->module["folder_path"]. 'phpQuery.php' );
				require_once( $this->module["folder_path"]. 'spin.class.php' );

				$spinner = WooZoneSpinner::getInstance();
				$spinner->set_syn_language( $this->set_syn_language() );
				$spinner->set_replacements_number( $request['replacements'] );
				
				// first check if you have the original content saved into DB
				$post_content = get_post_meta( $request['prodID'], 'WooZone_old_content', true );
				
				// if not, retrive from DB
				if( $post_content == false ){
					$live_post = get_post( $request['prodID'], ARRAY_A );
					$post_content = $live_post['post_content'];
				}
				
				$spinner->load_content( $post_content );
				$spin_return = $spinner->spin_content();
				$reorder_content = $spinner->reorder_synonyms();
				$fresh_content = $spinner->get_fresh_content( $reorder_content );
				
				update_post_meta( $request['prodID'], 'WooZone_spinned_content', $spin_return['spinned_content'] );
				update_post_meta( $request['prodID'], 'WooZone_reorder_content', $reorder_content );
				update_post_meta( $request['prodID'], 'WooZone_old_content', $spin_return['old_content'] );
				update_post_meta( $request['prodID'], 'WooZone_finded_replacements', $spin_return['finded_replacements'] );
				
				// Update the post into the database
				wp_update_post( array(
				      'ID'           => $request['prodID'],
				      'post_content' => $fresh_content
				) );
  
				$return[$request['sub_action']] = array(
					'status' => 'valid',
					'data' => array(
						'reorder_content' => $reorder_content
					)
				);*/
				$return[$request['sub_action']] = $this->the_plugin->spin_content(array(
					'prodID'		=> $request['prodID'],
					'replacements'	=> $request['replacements']
				));
				
				// make the final return
				die(json_encode($return));
			} 

			// make the final return
			die(json_encode($return));
		}
    }
}
 
// Initialize the WooZoneContentSpinner class
$WooZoneContentSpinner = WooZoneContentSpinner::getInstance();
