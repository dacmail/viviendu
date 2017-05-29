<?php
/**
 * Config file, return as json_encode
 * http://www.aa-team.com
 * =======================
 *
 * @author		Andrei Dinca, AA-Team
 * @version		1.0
 */
 echo json_encode(
	array(
		'report' => array(
			'version' => '1.0',
			'menu' => array(
				'order' => 3,
				'title' => 'WooZone Report',
				'icon' => 'woozone_report'
			),
			'in_dashboard' => array(
				'icon' 	=> 'woozone_report',
				'url'	=> admin_url("admin.php?page=WooZone#!/report")
			),
			'description' => "Woozone Report - Get reports regarding your Products Stats.",
			'help' => array(
				'type' => 'remote',
				'url' => 'http://docs.aa-team.com/woocommerce-amazon-affiliates/documentation/synchronization-log/'
			),
			'module_init' => 'init.php',
			'load_in' => array(
				'backend' => array(
				    'admin.php?page=WooZone_report',
					'admin-ajax.php'
				),
				'frontend' => false
			),
			'javascript' => array(
				'admin',
				'hashchange',
				'tipsy'
			),
			'css' => array(
				'admin',
				'tipsy'
			)
		)
	)
 );