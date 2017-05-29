<?php
/**
* Return as json_encode
* http://www.aa-team.com
* ======================
*
* @author		Andrei Dinca, AA-Team
* @version		1.0
*/
global $WooZone;
$WooZoneDashboard = WooZoneDashboard::getInstance();
echo json_encode(array(
    $tryed_module['db_alias'] =
        'html_validation' => ( $WooZoneDashboard->getBoxes() )
));