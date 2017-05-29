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
		'product_in_post' => array( 
			'version' => '1.0',
			'menu' => array(
				'order' => 4,
				'show_in_menu' => false,
				'title' => 'Product in post',
				'icon' => 'images/16.png'
			),
			'help' => array(
				'type' => 'remote',
				'url' => 'http://docs.aa-team.com/woocommerce-amazon-affiliates/documentation/amazon-asin-grabber/'
			),
			'description' => "With this module you can insert Amazon products inside your posts.",
			'module_init' => 'init.php',
			'load_in' => array(
				'backend' => array(
					'post.php',
					'post-new.php',
					'admin-ajax.php'
				),
				'frontend' => true
			),
			'javascript' => array(
				'admin',
				'download_asset',
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