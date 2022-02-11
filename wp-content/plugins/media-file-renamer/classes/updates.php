<?php

class Meow_MFRH_Updates {

  private $core = null;
	private $useless_types_conditions = array( 
		"post_status != 'trash'",
		"post_type != 'attachment'",
		"post_type NOT LIKE '%acf-%'",
		"post_type NOT LIKE '%edd_%'",
		"post_type != 'shop_order'",
		"post_type != 'shop_order_refund'",
		"post_type != 'nav_menu_item'",
		"post_type != 'revision'",
		"post_type != 'auto-draft'"
	);

	public function __construct( $core ) {
    $this->core = $core;

		$this->init_actions();

		// Support for WPML
		if ( function_exists( 'icl_object_id' ) )
			require( 'plugins/wpml.php' );
		// Support for Beaver Builder
		if ( class_exists( 'FLBuilderModel' ) )
			require( 'plugins/beaverbuilder.php' );
	}

	function init_actions() {
		add_action( 'mfrh_media_renamed', array( $this, 'action_update_media_file_references' ), 10, 3 );

		if ( get_option( "mfrh_update_posts", true ) )
			add_action( 'mfrh_url_renamed', array( $this, 'action_update_posts' ), 10, 3 );
		if ( get_option( "mfrh_update_postmeta", true ) )
			add_action( 'mfrh_url_renamed', array( $this, 'action_update_postmeta' ), 10, 3 );
		if ( get_option( "mfrh_rename_guid" ) )
			add_action( 'mfrh_media_renamed', array( $this, 'action_rename_guid' ), 10, 4 );
	}

