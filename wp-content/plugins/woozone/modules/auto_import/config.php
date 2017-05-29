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
		'auto_import' => array(
			'version' => '1.0',
			'menu' => array(
				'order' => 4,
				'show_in_menu' => false,
				'title' => 'Auto Import Products',
				'icon' => 'auto_import'
			),
			'in_dashboard' => array(
				//admin_url("admin.php?page=WooZone#!/auto_import")
				/*array(
					'title'	=> 'Auto Import Queue',
					'icon' 	=> 'images/32.png',
					'url'	=> 'admin.php?page=WooZone_auto_import_queue'
				),
				array(
					'title'	=> 'Auto Import Search',
					'icon' 	=> 'images/32.png',
					'url'	=> 'admin.php?page=WooZone_auto_import_search'
				)*/
				'icon' 	=> 'auto_import',
				'url'	=> admin_url("admin.php?page=WooZone_auto_import_queue")
			),
			'help' => array(
				'type' => 'remote',
				'url' => 'http://docs.aa-team.com/woocommerce-amazon-affiliates/documentation/amazon-asin-grabber/'
			),
			'description' => "With this module you can schedule automatic products fetch on certain intervals",
			'module_init' => 'init.php',
			'load_in' => array(
				'backend' => array(
					'admin.php?page=WooZone_auto_import_queue',
					'admin.php?page=WooZone_auto_import_search',
					'admin.php?page=WooZone_insane_import',
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
			),
            'errors' => array()
		)
	)
);