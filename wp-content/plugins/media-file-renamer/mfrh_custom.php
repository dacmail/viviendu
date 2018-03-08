<?php

// EXAMPLES TO USE ACTIONS AND FILTERS
// For help about this file, check:
// http://meowapps.com/media-file-renamer/faq/


// HANDLE THE RENAMING
// $new is the proposed filename by Media File Renamer (without extension)
// $old is the current filename (without extension)
// $post is the attachment/media
// return: your ideal filename
// =============================================================================
// add_filter( 'mfrh_new_filename', 'add_hello_in_front_of_filenames', 10, 3 );
// function add_hello_in_front_of_filenames( $new, $old, $post ) {
//   return $new . "-offbeat";
// }

// REPLACE CHARACTER/STRING IN THE FILENAMES
// =============================================================================
// add_filter( 'mfrh_replace_rules', 'replace_s_by_z', 10, 1 );
//
// function replace_s_by_z( $rules ) {
//   $rules['s'] = 'z';
//   return $rules;
// }

// DO SOMETHING (UPDATES FOR INSTANCE) WHEN THE NEW URL IS READY
// =============================================================================
// add_action( 'mfrh_url_renamed', 'url_of_media_was_modified', 10, 3 );
//
// function url_of_media_was_modified( $post, $orig_image_url, $new_image_url ) {
//   global $wpdb;
//   $query = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = REPLACE(meta_value, '%s', '%s');", $orig_image_url, $new_image_url );
//   $query_revert = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = REPLACE(meta_value, '%s', '%s');", $new_image_url, $orig_image_url );
//   $wpdb->query( $query );
//   $this->log_sql( $query, $query_revert );
//   $this->log( "Metadata like $orig_image_url were replaced by $new_image_url." );
// }

// DO SOMETHING (UPDATES FOR INSTANCE) WHEN THE FILE IS READY
// =============================================================================
// add_action( 'mfrh_media_renamed', 'filepath_of_media_was_modified', 10, 3 );
//
// function filepath_of_media_was_modified( $post, $orig_image_url, $new_image_url ) {
//
// }
// =============================================================================

// Beaver Builder: Update the Metadata, clear the cache
// https://www.wpbeaverbuilder.com/frequently-asked-questions/

if ( class_exists( 'FLBuilderModel' ) )
  add_action( 'mfrh_url_renamed', 'mfrh_beaver_builder', 10, 3 );

function mfrh_beaver_builder( $post, $orig_image_url, $new_image_url ) {
  global $wpdb;
  $query = $wpdb->prepare( "UPDATE $wpdb->postmeta 
    SET meta_value = REPLACE(meta_value, 's:%d:\"$orig_image_url', 's:%d:\"$new_image_url')
    WHERE meta_key = '_fl_builder_data' 
    OR meta_key = '_fl_builder_draft'", 
    strlen( $orig_image_url ), strlen( $new_image_url ) );
  $query_revert = $wpdb->prepare( "UPDATE $wpdb->postmeta 
    SET meta_value = REPLACE(meta_value, 's:%d:\"$new_image_url', 's:%d:\"$orig_image_url')
    WHERE meta_key = '_fl_builder_data' 
    OR meta_key = '_fl_builder_draft'",
    strlen( $new_image_url ), strlen( $orig_image_url ) );
  $wpdb->query( $query );
  global $mfrh_core;
  $mfrh_core->log_sql( $query, $query_revert );
  $mfrh_core->log( "Beaver Metadata like $orig_image_url was replaced by $new_image_url." );

  // Clear cache
  $uploads = wp_upload_dir();
  $cache = trailingslashit( $uploads['basedir'] ) . 'bb-plugin';
  if ( file_exists( $cache ) )
    Meow_MFRH_Core::rmdir_recursive( $cache );
  else {
    $cache = trailingslashit( $uploads['basedir'] ) . 'fl-builder';
    if ( file_exists( $cache ) )
      Meow_MFRH_Core::rmdir_recursive( $cache );
  }
}

?>