	// Mass update of all the meta with the new filenames
	function action_update_postmeta( $post, $orig_image_url, $new_image_url ) {
		global $wpdb;

		$query = $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta 
			WHERE meta_value LIKE '%s'
			AND meta_key <> '_original_filename'
			AND (TRIM(meta_value) = '%s'
			OR TRIM(meta_value) = '%s'
			)", $new_image_url, $orig_image_url, str_replace( ' ', '%20', $orig_image_url ) );
		$ids = $wpdb->get_col( $query );
		if ( empty( $ids ) ) {
			return array();
		}

		// Prepare SQL (WHERE IN)
		$ids_to_update = array_map(function( $id ) { return "'" . esc_sql( $id ) . "'"; }, $ids );
		$ids_to_update = implode(',', $ids_to_update);

		// Execute updates
		$query = $wpdb->prepare( "UPDATE $wpdb->posts 
			SET meta_value = %s
			WHERE ID IN (" . $ids_to_update . ")", $new_image_url );
		$wpdb->query( $query );

		// Reverse updates & log
		$query_revert = $wpdb->prepare( "UPDATE $wpdb->postmeta 
			SET meta_value = '%s'
			WHERE ID IN (" . $ids_to_update . ")", $new_image_url );
		$this->core->log_sql( $query, $query_revert );

		// Reset meta cache
		update_meta_cache( 'post', $ids );

		$this->core->log( "🚀 Rewrite meta $orig_image_url ➡️ $new_image_url" );
	}

	// Mass update of all the articles with the new filenames
	function action_update_posts( $post, $orig_image_url, $new_image_url ) {
		$ids = $this->bulk_rename_content( $orig_image_url, $new_image_url );
		$this->core->log( "🚀 Rewrite content $orig_image_url ➡️ $new_image_url" );
		$more_ids = $this->bulk_rename_excerpts( $orig_image_url, $new_image_url );
		$this->core->log( "🚀 Rewrite excerpts $orig_image_url ➡️ $new_image_url" );

		// Reset post cache
		if ( !empty( $ids ) && !empty( $more_ids ) ) {
			array_walk( array_merge( $ids, $more_ids ), 'clean_post_cache' );
		}
  }

	function bulk_rename_content( $orig_image_url, $new_image_url ) {
		global $wpdb;

		// Conditions to avoid useless posts (which aren't related to content)
		$sql_conditions = implode( ' AND ', $this->useless_types_conditions );
		
		// Get the IDs that require an update
		$query = $wpdb->prepare( "SELECT ID FROM $wpdb->posts 
			WHERE post_content LIKE '%s'
			AND $sql_conditions", '%' . $orig_image_url . '%' );
		$ids = $wpdb->get_col( $query );
		if ( empty( $ids ) ) {
			return array();
		}

		// Prepare SQL (WHERE IN)
		$ids_to_update = array_map(function( $id ) { return "'" . esc_sql( $id ) . "'"; }, $ids );
		$ids_to_update = implode(',', $ids_to_update);

		// Execute updates
		$query = $wpdb->prepare( "UPDATE $wpdb->posts 
			SET post_content = REPLACE(post_content, '%s', '%s')
			WHERE ID IN (" . $ids_to_update . ")", $orig_image_url, $new_image_url );
		$wpdb->query( $query );

		// Reverse updates & log
		$query_revert = $wpdb->prepare( "UPDATE $wpdb->posts 
			SET post_content = REPLACE(post_content, '%s', '%s')
			WHERE ID IN (" . $ids_to_update . ")", $orig_image_url, $new_image_url );
		$this->core->log_sql( $query, $query_revert );

		return $ids;
	}

	function bulk_rename_excerpts( $orig_image_url, $new_image_url ) {
		global $wpdb;

		// Conditions to avoid useless posts (which aren't related to content)
		$sql_conditions = implode( ' AND ', $this->useless_types_conditions );
		
		// Get the IDs that require an update
		$query = $wpdb->prepare( "SELECT ID FROM $wpdb->posts 
			WHERE post_excerpt LIKE '%s'
			AND $sql_conditions", '%' . $orig_image_url . '%' );
		$ids = $wpdb->get_col( $query );
		if ( empty( $ids ) ) {
			return array();
		}

		// Prepare SQL (WHERE IN)
		$ids_to_update = array_map(function( $id ) { return "'" . esc_sql( $id ) . "'"; }, $ids );
		$ids_to_update = implode(',', $ids_to_update);

		// Execute updates
		$query = $wpdb->prepare( "UPDATE $wpdb->posts 
			SET post_excerpt = REPLACE(post_excerpt, '%s', '%s')
			WHERE ID IN (" . $ids_to_update . ")", $orig_image_url, $new_image_url );
		$wpdb->query( $query );

		// Reverse updates & log
		$query_revert = $wpdb->prepare( "UPDATE $wpdb->posts 
			SET post_excerpt = REPLACE(post_excerpt, '%s', '%s')
			WHERE ID IN (" . $ids_to_update . ")", $orig_image_url, $new_image_url );
		$this->core->log_sql( $query, $query_revert );

		return $ids;
	}

  // The GUID should never be updated but... this will if the option is checked.
	// [TigrouMeow] It the recent version of WordPress, the GUID is not part of the $post (even though it is in database)
	// Explanation: http://pods.io/2013/07/17/dont-use-the-guid-field-ever-ever-ever/
	function action_rename_guid( $post, $old_filepath, $new_filepath, $undo = false ) {
		$meta = wp_get_attachment_metadata( $post['ID'] );
		$old_guid = get_the_guid( $post['ID'] );
		if ( $meta )
			$new_filepath = wp_get_attachment_url( $post['ID'] );
		global $wpdb;
		$query = $wpdb->prepare( "UPDATE $wpdb->posts SET guid = '%s' WHERE ID = '%d'", $new_filepath,  $post['ID'] );
		$query_revert = $wpdb->prepare( "UPDATE $wpdb->posts SET guid = '%s' WHERE ID = '%d'", $old_guid,  $post['ID'] );
		$this->core->log_sql( $query, $query_revert );
		$wpdb->query( $query );
		clean_post_cache( $post['ID'] );
		$this->core->log( "🚀 GUID $old_guid ➡️ $new_filepath." );
  }

	/**
	 * Updates renamed file references of all the duplicated media entries
	 * @param array $post
	 * @param string $old_filepath
	 * @param string $new_filepath
	 */
	function action_update_media_file_references( $post, $old_filepath, $new_filepath ) {
		global $wpdb;

		// Source of sync on 'posts' table
		$id = $post['ID'];
		$src = $wpdb->get_row( "SELECT post_mime_type FROM {$wpdb->posts} WHERE ID = {$id}" );

		// Source of sync on 'postmeta' table
		$meta = array ( // Meta keys to sync
			'_wp_attached_file' => null,
			'_wp_attachment_metadata' => null
		);
		foreach ( array_keys( $meta ) as $i ) {
			$meta[$i] = $wpdb->get_var( "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = {$id} AND meta_key = '{$i}'" );
		}

		// Sync posts sharing the same attachment file
		$dest = $this->core->get_posts_by_attached_file( $old_filepath, $id );
		foreach ( $dest as $item ) {
			if ( get_post_type( $item ) != 'attachment' ) continue;

			// Set it as manual-renamed to avoid being marked as an issue
			add_post_meta( $item, '_manual_file_renaming', true, true );

			// Sync on 'posts' table
			$wpdb->update( $wpdb->posts, array ( // Data
				'post_mime_type' => $src->post_mime_type
			), array ( // WHERE
				'ID' => $item
			) );

			// Sync on 'postmeta' table
			foreach ( $meta as $j => $jtem ) {
				$wpdb->update( $wpdb->postmeta, array ( // Data
					'meta_value' => $jtem
				), array ( // WHERE
					'post_id'  => $item, // AND
					'meta_key' => $j
				) );
			}
		}
	}
}