<?php
/*
Plugin Name: Media File Renamer
Plugin URI: https://meowapps.com
Description: Renames your media files for better SEO and a nicer filesystem (automatically or manually).
Version: 5.3.6
Author: Jordy Meow
Author URI: https://meowapps.com
Text Domain: media-file-renamer
Domain Path: /languages

Originally developed for two of my websites:
- Jordy Meow (https://offbeatjapan.org)
- Haikyo (https://haikyo.org)
*/

if ( !defined( 'MFRH_VERSION' ) ) {
  define( 'MFRH_VERSION', '5.3.6' );
  define( 'MFRH_PREFIX', 'mfrh' );
  define( 'MFRH_DOMAIN', 'media-file-renamer' );
  define( 'MFRH_ENTRY', __FILE__ );
  define( 'MFRH_PATH', dirname( __FILE__ ) );
  define( 'MFRH_URL', plugin_dir_url( __FILE__ ) );
}

require_once( 'classes/init.php');

?>