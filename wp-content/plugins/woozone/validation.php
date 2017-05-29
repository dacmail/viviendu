<?php
/**
 * Description: 	We developed this module to stop all those haters who steal our hard work!
 * Author: 		AA-Team
 * Author URI:		http://codecanyon.net/user/AA-Team/portfolio
**/
! defined( 'ABSPATH' ) and exit;

if(class_exists('WooZone_Validation') != true) {
	class WooZone_Validation {

		const VERSION = 1;
		const ALIAS = 'WooZone';

		/**
		 * configuration storage
		 *
		 * @var array
		 */
		public $cfg = array();

		private $key_sep = '#!#';

		/**
		 * The constructor
		 */
		public function __construct ()
		{
			add_action('wp_ajax_' . ( self::ALIAS ) . 'TryActivate', array( $this, 'aaTeamServerValidate' ));
		}

		public function aaTeamServerValidate () {

			// fake return, just for development
			$input = array();

			// validation link
			$input = wp_remote_request( 'http://cc.aa-team.com/validation/validate.php?ipc=' .
			(urlencode($_REQUEST['ipc'])) .
			'&email=' . (urlencode($_REQUEST['email'])) .
			'&app=' .  self::ALIAS );

			// try to access the envato returns
			$aaTeamServer_return = json_decode($input['body'] ,true);

			if($aaTeamServer_return['status'] == 'valid') {
				$envato_return = $aaTeamServer_return['envato_obj'];
				if(count($envato_return) > 1){
					update_option( self::ALIAS . '_register_key', $_REQUEST['ipc']);
					update_option( self::ALIAS . '_register_email', $_REQUEST['email']);
					update_option( self::ALIAS . '_register_buyer', $envato_return['buyer']);
					update_option( self::ALIAS . '_register_item_id', $envato_return['item-id']);
					update_option( self::ALIAS . '_register_licence', $envato_return['licence']);
					update_option( self::ALIAS . '_register_item_name', $envato_return['item-name']);

					// generate the hash marker
					$hash = md5($this->encrypt( $_REQUEST['ipc'] ));

					// update to db the hash for plugin
					update_option( self::ALIAS . '_hash', $hash);

					die(json_encode(
						array(
							'status' => 'OK'
						)
					));
				}
			}

			die (json_encode(
				array(
					'status' => 'ERROR',
					'msg'	=> 'Unable to validate this plugin. Please contact AA-Team Support!'
				)
			));
		}

		public function isReg ( $hash )
		{
			$current_key = get_option( self::ALIAS . '_register_key');

			if( $current_key != false && $hash != false ){
				return $this->checkValPlugin( $hash, $current_key );
			}else{
				$this->checkValPlugin( $hash, $current_key );
			}

			return false;
		}

		private function checkValPlugin ( $hash, $code )
		{
			global $wpdb;

			$validation_date = get_option( self::ALIAS . '_register_timestamp');
			$sum_hash = md5($this->encrypt( $code, $validation_date ));

			// invalid, unload the modules
			if($sum_hash != $hash){
				$allSettingsQuery = "SELECT * FROM " . $wpdb->prefix . "options where 1=1 and option_name REGEXP '" . ( self::ALIAS . '_module' ) . "_([a-z])'";
				$results = $wpdb->get_results( $allSettingsQuery, ARRAY_A);
				// prepare the return
				$return = array();
				if( count($results) > 0 ){
					foreach ($results as $key => $value){
						if( get_option('WooZone_do_activation_redirect', false) == false ){
							//update_option( $value['option_name'], 'false' );
						}
					}
				}
			}else{
				return 'valid_hash';
			}
		}

		private function encrypt ( $code, $sendTime=null )
		{
			// add some extra data to hash
			$register_email = get_option( self::ALIAS . '_register_email');
			$buyer = get_option( self::ALIAS . '_register_buyer');
			$item_id = get_option( self::ALIAS . '_register_item_id');
			$validation_date = !isset($sendTime) ? time() : $sendTime;

			if(!isset($sendTime)) {
				// store the date into DB, use for decrypt
				update_option( self::ALIAS . '_register_timestamp', $validation_date);
			}

			return  $validation_date . $this->key_sep .
					$register_email . $this->key_sep .
					$this->getHost(get_option('siteurl')) . $this->key_sep .
					$buyer . $this->key_sep .
					$item_id . $this->key_sep .
					$code . $this->key_sep;
		}

		private function decrypt ( $code )
		{

		}

		private function getHost ( $url )
		{
			$__ = parse_url( $url );
			return $__['host'];
		}
	}
}