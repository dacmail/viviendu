<?php

	add_action( 'added_post_meta', 'mfrh_wc_update_meta', 10, 4 );
	add_action( 'updated_post_meta', 'mfrh_wc_update_meta', 10, 4 );

	function mfrh_wc_update_meta( $meta_id, $post_id, $meta_key, $meta_value )
	{
		if ( '_product_image_gallery' == $meta_key ) {
			$ids = explode( ',', $meta_value );
			foreach ( $ids as $id ) {
				wp_update_post( array( 'ID' => $id, 'post_parent' => $post_id ) );
				global $mfrh_core;
				$mfrh_core->rename( $id );
			}
		}
	}

?>