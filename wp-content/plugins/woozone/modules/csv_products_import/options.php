<?php
/**
 * Bulk Products Import module - return as json_encode
 * http://www.aa-team.com
 * =======================
 *
 * @author		Andrei Dinca, AA-Team
 * @version		0.1 - in development mode
 */ 
echo json_encode(
	array(
		$tryed_module['db_alias'] => array(
			/* define the form_messages box */
			'import_panel' => array(
				'title' 	=> 'Import products by ASIN code',
				'size' 		=> 'grid_4', // grid_1|grid_2|grid_3|grid_4
				'header' 	=> true, // true|false
				'toggler' 	=> false, // true|false
				'buttons' 	=> false, // true|false
				'style' 	=> 'panel', // panel|panel-widget
				
				'panel_setup_verification' => true,
				
				// create the box elements array
				'elements'	=> array(
					'help_required_fields' => array(
						'type' => 'message',
						'status' => 'danger',
						'html' => 'This module is deprecated since version 9.0. Please use <a href="' . admin_url( 'admin.php?page=WooZone_insane_import' ). '" class="' . ( WooZone()->alias ) . '-form-button-small ' . ( WooZone()->alias ) . '-form-button-danger ">Insane Import</a> module instead.'
					),
					array(
						'type' 		=> 'app',
						'path' 		=> '{plugin_folder_path}panel.php',
					)
				)
			)
		)
	)
);