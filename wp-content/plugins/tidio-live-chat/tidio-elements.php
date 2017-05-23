<?php

/**
 * Plugin Name: Tidio Chat
 * Plugin URI: http://www.tidiochat.com
 * Description: Tidio Live Chat - Live chat for your website. No logging in, no signing up - integrates with your website in less than 20 seconds.
 * Version: 3.0.2
 * Author: Tidio Ltd.
 * Author URI: http://www.tidiochat.com
 * License: GPL2
 */

class TidioLiveChat {

    private $scriptUrl = '//code.tidio.co/';
    private $tidioOne;

    public function __construct() {
        
        /* Before add link to menu - check is user trying to unninstal */
        if (is_admin() && !empty($_GET['tidio_one_clear_cache'])) {
            delete_option('tidio-one-public-key');
            delete_option('tidio-one-private-key');
        }
        
        add_action('admin_menu', array($this, 'addAdminMenuLink'));
        
        if(get_option('tidio-one-public-key')){
            add_action('admin_footer', array($this, 'adminJS'));
        }

        if (!is_admin()) {
            add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'));
        }else{            
            add_action('admin_enqueue_scripts', array($this, 'enqueueAdminScripts'));                 
        }       
        
        add_action('deactivate_' . plugin_basename(__FILE__), array($this, 'uninstall'));
        add_action('wp_ajax_tidio_chat_save_keys', array($this, 'ajaxTidioChatSaveKeys'));
        
        /* Ajax functions to set up existing tidio account  */
        add_action('wp_ajax_get_project_keys', array($this, 'ajaxGetProjectKeys'));
        add_action('wp_ajax_get_private_key', array($this, 'ajaxGetPrivateKey'));        

        if (!empty($_GET['tidio_chat_version'])) {
            echo '3.0.0';
            exit;
        }

        // WooCommerce Hooks - Active only after activiation by user, default is off
        if (get_option('tidio-one-woo-hooks-chat') && !class_exists('TidioOneApi')) {

            $tidioOneLibPath = plugin_dir_path(__FILE__) . 'classes/TidioOneApi.php';

            if (file_exists($tidioOneLibPath)) {

                include($tidioOneLibPath);

                $this->tidioOne = new TidioOneApi(self::getPublicKey());
                add_action('woocommerce_checkout_order_processed', array($this, 'wooPaymentCharged'));
                add_action('woocommerce_add_to_cart', array($this, 'wooAddToCart'), 10, 6);
                add_action('woocommerce_cart_item_removed', array($this, 'wooRemoveFromCart'), 10, 2);

                add_action('wp_head', array($this, 'wooAddScript'));
            }
        }

        // Activation by user process, have to use private key

        if (!empty($_GET['tidio_one_hooks_activiation']) && $_GET['tidio_one_hooks_activiation'] == self::getPrivateKey()) {
            if (!get_option('tidio-one-woo-hooks')) {
                update_option('tidio-one-woo-hooks', '1');
                update_option('tidio-one-woo-hooks-chat', '1');
                echo 'OK';
                exit;
            } else {
                echo 'SETTED';
                exit;
            }
        }
    }
    
    public function ajaxGetProjectKeys(){
        update_option('tidio-one-public-key', $_POST['public_key']);
        update_option('tidio-one-private-key', $_POST['private_key']);
        echo self::getRedirectUrl($_POST['private_key']);
        exit();
    }

    // Ajax - Create an new project

    public function ajaxTidioChatSaveKeys() {
        
        if (!is_admin()) {
            exit;
        }
        
        if (empty($_POST['private_key']) || empty($_POST['public_key'])) {
            exit;
        }

        update_option('tidio-one-public-key', $_POST['public_key']);
        update_option('tidio-one-private-key', $_POST['private_key']);

        echo '1';
        exit;
    }
    
    // Front End Scripts
    public function enqueueScripts() {
        wp_enqueue_script('tidio-chat', $this->scriptUrl . self::getPublicKey() . '.js', array(), '3.0.0', true);
    }
    
    // Admin scripts and style enquee
    public function enqueueAdminScripts(){
        wp_enqueue_script('tidio-chat-admin', plugins_url('media/js/options.js', __FILE__), array(), '3.0.0', true);
        wp_enqueue_style('tidio-chat-admin-style', plugins_url('media/css/options.css', __FILE__));
    }

    // Admin JavaScript
    public function adminJS() {
        $privateKey = self::getPrivateKey();
        $redirectUrl = '';

        if ($privateKey && $privateKey != 'false') {
            $redirectUrl = self::getRedirectUrl($privateKey);
        } else {
            $redirectUrl = admin_url('admin-ajax.php?action=tidio_chat_redirect');
        }

        echo "<script>jQuery('a[href=\"admin.php?page=tidio-chat\"]').attr('href', '" . $redirectUrl . "').attr('target', '_blank') </script>";
    }

    // Menu Pages

    public function addAdminMenuLink() {
        $optionPage = add_menu_page(
                'Tidio Chat', 'Tidio Chat', 'manage_options', 'tidio-chat', array($this, 'addAdminPage'), content_url() . '/plugins/tidio-live-chat/media/img/icon.png'
        );
    }

