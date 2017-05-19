<?php
// soap
if (extension_loaded('soap')) {
?>
<div class="WooZone-message WooZone-success">
	SOAP extension installed on server
</div>
<?php
}else{
?>
<div class="WooZone-message WooZone-error">
	SOAP extension not installed on your server, please talk to your hosting company and they will install it for you.
</div>
<?php
}

// Woocommerce
if( class_exists( 'Woocommerce' ) ){
?>
<div class="WooZone-message WooZone-success">
	 WooCommerce plugin installed
</div>
<?php
}else{
?>
<div class="WooZone-message WooZone-error">
	WooCommerce plugin not installed, in order the product to work please <a href="https://www.woothemes.com/woocommerce/" traget="_blank">install WooCommerce wordpress plugin</a>.
</div>
<?php
}

// curl
if ( function_exists('curl_init') ) {
?>
<div class="WooZone-message WooZone-success">
	cURL extension installed on server
</div>
<?php
}else{
?>
<div class="WooZone-message WooZone-error">
	cURL extension not installed on your server, please talk to your hosting company and they will install it for you.
</div>
<?php
}
?>
<?php
