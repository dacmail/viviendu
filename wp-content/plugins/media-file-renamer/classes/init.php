<?php

if ( class_exists( 'MeowPro_MFRH_Core' ) && class_exists( 'Meow_MFRH_Core' ) ) {
	function mfrh_admin_notices() {
		echo '<div class="error"><p>Thanks for installing the Pro version of Media File Renamer :) However, the free version is still enabled. Please disable or uninstall it.</p></div>';
	}
	add_action( 'admin_notices', 'mfrh_admin_notices' );
	return;
}

spl_autoload_register(function ( $class ) {
  $necessary = true;
  $file = null;
  if ( strpos( $class, 'Meow_MFRH' ) !== false ) {
    $file = MFRH_PATH . '/classes/' . str_replace( 'meow_mfrh_', '', strtolower( $class ) ) . '.php';
  }
  else if ( strpos( $class, 'MeowCommon_Classes_' ) !== false ) {
    $file = MFRH_PATH . '/common/classes/' . str_replace( 'meowcommon_classes_', '', strtolower( $class ) ) . '.php';
  }
  else if ( strpos( $class, 'MeowCommon_' ) !== false ) {
    $file = MFRH_PATH . '/common/' . str_replace( 'meowcommon_', '', strtolower( $class ) ) . '.php';
  }
  else if ( strpos( $class, 'MeowPro_MFRH' ) !== false ) {
    $necessary = false;
    $file = MFRH_PATH . '/premium/' . str_replace( 'meowpro_mfrh_', '', strtolower( $class ) ) . '.php';
  }
  if ( $file ) {
    if ( !$necessary && !file_exists( $file ) ) {
      return;
    }
    require( $file );
  }
});

require_once( MFRH_PATH . '/classes/api.php');
require_once( MFRH_PATH . '/common/helpers.php');

// In admin or Rest API request (REQUEST URI begins with '/wp-json/')
if ( is_admin() || MeowCommon_Helpers::is_rest() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	global $mfrh_core;
	$mfrh_core = new Meow_MFRH_Core();
}

?>