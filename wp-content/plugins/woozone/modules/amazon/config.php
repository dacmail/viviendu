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
		'amazon' => array(
			'version' => '1.0',
			'menu' => array(
				'order' => 3,
				'title' => 'Amazon config',
				'icon' => 'amazon'
			),
			'in_dashboard' => array(
				'icon' 	=> 'images/32.png',
				'url'	=> admin_url("admin.php?page=WooZone#!/amazon")
			),
			'description' => "Amazon configuration - mandatory fields - Amazon Secret Key, Access Key ID and Affiliate ID.",
			'help' => array(
				'type' => 'remote',
				'url' => 'http://docs.aa-team.com/woocommerce-amazon-affiliates/documentation/amazon-config/'
			),
			'module_init' => 'init.php',
			'load_in' => array(
				'backend' => array(
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