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
		'stats_prod' => array(
			'version' => '1.0',
			'menu' => array(
				'order' => 5,
				'show_in_menu' => true,
				'title' => 'Products Stats',
				'icon' => 'products_statistics'
			),
			'in_dashboard' => array(
				'icon' 	=> 'products_statistics',
				'url'	=> admin_url("admin.php?page=WooZone_stats_prod")
			),
			'help' => array(
				'type' => 'remote',
				'url' => 'http://docs.aa-team.com/woocommerce-amazon-affiliates/documentation/products-stats/'
			),
			'description' => "Useful stats about your amazon products!",
			'module_init' => 'init.php',
			'load_in' => array(
				'backend' => array(
					'admin.php?page=WooZone_stats_prod',
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
