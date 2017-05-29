<?php

global $WooZone;

function __WooZone_report_recurrency() {
    global $WooZone;
    $recurrency = array(
        12      => __('Every 12 hours', $WooZone->localizationName),
        24      => __('Every single day', $WooZone->localizationName),
        48      => __('Every 2 days', $WooZone->localizationName),
        72      => __('Every 3 days', $WooZone->localizationName),
        96      => __('Every 4 days', $WooZone->localizationName),
        120     => __('Every 5 days', $WooZone->localizationName),
        144     => __('Every 6 days', $WooZone->localizationName),
        168     => __('Every 1 week', $WooZone->localizationName),
        336     => __('Every 2 weeks', $WooZone->localizationName),
        504     => __('Every 3 weeks', $WooZone->localizationName),
        720     => __('Every 1 month', $WooZone->localizationName), // ~ 4 weeks + 2 days
    );
    return $recurrency;
}

function __WooZone_report_recurrency_html( $module, $action='default', $istab = '', $is_subtab='' ) {
    global $WooZone;
    
    $req['action'] = $action;
	
	$ss = get_option('WooZone_report', array());
	$ss = maybe_unserialize($ss);
	$ss = $ss !== false ? $ss : array();

	$module_ = '';
	if ( 'report|products_status' == $module ) {
    	$notifyStatus = get_option('WooZone_report_act', array());
		$module_ = '';
	}
	else if ( 'report|auto_import' == $module ) {
    	$notifyStatus = get_option('WooZone_ai_report_act', array());
		$module_ = '_ai';
	}
   	$recurrency_list = __WooZone_report_recurrency();

    if ( $req['action'] == 'getStatus' ) {
        if ( $notifyStatus === false || !isset($notifyStatus["report"]) ) {
            return '';
        }
        return $notifyStatus["report"]["html"];
    }

    $html = array();
    
    $vals = array('recurrency' => '24');
	if ( isset($ss["recurrency{$module_}"]) && !empty($ss["recurrency{$module_}"]) ) {
		$vals = array('recurrency' => $ss["recurrency{$module_}"]); // get from db
	}
    
    ob_start();
?>
<div class="WooZone-form-row WooZone-report-container <?php echo ($istab!='' ? ' '.$istab : ''); ?><?php echo ($is_subtab!='' ? ' '.$is_subtab : ''); ?> WooZone-mod-<?php echo $module_; ?>">

    <div class="WooZone-form-item large">
    <span class="formNote"><?php _e('report sending recurrency', 'WooZone'); ?></span>

    <span><?php _e('Recurrency:', 'WooZone'); ?></span>&nbsp;
    <select id="recurrency" name="recurrency<?php echo $module_; ?>" style="width: 180px;">
        <?php
            foreach ($recurrency_list as $kk => $vv){
                $vv = (string) $vv;
                echo '<option value="' . ( $kk ) . '" ' . ( $vals["recurrency"] == $kk ? 'selected="true"' : '' ) . '>' . ( $vv ) . '</option>';
            } 
        ?>
    </select>&nbsp;&nbsp;
    
    <input type="button" class="WooZone-form-button WooZone-form-button-info" style="width: 160px;" id="WooZone-report-now" value="<?php _e('Send Report NOW', 'WooZone'); ?>">
    <img id="ajaxLoading" src="<?php echo $WooZone->cfg['modules']['report']['folder_uri']; ?>/images/ajax-loader.gif" width="16" height="11" style="display:none; width:auto;"/>
    <span style="margin:0px 0px 0px 10px" class="response"><?php echo __WooZone_report_recurrency_html( $module, 'getStatus' ); ?></span>

    </div>
</div>
<?php
    $htmlRow = ob_get_contents();
    ob_end_clean();
    $html[] = $htmlRow;
    
    // view page button
    ob_start();
?>
    <script>
    (function($) {
        var ajaxurl = '<?php echo admin_url('admin-ajax.php');?>';
        
        $(document).ready(function() {
            $.post(ajaxurl, {
                'action'        : 'WooZone_report_settings',
                'subaction'     : 'getStatus',
                'module'		: '<?php echo $module; ?>',
                'module_'		: '<?php echo $module_; ?>'
            }, function(response) {

                var $box = $('.WooZone-report-container.WooZone-mod-<?php echo $module_; ?>'), $res = $box.find('.response');
                $res.html( response.html );
                if ( response.status == 'valid' )
                    return true;
                return false;
            }, 'json');
        });

        $("body").on("click", ".WooZone-mod-<?php echo $module_; ?> #WooZone-report-now", function(){
			$(this).hide();
			$('.WooZone-mod-<?php echo $module_; ?> #ajaxLoading').show();
			
            $.post(ajaxurl, {
                'action'        : 'WooZone_report_settings',
                'subaction'    : 'send_report',
                'module'		: '<?php echo $module; ?>',
                'module_'		: '<?php echo $module_; ?>'
            }, function(response) {
				$('.WooZone-mod-<?php echo $module_; ?> #ajaxLoading').hide();
				$('.WooZone-mod-<?php echo $module_; ?> #WooZone-report-now').show();
				
                var $box = $('.WooZone-report-container.WooZone-mod-<?php echo $module_; ?>'), $res = $box.find('.response');
                $res.html( response.html );
                if ( response.status == 'valid' )
                    return true;
                return false;
            }, 'json');
        });
    })(jQuery);
    </script>
<?php
    $__js = ob_get_contents();
    ob_end_clean();
    $html[] = $__js;

    return implode( "\n", $html );
}

