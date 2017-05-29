<?php
/*
Plugin Name:	 	WooZone - WooCommerce Amazon Affiliates
Plugin URI: 		http://codecanyon.net/item/woocommerce-amazon-affiliates-wordpress-plugin/3057503
Description: 		Choose from over a million products & earn advertising fees from the 1’st internet retailer online! You can earn up to 10% advertising fees from the 1’st trusted e-commerce leader with minimal effort. This plugin allows you to import unlimited number of products directly from Amazon right into your Wordpress WooCommerce Store! EnjoY!
Version: 			9.0.4.5
Author: 			AA-Team
Author URI: 		http://codecanyon.net/user/AA-Team/portfolio
Text Domain: 	    woozone
*/
! defined( 'ABSPATH' ) and exit;

define('WOOZONE_VERSION', '9.0.4.5');

// Derive the current path and load up WooZone
$plugin_path = dirname(__FILE__) . '/';
if(class_exists('WooZone') != true) {
	require_once($plugin_path . 'aa-framework/framework.class.php');
}

// Initalize the your plugin
$WooZone = new WooZone();

// Add an activation hook
register_activation_hook(__FILE__, array(&$WooZone, 'activate'));

// load textdomain
add_action( 'plugins_loaded', 'woozone_load_textdomain' );
add_action( 'plugins_loaded', 'woozone_check_integrity' );

function woozone_load_textdomain() {  
	load_plugin_textdomain( 'woozone', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
function woozone_check_integrity() {
	$mainObj = WooZone();
	return is_object($mainObj) ? $mainObj->plugin_integrity_check() : true;
}