<?php
/**
 * Config file, return as json_encode
 * http://www.aa-team.com
 * =======================
 *
 * @author		Andrei Dinca, AA-Team
 * @version		1.0
 */
global $WooZone;
echo json_encode(
	array(
		'asin_grabber' => array(
			'version' => '1.0',
			'menu' => array(
				'order' => 4,
				'show_in_menu' => false,
				'title' => 'ASIN Grabber',
				'icon' => 'asin_grabber'
			),
			'in_dashboard' => array(
				'icon' 	=> 'images/32.png',
				'url'	=> admin_url("admin.php?page=WooZone_asin_grabber")
			),
			'help' => array(
				'type' => 'remote',
				'url' => 'http://docs.aa-team.com/woocommerce-amazon-affiliates/documentation/amazon-asin-grabber/'
			),
			'description' => "With this module you can import hundreds of ASIN codes from Amazon pages like: Best Sellers, Most Wished, etc.",
			'module_init' => 'init.php',
			'load_in' => array(
				'backend' => array(
					'admin.php?page=WooZone_assets_download',
					'admin.php?page=WooZone_asin_grabber',
					'admin-ajax.php'
				),
				'frontend' => false
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
			),
			'errors' => array(
				1 => __('
					You configured ASIN Grabber incorrectly. See 
					' . ( is_object($WooZone) ? $WooZone->convert_to_button ( array(
						'color' => 'white_blue WooZone-show-docs-shortcut',
						'url' => 'javascript: void(0)',
						'title' => 'here'
					) ) : '' ) . ' for more details on fixing it. <br />
					Setup the Amazon config mandatory settings ( Access Key ID, Secret Access Key, Main Affiliate ID ) 
					' . ( is_object($WooZone) ? $WooZone->convert_to_button ( array(
						'color' => 'white_blue',
						'url' => admin_url( 'admin.php?page=WooZone#!/amazon' ),
						'title' => 'here'
					) ) : '' ) . '
					', $WooZone->localizationName),
				2 => __('
					You don\'t have WooCommerce installed/activated! Please activate it:
					' . ( is_object($WooZone) ? $WooZone->convert_to_button ( array(
						'color' => 'white_blue',
						'url' => admin_url('plugin-install.php?tab=search&s=woocommerce&plugin-search-input=Search+Plugins'),
						'title' => 'NOW'
					) ) : '' ) . '
					', $WooZone->localizationName),
				3 => __('
					You don\'t have the SOAP library installed! Please activate it!
					', $WooZone->localizationName),
				4 => __('
					You don\'t have the cURL library installed! Please activate it!
					', $WooZone->localizationName)
			)
		)
	)
);