echo json_encode(array(
    $tryed_module['db_alias'] => array(
        
        /* define the form_sizes  box */
        'report' => array(
            'title' => 'Woozone Report',
            'icon' => '{plugin_folder_uri}images/16.png',
            'size' => 'grid_4', // grid_1|grid_2|grid_3|grid_4
            'header' => true, // true|false
            'toggler' => false, // true|false
            'buttons' => true, // true|false
            'style' => 'panel', // panel|panel-widget
            
            // create the box elements array
            'elements' => array(
                
                '__help_report_prods_performance_sync' => array(
                    'type' => 'message',
                    'status' => 'info',
                    'html' => 'Products Performance & Sync Stats'
                ),
                
                '__report' => array(
                    'type' => 'html',
                    'html' => __WooZone_report_recurrency_html( 'report|products_status', 'default', '__tab1', '' )
                ),
                
                'email_subject' => array(
                    'type' => 'text',
                    'std' => 'WooZone Report - Products Performance and Sync',
                    'size' => 'large',
                    'force_width' => '500',
                    'title' => 'Email subject',
                    'desc' => 'email subject'
                ),
                
                'email_to' => array(
                    'type' => 'text',
                    'std' => '',
                    'size' => 'large',
                    'force_width' => '300',
                    'title' => 'Email to',
                    'desc' => 'email to address'
                ),
                
                '__help_report_auto_import' => array(
                    'type' => 'message',
                    'status' => 'info',
                    'html' => 'Auto Import Product: Queue & Search'
                ),
                
                '__report_ai' => array(
                    'type' => 'html',
                    'html' => __WooZone_report_recurrency_html( 'report|auto_import', 'default', '__tab1', '' )
                ),
                
                'email_subject_ai' => array(
                    'type' => 'text',
                    'std' => 'WooZone Report - Auto Import',
                    'size' => 'large',
                    'force_width' => '500',
                    'title' => 'Email subject',
                    'desc' => 'email subject'
                ),
                
                'email_to_ai' => array(
                    'type' => 'text',
                    'std' => '',
                    'size' => 'large',
                    'force_width' => '300',
                    'title' => 'Email to',
                    'desc' => 'email to address'
                )
            )
        )
    )
));