    public function addAdminPage() {
        // Set class property
        $dir = plugin_dir_path(__FILE__);
        include $dir . 'options.php';
    }

    // Uninstall

    public function uninstall() {
        delete_option('tidio-one-public-key');
        delete_option('tidio-one-private-key');
    }

    // Get Private Key

    public static function getPrivateKey() {
        self::syncPrivateKey();
        
        $privateKey = get_option('tidio-one-private-key');

        if ($privateKey) {
            return $privateKey;
        }
        
        try {
            $data = self::getContent(self::getAccessUrl());
        } catch(Exception $e){
            $data = null;
        }
        //
        if (!$data) {
            update_option('tidio-one-private-key', 'false');
            return false;
        }

        @$data = json_decode($data, true);
        if (!$data || !$data['status']) {
            update_option('tidio-one-private-key', 'false');
            return false;
        }

        update_option('tidio-one-private-key', $data['value']['private_key']);
        update_option('tidio-one-public-key', $data['value']['public_key']);

        return $data['value']['private_key'];
    }
    
    public static function getContent($url){
        
        if(function_exists('curl_version')){ // load trought curl
            $ch = curl_init();
         
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
         
            $data = curl_exec($ch);
            curl_close($ch);
         
            return $data;
        } else { // load trought file get contents
            return file_get_contents($url);
        }
            
    }

    // Sync private key with old version

    public static function syncPrivateKey() {
        if (get_option('tidio-one-public-key')) {
            return false;
        }       

        $publicKey = get_option('tidio-chat-external-public-key');
        $privateKey = get_option('tidio-chat-external-private-key');

        if (!$publicKey || !$privateKey) {
            return false;
        }

        // sync old variables with new one

        update_option('tidio-one-public-key', $publicKey);
        update_option('tidio-one-private-key', $privateKey);

        return true;
    }

    // Get Access Url

    public static function getAccessUrl() {

        return 'http://www.tidio.co/external/create?url=' . urlencode(site_url()) . '&platform=wordpress&email=' . urlencode(get_option('admin_email')) . '&_ip=' . $_SERVER['REMOTE_ADDR'];
    }

    public static function getRedirectUrl($privateKey) {

        return 'https://www.tidio.co/external/access?privateKey=' . $privateKey . '&app=chat';
    }
    
    public static function ajaxGetPrivateKey(){
        $privateKey = self::getPrivateKey();
        if(!$privateKey || $privateKey=='false'){
            echo 'error';
            exit();    
        }
        echo self::getRedirectUrl($privateKey);
        exit();
    }

    // Get Public Key

    public static function getPublicKey() {        
        $publicKey = get_option('tidio-one-public-key');

        if ($publicKey) {
            return $publicKey;
        }

        self::getPrivateKey();

        return get_option('tidio-one-public-key');
    }

    // WooCommerce Hooks - Thanks to this option, chat operator can see what user do while talking with him
    // Based on Tidio One API

    public function wooPaymentCharged($orderId) {

        $visitorId = $this->getVisitorId();

        if (!$visitorId) {
            return false;
        }

        $order = new WC_Order($orderId);
        $curreny = $order->get_order_currency();
        $amount = $order->get_total();
        $response = $this->tidioOne->request('api/track', array(
            'name' => 'payment charged',
            'visitorId' => $visitorId
        ));
    }

    public function wooAddToCart($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {

        $visitorId = $this->getVisitorId();

        if (!$visitorId) {
            return false;
        }

        $response = $this->tidioOne->request('api/track', array(
            'name' => 'add to cart',
            'visitorId' => $visitorId,
            'data' => array(
                '_product_id' => $product_id,
                '_product_name' => get_the_title($product_id),
                '_product_quantity' => $quantity,
                '_product_url' => get_permalink($product_id),
            )
        ));
    }

    public function wooRemoveFromCart($cart_item_key, $cart) {

        $visitorId = $this->getVisitorId();

        if (!$visitorId) {
            return false;
        }

        foreach ($cart->removed_cart_contents as $key => $removed) {
            $product_id = $removed['product_id'];
            $quantity = $removed['quantity'];
            $response = $this->tidioOne->request('api/track', array(
                'name' => 'remove from cart',
                'visitorId' => $visitorId,
                'data' => array(
                    '_product_id' => $product_id,
                    '_product_quantity' => $quantity,
                )
            ));
        }
    }

    public function wooAddScript() {
        echo '<script type="text/javascript"> document.tidioOneWooTrackingInside = 1; </script>';
    }


    private function getVisitorId() {

        if (empty($_COOKIE['_tidioOne_'])) {
            return null;
        }

        if (!function_exists('json_decode')) {
            return null;
        }

        $data = $_COOKIE['_tidioOne_'];

        $data = str_replace('\"', '"', $data);

        @$data = json_decode($data, true);

        if (!$data || empty($data['tidioOneVistiorId'])) {
            return null;
        }

        return $data['tidioOneVistiorId'][0];
    }

}

$tidioLiveChat = new TidioLiveChat();

