<?php
/**
 * Config file, return as json_encode
 * http://www.aa-team.com
 * =======================
 *
 * @author		Andrei Dinca, AA-Team
 * @version		1.0 - in development mode
 */
 echo json_encode(
	array(
		'synchronization' => array(
			'version' => '1.0',
			'menu' => array(
				'order' => 7,
				'title' => 'Products synchronization',
				'icon' => 'sync'
			),
			'in_dashboard' => array(
				'icon' 	=> 'sync',
				'url'	=> admin_url("admin.php?page=WooZone_synclog")
			),
			'help' => array(
				'type' => 'remote',
				'_url' => 'http://docs.aa-team.com/woocommerce-amazon-affiliates/documentation/synchronization/',
				'url' => 'http://docs.aa-team.com/woocommerce-amazon-affiliates/documentation/synchronization-log/'
			),
			'description' => "Using custom cron jobs you can keep your products updated.",
			'module_init' => 'tail.php',
			'load_in' => array(
				'backend' => array(
					'admin.php?page=WooZone_synclog',
					'admin-ajax.php'
				),
				'frontend' => true
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