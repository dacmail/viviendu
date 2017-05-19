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
		'price_select' => array(
			'version' => '1.0',
			'menu' => array(
				'order' => 4,
				'show_in_menu' => false,
				'title' => 'Price select',
				'icon' => 'images/16.png'
			),
			/*'in_dashboard' => array(
				'icon' 	=> 'images/32.png',
				'url'	=> admin_url("admin.php?page=WooZone_price_select")
			),*/
			'help' => array(
				'type' => 'remote',
				'url' => 'http://docs.aa-team.com/woocommerce-amazon-affiliates/documentation/price_select/'
			),
			'description' => "With this module you can manually select amazon products price.",
			'module_init' => 'init.php',
			'load_in' => array(
				'backend' => array(
					'post.php',
					'post-new.php',
					'admin-ajax.php',
					array('edit\.php\?(.*)post_type\=product')
				),
				'frontend' => true
			),
			'javascript' => array(
				'admin',
				'hashchange',
				'tipsy',
				'thickbox'
			),
			'css' => array(
				'admin',
				'tipsy'
			)
		)
	)
);