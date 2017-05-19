<?php
/*
* Define class
* Make sure you skip down to the end of this file, as there are a few
* lines of code that are very important.
*/
! defined( 'ABSPATH' ) and exit;

if(class_exists('WooZoneCronjobsPanel') != true) {

    class WooZoneCronjobsPanel {
        /*
        * Some required plugin information
        */
        const VERSION = '1.0';

        /*
        * Store some helpers config
        *
        */
        public $the_plugin = null;
        public $cfg = array();

        private $module_folder = '';
        private $module_folder_path = '';
        public $module  = array();
        
        static protected $_instance;
        
        private $plugin_settings = array();
        
        public $is_admin = false;
        
        public $alias = '';
        public $localizationName = '';
        
        static private $cron_config_alias = '';


        /*
        * Required __construct() function that initalizes the AA-Team Framework
        */
        public function __construct($cfg, $module=array())
        {
            global $WooZone;
            
            $this->the_plugin = $WooZone;
            $this->cfg = $cfg;

            $this->module_folder = $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'modules/cronjobs/';
            $this->module_folder_path = $this->the_plugin->cfg['paths']['plugin_dir_path'] . 'modules/cronjobs/';
            $this->module = $module;
            
            $this->alias = $this->the_plugin->alias;
            $this->localizationName = $this->the_plugin->localizationName;
 
            $this->is_admin = $this->the_plugin->is_admin;
            
            self::$cron_config_alias = $this->alias.'_cronjobs';
            
            // ajax helper
            // ...see also /utils/action_admin_ajax.php
        }
        
        /**
        * Singleton pattern
        *
        * @return WooZoneCronjobs Singleton instance
        */
        static public function getInstance()
        {
            if (!self::$_instance) {
                self::$_instance = new self;
            }

            return self::$_instance;
        }
        

        // test wp-cron status on your webiste by performing a test spawn (cached for 1 hour where success).
        public function test_cron_spawn( $cache = true ) {

            if ( defined('ALTERNATE_WP_CRON') && ALTERNATE_WP_CRON )
                return true;
    
            $cached_status = get_transient( 'WooZone-cronjobs-test-ok' );
    
            if ( $cache && $cached_status )
                return true;
    
            $doing_wp_cron = sprintf( '%.22F', microtime( true ) );
    
            $cron_request = apply_filters( 'cron_request', array(
                'url'  => site_url( 'wp-cron.php?doing_wp_cron=' . $doing_wp_cron ),
                'key'  => $doing_wp_cron,
                'args' => array(
                    'timeout'   => 3,
                    'blocking'  => true,
                    'sslverify' => apply_filters( 'https_local_ssl_verify', true )
                )
            ) );
    
            $cron_request['args']['blocking'] = true;
    
            $result = wp_remote_post( $cron_request['url'], $cron_request['args'] );
    
            if ( is_wp_error( $result ) ) {
                return $result;
            } else {
                set_transient( 'WooZone-cronjobs-test-ok', 1, 3600 );
                return true;
            }

        }
    
        // wp-cron status on your website
        public function show_cron_status() {
            $ret = '';

            $status = $this->test_cron_spawn();
    
            $__status = true;
            if ( is_wp_error( $status ) ) {
                $msg = sprintf( __( 'Issue encountered when trying to spawn a call to the WP-Cron system on your website. The WP-Cron jobs on your website may not work. The issue details: %s', 'WooZone' ), '<br /><strong>' . esc_html( $status->get_error_message() ) . '</strong>' );
                $__status = false;
            } else {
                $msg = sprintf( __( 'Successfully spawn a call to the WP-Cron system on your website. The WP-Cron jobs on your website seems to work fine.', 'WooZone' ) );
            }
            
            if (1) {
                ob_start();
                ?>
        <div class="WooZone-form-row">
            <div class="WooZone-message WooZone-<?php echo $__status ? 'success' : 'error'; ?>">
                <p><?php echo $msg; ?></p>
            </div>
        </div>
                <?php
                $ret = ob_get_contents();
                ob_end_clean();
            }
            return $ret;
        }

        public function show_timezone() {
            $ret = '';

            $time_format = 'Y-m-d H:i:s';
    
            $tzstring = get_option( 'timezone_string' );
            $current_offset = get_option( 'gmt_offset' );
    
            if ( $current_offset >= 0 )
                $current_offset = '+' . $current_offset;
    
            if ( '' === $tzstring )
                $tz = sprintf( 'UTC%s', $current_offset );
            else
                $tz = sprintf( '%s (UTC%s)', str_replace( '_', ' ', $tzstring ), $current_offset );

            $html = array();
            $html[] = sprintf( __( 'Local timezone is <strong>%s</strong>', 'WooZone' ), '<code>' . $tz . '</code>' );
            $html[] = '<span id="WooZone-utc-time">' . sprintf( __( 'UTC time is <strong>%s</strong>', 'WooZone' ), '<code>' . date_i18n( $time_format, false, true ) . '</code>' ) . '</span>';
            $html[] = '<span id="WooZone-local-time">' . sprintf( __( 'Local time is <strong>%s</strong>', 'WooZone' ), '<code>' . date_i18n( $time_format ) . '</code>' ) . '</span>';

            if (1) {
                $msg = implode(PHP_EOL, $html);
                ob_start();
                ?>
        <div class="WooZone-form-row">
            <div class="WooZone-message WooZone-success">
                <p><?php echo $msg; ?></p>
            </div>
        </div>
                <?php
                $ret = ob_get_contents();
                ob_end_clean();
            }
            return $ret;
        }

        public function get_cron_events() {
            $ret = array('status' => 'invalid', 'msg' => 'unknown error.');

            $crons  = _get_cron_array();
            $events = array();
    
            if ( empty( $crons ) ) {
                return array_merge($ret, array('msg' => __('You currently have no scheduled cron events.', 'WooZone')));
            }
    
            foreach( $crons as $time => $cron ) {
                if ( empty($cron) || !is_array($cron) ) continue 1;
                foreach( $cron as $hook => $dings) {
                    if ( empty($dings) || !is_array($dings) ) continue 1;
                    foreach( $dings as $sig => $data ) {
                        if ( empty($data) || !is_array($data) ) continue 1;
    
                        //$events["$hook-$sig"] = (object) array(
                        $events["$hook"] = (object) array(
                            'hook'     => $hook,
                            'time'     => $time,
                            'sig'      => $sig,
                            'args'     => $data['args'],
                            'schedule' => $data['schedule'],
                            'interval' => isset( $data['interval'] ) ? $data['interval'] : null,
                        );
                    }
                }
            }
            return array_merge($ret, array(
                'status'            => 'valid',
                'msg'               => sprintf( __('You have %s scheduled cron events.', 'WooZone'), count($events) ),
                'events'            => $events,
            ));
        }

        private function default_cron_obj( $hook ) {
            return (object) array(
                'hook'     => $hook,
                'time'     => '',
                'sig'      => '',
                'args'     => '',
                'schedule' => '',
                'interval' => '',
            );
        }

        public function get_cron_events_hooks( $events ) {
            $ret = array();
            //foreach( (array) $events as $id=>$event ) {
            //    $hook = $event->hook;
            //    $ret["$id"] = $hook;
            //}
            $ret = array_keys( $events );
            return $ret;
        }

        public function get_plugin_cron_events( $retType='return' ) {
            $ret = array('status' => 'valid', 'html' => '');

            $events = $this->get_cron_events();
            
            if ( $events['status'] != 'valid' ) {
                ob_start();
                ?>
                    <tr><td colspan="7">
                        <div class="WooZone-message WooZone-error">
                            <p><?php echo $events['msg']; ?></p>
                        </div>
                    </td></tr>
                <?php
                $html = ob_get_contents();
                ob_end_clean();
                
                $ret = array_merge($ret, array(
                    'html'      => $html,
                ));
                if ( $retType == 'return' ) {
                    return $ret;
                } else {
                    die(json_encode($ret));
                }
            }

            $current_time = time();
            $time_format = 'Y-m-d H:i:s';
            
            $events = $events['events'];
            $events_hooks = $this->get_cron_events_hooks( $events );
            
            $plugin_crons = $this->get_plugin_crons();
            foreach ( (array) $plugin_crons as $cron_id => $cron ) {

                // make sure it is a valid cron hook
                if ( is_int($cron_id) ) {
                    unset($plugin_crons["$cron_id"]);
                    continue 1;
                }
                $_cron_id = $this->alias . '_' . $cron_id;

                $plugin_crons["$cron_id"]["__event"] = $this->default_cron_obj( $_cron_id );
                if ( in_array($_cron_id, $events_hooks) ) {
                    $plugin_crons["$cron_id"]["__event"] = $events["$_cron_id"];
                }                
            }
 
            $html = array();
            foreach( $plugin_crons as $cron_id => $cron ) {
                $event = $cron["__event"];

                $tr_css = empty($event->time) ? 'cut' : '';
                $tr_data = array();
                $tr_data[] = "data-cron_id='$cron_id'"; 
                $tr_data[] = $cron['is_active'] == 'yes' ? "data-new_status='no'" : "data-new_status='yes'";
                $tr_data = implode(' ', $tr_data);
                
                $args = empty( $event->args ) ? '<em>' . __( 'None', 'WooZone' ) . '</em>' : json_encode( $event->args );
                $next_run = empty($event->time) ? '<em>' . __( 'None', 'WooZone' ) . '</em>' : get_date_from_gmt(date('Y-m-d H:i:s', $event->time), $time_format)." (".$this->time_since($current_time, $event->time).")";
                $recurrence = empty($event->time) ? '<em>' . __( 'None', 'WooZone' ) . '</em>' :  ( $event->schedule ? $this->interval($event->interval) : __('Non-repeating', 'WooZone') );
                $status = $cron['status'];
                $duration = isset($cron['run_duration']) && $cron['run_duration'] != '' ? sprintf( __('%s seconds', 'WooZone'), $cron['run_duration'] ) : '';
                
                $buttons = '<input type="button" class="WooZone-form-button ' . ($cron['is_active'] == 'yes' ? 'WooZone-form-button-danger' : 'WooZone-form-button-success') . '" value="' . ($cron['is_active'] == 'yes' ? __( 'Clear', 'WooZone' ) : __( 'Activate', 'WooZone' )) . '" style="width:122px">';

                $html[] = "<tr class='$tr_css' $tr_data>";
                $html[] =   "<td>".( $event->hook )."</td>";
                $html[] =   "<td>".( $args )."</td>";
                $html[] =   "<td>".( $next_run )."</td>";
                $html[] =   "<td>".( $recurrence )."</td>";
                $html[] =   "<td>".( $status )."</td>";
                $html[] =   "<td>".( $duration )."</td>";
                $html[] =   "<td>".( $buttons )."</td>";
                $html[] = "</tr>";
            }

            $ret = array_merge($ret, array(
                'status'    => 'valid',
                'html'      => implode(PHP_EOL, $html),
                'nb'        => count($plugin_crons),
            ));
            if ( $retType == 'return' ) {
                return $ret;
            } else {
                die(json_encode($ret));
            }
        }

        public function get_plugin_crons() {
            // Initialize the WooZoneCronjobs core class
            require_once( $this->the_plugin->cfg['paths']['plugin_dir_path'] . '/modules/cronjobs/cronjobs.core.php' );
            $cronObj = new WooZoneCronjobs($this->the_plugin);
            
            return $cronObj->get_config();
        }


        public function printListInterface ()
        {
            global $WooZone;

            $amazon_settings = $WooZone->getAllSettings('array', 'amazon');
                
            $html = array();
            $html[] = '<style type="text/css">#WooZone-amazonDebug { display: block } </style>';
            
            $html[] = '<script type="text/javascript" id="WooZone-cronjobs-js" src="' . ( $this->module['folder_uri'] ) . 'app.cronjobs.js" ></script>';
            $html[] = "<link rel='stylesheet' id='WooZone-cronjobs-css' href='" . ( $this->module['folder_uri'] ) . "app.cronjobs.css" . '?'.time() . "' type='text/css' media='all' />";

            ob_start();
        ?>

<?php /*<div class="WooZone-form-row">
    <div class="WooZone-form-item large">
        <span class="formNote"><?php _e('Cronjobs setted by our plugin.', 'WooZone'); ?></span>
    </div>
</div>*/ ?>
<?php
    //_e('Sync settings saved successfully.', $this->the_plugin->localizationName);
        
    $website_cron_status = $this->show_cron_status();
    if ( !empty($website_cron_status) ) {
        echo $website_cron_status;
    }
    
    $website_show_timezone = $this->show_timezone();
    if ( !empty($website_show_timezone) ) {
        echo $website_show_timezone;
    }
?>
<div class="WooZone-form-row WooZone-cj-list">

    <!-- Main loading box -->
    <div id="WooZone-main-loading">
        <div id="WooZone-loading-overlay"></div>
        <div id="WooZone-loading-box">
            <div class="WooZone-loading-text"><?php _e('Loading', $this->the_plugin->localizationName);?></div>
            <div class="WooZone-meter WooZone-animate" style="width:86%; margin: 34px 0px 0px 7%;"><span style="width:100%"></span></div>
        </div>
    </div>

    <div id="WooZone-cj-reload">
        <input type="button" class="WooZone-form-button WooZone-form-button-info" value="<?php _e( 'Reload cronjobs list', 'WooZone' ); ?>" style="width:182px">
    </div>
    <table id="WooZone-cj-table">
        <thead>
            <tr>
                <th><?php _e('Hook Name', 'WooZone'); ?></th>
                <th><?php _e('Arguments', 'WooZone'); ?></th>
                <th><?php _e('Next Run', 'WooZone'); ?></th>
                <th><?php _e('Recurrence', 'WooZone'); ?></th>
                <th><?php _e('Last status', 'WooZone'); ?></th>
                <th><?php _e('Last duration', 'WooZone'); ?></th>
                <th><?php _e('Action', 'WooZone'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php 
                $cron_events = $this->get_plugin_cron_events('return');
                echo $cron_events['html'];
            ?>
        </tbody>
    </table>
</div>

<div class="WooZone-form-row WooZone-cj-help">
    <?php
    //$cron_url = $this->the_plugin->cfg["paths"]["plugin_dir_url"] . 'do-cron.php';
    $cron_url = site_url( 'wp-cron.php?doing_wp_cron' );
    echo '<h1>How to Replace WordPress Cron With A Real Cron Job (Optional)</h1>

    <p>WordPress comes with its own cron job that allows you to schedule your posts and events. However, in many situations, the WP-Cron is not working well and leads to posts missed their publication schedule and/or scheduled events not executed.<br>
    <span id="more-74"></span><br>
    To understand why this happen, we need to know that the WP-Cron is not a real cron job. It is in fact a virtual cron that only works when a page is loaded. In short, when a page is requested on the frontend/backend, WordPress will first load WP-Cron, follow by the necessary page to display to your reader. The loaded WP-Cron will then check the database to see if there is any thing that needs to be done.</p>
    <p>Reasons for WP-Cron to fail could be due to:</p>
    <ul>
        <li>DNS issue in the server.</li>
        <li>Plugins conflict</li>
        <li>Heavy load in the server which results in WP-Cron not executed fully</li>
        <li>WordPress bug</li>
        <li>Using of cache plugins that prevent the WP-Cron from loading</li>
        <li>And many other reasons</li>
    </ul>
    <p>There are many ways to solve the WP-Cron issue, but the one that I am going to propose here is to disable the virtual WP-Cron and use a real cron job instead.</p>
    <h3>Why use a real cron job?</h3>
    <p>By using a real cron job, you can be sure that all your scheduled items are executed. For popular blogs with high traffic, using a real cron job can also reduce the server bandwidth and reduce the chances of your server crashing, especially when you are experiencing Digg/Slashdot effect.</p>
    <h3>Scheduling a real cron job</h3>
    <p>To configure a real cron job, you will need access to your cPanel or Admin panel (we will be using cPanel in this tutorial).</p>
    <p>1. Log into your cPanel.</p>
    <p>2. Scroll down the list of applications until you see the “<em>cron jobs</em>” link. Click on it.</p>
    <p><img width="510" height="192" class="aligncenter size-full wp-image-81" alt="wpcron-cpanel" src="{plugin_folder_uri}images/wpcron-cpanel.png"></p>
    <p>3. Under the <em>Add New Cron Job</em> section, choose the interval that you want it to run the cron job. I have set it to run every 15minutes, but you can change it according to your liking.</p>
    <p><img width="470" height="331" class="aligncenter size-full wp-image-82" alt="wpcron-add-new-cron-job" src="{plugin_folder_uri}/images/wpcron-add-new-cron-job.png"></p>
    <p>4. In the Command field, enter the following:</p>

    <div class="wp_syntax"><div class="code"><pre style="font-family:monospace;" class="bash"><span style="color: #c20cb9; font-weight: bold;">wget</span> <span style="color: #660033;">-q</span> <span style="color: #660033;">-O</span> - </span>' . $cron_url . ' <span style="color: #000000; font-weight: bold;">&gt;/</span>dev<span style="color: #000000; font-weight: bold;">/</span>null <span style="color: #000000;">2</span><span style="color: #000000; font-weight: bold;">&gt;&amp;</span><span style="color: #000000;">1</span></pre></div></div>

    <p>5. Click the “Add New Cron Job” button. You should now see a message like this:</p>
    <p><img width="577" height="139" class="aligncenter size-full wp-image-83" alt="wpcron-current-cron-job" src="{plugin_folder_uri}/images/wpcron-current-cron-job.png"></p>
    <p>6. Next, using a FTP program, connect to your server and download the <code>wp-config.php</code> file.</p>
    <p>7. Open the <code>wp-config.php</code> file with a text editor and paste the following line:</p>

    <div class="wp_syntax"><div class="code"><pre style="font-family:monospace;" class="php"><span style="color: #990000;">define</span><span style="color: #009900;">(</span><span style="color: #0000ff;">\'DISABLE_WP_CRON\'</span><span style="color: #339933;">,</span> <span style="color: #009900; font-weight: bold;">true</span><span style="color: #009900;">)</span><span style="color: #339933;">;</span></pre></div></div>

    <p>8. Save and upload (and replace) this file back to the server. This will disable WordPress internal cron job.</p>
    <p>That’s it.</p>


    <a href="http://wpdailybits.com/blog/replace-wordpress-cron-with-real-cron-job/74"> Credits </a>';
    ?>
</div>

        <?php
            $html[] = ob_get_clean();
            return implode(PHP_EOL, $html);
        }


        /**
         * Ajax requests
         */
        public function ajax_request()
        {
            global $wpdb;
            $request = array(
                'action'                        => isset($_REQUEST['subaction']) ? $_REQUEST['subaction'] : '',
                //'sync_fields'                 => isset($_REQUEST['sync_fields']) ? $_REQUEST['sync_fields'] : array(),
                
                'cron_id'                       => isset($_REQUEST['cron_id']) ? $_REQUEST['cron_id'] : '',
                'new_status'                    => isset($_REQUEST['new_status']) ? $_REQUEST['new_status'] : '',
            );
            $request['new_status'] = in_array($request['new_status'], array('no', 'yes')) ? $request['new_status'] : 'no';
            extract($request);
            
            $ret = array(
                'status'        => 'invalid',
                'html'          => '<tr><td colspan="7">
                        <div class="WooZone-message WooZone-error">
                            <p>' . __('Invalid action!', $this->the_plugin->localizationName) . '</p>
                        </div>
                    </td></tr>',
            );
            
            if ( empty($action) || !in_array($action, array('load_cronjobs', 'cron_activate')) ) {
                die(json_encode($ret));
            }
   
            if ( $action == 'load_cronjobs' ) {
                
                // Initialize the WooZoneCronjobs core class
                require_once( $this->the_plugin->cfg['paths']['plugin_dir_path'] . '/modules/cronjobs/cronjobs.core.php' );
                $cronObj = new WooZoneCronjobs($this->the_plugin);
                
                // reinit cronjobs so we can retrieve new statuses!
                $cronObj->init();

                // reload cronjobs
                $productsList = $this->get_plugin_cron_events();

                $ret = array_merge($ret, array(
                    'status'    => 'valid',
                    'html'      => $productsList['html'],
                    'nb'        => $productsList['nb'],
                ));

            } else if ( $action == 'cron_activate' ) {

                // Initialize the WooZoneCronjobs core class
                require_once( $this->the_plugin->cfg['paths']['plugin_dir_path'] . '/modules/cronjobs/cronjobs.core.php' );
                $cronObj = new WooZoneCronjobs($this->the_plugin);
                
                $cronObj->set_cron($cron_id, array('is_active' => $new_status));
                
                // depedency
                $cron_details = $cronObj->get_cron($cron_id);
                $cron_depedency = isset($cron_details['depedency'], $cron_details['depedency']['is_active'])
                    ?  (array) $cron_details['depedency']['is_active'] : array();
                foreach ( $cron_depedency as $__cron_id => $__cron_status ) {
                    $cronObj->set_cron($__cron_id, array('is_active' => $new_status));
                }

                // reinit cronjobs so we can retrieve new statuses!
                $cronObj->init();

                // reload cronjobs
                $productsList = $this->get_plugin_cron_events();

                $ret = array_merge($ret, array(
                    'status'    => 'valid',
                    'html'      => $productsList['html'],
                    'nb'        => $productsList['nb'],
                ));

            }
            die(json_encode($ret));
        }


        /**
         * Pretty-prints the difference in two times.
         *
         * @param time $older_date
         * @param time $newer_date
         * @return string The pretty time_since value
         * @original link http://binarybonsai.com/code/timesince.txt
         */
        public function time_since($older_date, $newer_date) {
            return $this->interval( $newer_date - $older_date );
        }
        public function interval( $since ) {
            // array of time period chunks
            $chunks = array(
                array(60 * 60 * 24 * 365 , _n_noop('%s year', '%s years', 'WooZone')),
                array(60 * 60 * 24 * 30 , _n_noop('%s month', '%s months', 'WooZone')),
                array(60 * 60 * 24 * 7, _n_noop('%s week', '%s weeks', 'WooZone')),
                array(60 * 60 * 24 , _n_noop('%s day', '%s days', 'WooZone')),
                array(60 * 60 , _n_noop('%s hour', '%s hours', 'WooZone')),
                array(60 , _n_noop('%s minute', '%s minutes', 'WooZone')),
                array( 1 , _n_noop('%s second', '%s seconds', 'WooZone')),
            );
    
    
            if( $since <= 0 ) {
                return __('now', 'WooZone');
            }
    
            // we only want to output two chunks of time here, eg:
            // x years, xx months
            // x days, xx hours
            // so there's only two bits of calculation below:
    
            // step one: the first chunk
            for ($i = 0, $j = count($chunks); $i < $j; $i++)
                {
                $seconds = $chunks[$i][0];
                $name = $chunks[$i][1];
    
                // finding the biggest chunk (if the chunk fits, break)
                if (($count = floor($since / $seconds)) != 0)
                    {
                    break;
                    }
                }
    
            // set output var
            $output = sprintf(_n($name[0], $name[1], $count, 'WooZone'), $count);
    
            // step two: the second chunk
            if ($i + 1 < $j)
                {
                $seconds2 = $chunks[$i + 1][0];
                $name2 = $chunks[$i + 1][1];
    
                if (($count2 = floor(($since - ($seconds * $count)) / $seconds2)) != 0)
                    {
                    // add to output var
                    $output .= ' '.sprintf(_n($name2[0], $name2[1], $count2, 'WooZone'), $count2);
                    }
                }
    
            return $output;
        }
    }
}

// Initalize the your amazonDebug
//$WooZoneCronjobsPanel = new WooZoneCronjobsPanel($this->cfg, $module);
//$WooZoneCronjobsPanel = WooZoneCronjobsPanel::getInstance();