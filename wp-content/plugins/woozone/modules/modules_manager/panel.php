<?php 

/*
* Define class Modules Manager List
* Make sure you skip down to the end of this file, as there are a few
* lines of code that are very important.
*/

! defined( 'ABSPATH' ) and exit;

// load the modules managers class
$module_class_path = $module['folder_path'] . 'aaModulesManager.class.php';

if(is_file($module_class_path)) {

	require_once( 'aaModulesManager.class.php' );

	//$WooZoneAAModulesManger = new WooZoneAAModulesManger($this->cfg, $module);
	$WooZoneAAModulesManger = WooZoneAAModulesManger::getInstance();

	// print the lists interface 
	echo $WooZoneAAModulesManger->printListInterface();
}