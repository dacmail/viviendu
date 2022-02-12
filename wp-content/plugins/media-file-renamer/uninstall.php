<?php
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
  die;
}

if ( !get_option( 'mfrh_clean_uninstall', false ) ) {
  return;
}

global $wpdb;
$options = $wpdb->get_results( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE 'mfrh_%'" );
foreach ( $options as $option ) {
  delete_option( $option->option_name );
}
