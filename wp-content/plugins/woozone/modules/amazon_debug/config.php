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
		'amazon_debug' => array(
			'version' => '1.0',
			'menu' => array(
				'order' => 4,
				'title' => 'Amazon Debug Console',
				'icon' => 'debug'
			),
			'in_dashboard' => array(
				'icon' 	=> 'images/32.png',
				'url'	=> admin_url("admin.php?page=WooZone#!/amazon_debug")
			),
			'help' => array(
				'type' => 'remote',
				'url' => 'http://docs.aa-team.com/woocommerce-amazon-affiliates/documentation/csv-bulk-import/'
			),
			'module_init' => 'ajax-request.php',
			'description' => "With this module you can get amazon response for a product by using ASIN code!",
			'load_in' => array(
				'backend' => array(
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
					You configured Amazon Debug incorrectly. See 
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