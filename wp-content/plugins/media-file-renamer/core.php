<?php

class Meow_MFRH_Core {

	private $mfrh_admin = null;

	public function __construct( $mfrh_admin ) {
		$this->mfrh_admin = $mfrh_admin;
		add_action( 'plugins_loaded', array( $this, 'init' ) );
		add_action( 'plugins_loaded', array( $this, 'init_actions' ) );

		// Support for additional plugins
		add_action( 'wpml_loaded', array( $this, 'wpml_load' ) );
	}

	function init() {
		include( 'mfrh_custom.php' );
		include( 'api.php' );

		global $mfrh_version;
		load_plugin_textdomain( 'media-file-renamer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'wp_ajax_mfrh_rename_media', array( $this, 'wp_ajax_mfrh_rename_media' ) );
		add_action( 'wp_ajax_mfrh_undo_media', array( $this, 'wp_ajax_mfrh_undo_media' ) );
		add_filter( 'media_send_to_editor', array( $this, 'media_send_to_editor' ), 20, 3 );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		//add_action( 'edit_attachment', array( $this, 'edit_attachment' ) );
		//add_action( 'add_attachment', array( $this, 'add_attachment' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_rename_metabox' ) );
		add_filter( 'attachment_fields_to_save', array( $this, 'attachment_fields_to_save' ), 20, 2 );
		add_action( 'save_post', array( $this, 'save_post' ) );

		if ( get_option( 'mfrh_on_upload', false ) ) {
			add_filter( 'wp_handle_upload_prefilter', array( $this, 'wp_handle_upload_prefilter' ), 10, 2 );
		}

		// Column for Media Library
		$method = apply_filters( 'mfrh_method', 'media_title' );
		if ( $method != 'none' ) {
			add_filter( 'manage_media_columns', array( $this, 'add_media_columns' ) );
			add_action( 'manage_media_custom_column', array( $this, 'manage_media_custom_column' ), 10, 2 );
		}

		// Media Library Bulk Actions
		add_filter( 'bulk_actions-upload', array( $this, 'library_bulk_actions' ) );
		add_filter( 'handle_bulk_actions-upload', array( $this, 'library_bulk_actions_handler' ), 10, 3 );
	}

	/**
	 *
	 * ADDITIONAL PLUGINS
	 *
	 */

	function wpml_load() {
		require( 'plugins/wpml.php' );
	}

	/**
	 *
	 * ERROR/INFO MESSAGE HANDLING
	 *
	 */

	function admin_notices() {
		$screen = get_current_screen();
		if ( ( $screen->base == 'post' && $screen->post_type == 'attachment' ) ||
			( $screen->base == 'media' && isset( $_GET['attachment_id'] ) ) ) {
			$id = isset( $_GET['post'] ) ? $_GET['post'] : $_GET['attachment_id'];
			if ( $this->check_attachment( get_post( $id, ARRAY_A ), $output ) ) {
				if ( $output['desired_filename_exists'] ) {
					echo '<div class="error"><p>
						The file ' . $output['desired_filename'] . ' already exists. Please give a new title for this media.
					</p></div>';
				}
			}
			if ( $this->wpml_media_is_installed() && !$this->is_real_media( $id ) ) {
				echo '<div class="error"><p>
					This attachment seems to be a virtual copy (or translation). Media File Renamer will not make any modification from here.
				</p></div>';
			}
		}
	}

	/**
	 *
	 * TOOLS / HELPERS
	 *
	 */

	// Check if the file exists, if it is, return the real path for it
	// https://stackoverflow.com/questions/3964793/php-case-insensitive-version-of-file-exists
	static function sensitive_file_exists( $filename, $fullpath = true, $caseInsensitive = true ) {
		$output = false;
		$directoryName = dirname( $filename );
		$fileArray = glob( $directoryName . '/*', GLOB_NOSORT );
		$i = ( $caseInsensitive ) ? "i" : "";

		// Check if \ is in the string
		if ( preg_match( "/\\\|\//", $filename) ) {
			$array = preg_split("/\\\|\//", $filename);
			$filename = $array[count( $array ) -1];
		}
		// Compare filenames
		foreach ( $fileArray as $file ) {
			if ( preg_match( "/" . preg_quote( $filename ) . "/{$i}", $file ) ) {
				$output = $file;
				break;
			}
    }
		return $output;
	}

	static function rmdir_recursive( $directory ) {
		foreach ( glob( "{$directory}/*" ) as $file ) {
			if ( is_dir( $file ) )
				Meow_MFRH_Core::rmdir_recursive( $file );
			else
				unlink( $file );
		}
		rmdir( $directory );
	}

	/**
	 *
	 * MEDIA LIBRARY
	 *
	 */

	function library_bulk_actions( $bulk_actions ) {
		$bulk_actions['mfrh_lock_all'] = __( 'Lock (Renamer)', 'media-file-renamer');
		$bulk_actions['mfrh_unlock_all'] = __( 'Unlock (Renamer)', 'media-file-renamer');
		$bulk_actions['mfrh_rename_all'] = __( 'Rename (Renamer)', 'media-file-renamer');
		return $bulk_actions;
	}

	function library_bulk_actions_handler( $redirect_to, $doaction, $ids ) {
		if ( $doaction == 'mfrh_lock_all' ) {
			foreach ( $ids as $post_id ) {
				add_post_meta( $post_id, '_manual_file_renaming', true, true );
			}
		}
		if ( $doaction == 'mfrh_unlock_all' ) {
			foreach ( $ids as $post_id ) {
				delete_post_meta( $post_id, '_manual_file_renaming' );
			}
		}
		if ( $doaction == 'mfrh_rename_all' ) {
			foreach ( $ids as $post_id ) {
				$this->rename( $post_id );
			}
		}
		return $redirect_to;
	}

	/**
	 *
	 * 'RENAME' LINK
	 *
	 */

	function add_media_columns($columns) {
			$columns['mfrh_column'] = __( 'Rename', 'media-file-renamer' );
			return $columns;
	}

	function manage_media_custom_column( $column_name, $id ) {
		$paged = isset( $_GET['paged'] ) ? ( '&paged=' . $_GET['paged'] ) : "";
		if ( $column_name !== 'mfrh_column' )
			return;

		// Information for locked media
		$locked = get_post_meta( $id, '_manual_file_renaming', true );
		if ( $locked ) {
			echo "<span title='" . __( 'Manually renamed.', 'media-file-renamer' ) . "' style='font-size: 24px; color: #36B15C;' class='dashicons dashicons-yes'></span>";
			$page = isset( $_GET['page'] ) ? ( '&page=' . $_GET['page'] ) : "";
			echo "<a title='" . __( 'Locked to manual only. Click to unlock it.', 'media-file-renamer' ) . "' href='?" . $page . "&mfrh_unlock=" . $id . $paged . "'><span style='font-size: 20px; position: relative; top: 0px; color: #36B15C;' class='dashicons dashicons-lock'></span></a>";
			return;
		}

		// Information for media that needs renaming
		$needs_rename = $this->check_attachment( get_post( $id, ARRAY_A ), $output );
		if ( $needs_rename ) {
			$this->generate_explanation( $output );
			return;
		}

		// Information for non-locked media
		$original_filename = get_post_meta( $id, '_original_filename', true );
		echo "<span title='" . __( 'Automatically renamed.', 'media-file-renamer' ) . "'style='font-size: 24px; color: #36B15C;' class='dashicons dashicons-yes'></span>";
		$page = isset( $_GET['page'] ) ? ( '&page=' . $_GET['page'] ) : "";
		if ( get_option( 'mfrh_undo', false ) && !empty( $original_filename ) ) {
			echo "<a title='" . __( 'Rename to original filename: ', 'media-file-renamer' ) . $original_filename . "' href='?" . $page . "&mfrh_undo=" . $id . $paged . "' style='position: relative; top: 4px; font-size: 15px; color: #de4817;' class='dashicons dashicons-undo'></a>";
		}
		echo "<a title='" . __( 'Click to lock it to manual only.', 'media-file-renamer' ) . "' href='?" . $page . "&mfrh_lock=" . $id . $paged . "'><span style='font-size: 20px;' class='dashicons dashicons-unlock'></span></a>";
	}

	function admin_head() {
		if ( !empty( $_GET['mfrh_rename'] ) ) {
			$mfrh_rename = $_GET['mfrh_rename'];
			$this->rename( $mfrh_rename );
			$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'mfrh_rename' ), $_SERVER['REQUEST_URI'] );
		}
		if ( !empty( $_GET['mfrh_unlock'] ) ) {
			$mfrh_unlock = $_GET['mfrh_unlock'];
			delete_post_meta( $mfrh_unlock, '_manual_file_renaming' );
			$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'mfrh_unlock' ), $_SERVER['REQUEST_URI'] );
		}
		if ( !empty( $_GET['mfrh_undo'] ) ) {
			$mfrh_undo = $_GET['mfrh_undo'];
			$original_filename = get_post_meta( $mfrh_undo, '_original_filename', true );
			$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'mfrh_undo' ), $_SERVER['REQUEST_URI'] );
			$this->rename( $mfrh_undo, $original_filename );

			$fp = get_attached_file( $mfrh_undo );
			$path_parts = pathinfo( $fp );
			$basename = $path_parts['basename'];
			if ( $basename == $original_filename )
				delete_post_meta( $mfrh_undo, '_original_filename' );
		}
		if ( !empty( $_GET['mfrh_lock'] ) ) {
			$mfrh_lock = $_GET['mfrh_lock'];
			add_post_meta( $mfrh_lock, '_manual_file_renaming', true, true );
			$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'mfrh_lock' ), $_SERVER['REQUEST_URI'] );
		}

		?>
		<script type="text/javascript" >

			var current;
			var ids = [];

			function mfrh_process_next() {
				var data = { action: 'mfrh_rename_media', subaction: 'renameMediaId', id: ids[current - 1] };
				jQuery('#mfrh_progression').text(current + "/" + ids.length);
				jQuery.post(ajaxurl, data, function (response) {
					if (++current <= ids.length) {
						mfrh_process_next();
					}
					else {
						jQuery('#mfrh_progression').html("<?php echo __( "Done. Please <a href='?page=rename_media_files'>refresh</a> this page.", 'media-file-renamer' ); ?>");
					}
				});
			}

			function mfrh_rename_media(all) {
				current = 1;
				ids = [];
				var data = { action: 'mfrh_rename_media', subaction: 'getMediaIds', all: all ? '1' : '0' };
				jQuery('#mfrh_progression').text("<?php echo __( "Please wait...", 'media-file-renamer' ); ?>");
				jQuery.post(ajaxurl, data, function (response) {
					reply = jQuery.parseJSON(response);
					ids = reply.ids;
					jQuery('#mfrh_progression').html(current + "/" + ids.length);
					mfrh_process_next();
				});
			}

			function mfrh_process_next_undo() {
				var data = { action: 'mfrh_undo_media', subaction: 'undoMediaId', id: ids[current - 1] };
				jQuery('#mfrh_progression').text(current + "/" + ids.length);
				jQuery.post(ajaxurl, data, function (response) {
					if (++current <= ids.length) {
						mfrh_process_next_undo();
					}
					else {
						jQuery('#mfrh_progression').html("<?php echo __( "Done. Please <a href='?page=rename_media_files'>refresh</a> this page.", 'media-file-renamer' ); ?>");
					}
				});
			}

			function mfrh_undo_media(all) {
				current = 1;
				ids = [];
				var data = { action: 'mfrh_undo_media', subaction: 'getMediaIds', all: all ? '1' : '0' };
				jQuery('#mfrh_progression').text("<?php echo __( "Please wait...", 'media-file-renamer' ); ?>");
				jQuery.post(ajaxurl, data, function (response) {
					reply = jQuery.parseJSON(response);
					ids = reply.ids;
					jQuery('#mfrh_progression').html(current + "/" + ids.length);
					mfrh_process_next_undo();
				});
			}

			function mfrh_export_table(table) {
				var table = jQuery(table);
				var data = [];
				// Header
				table.find('thead tr').each(function(i, tr) {
					var row = [];
					jQuery(tr).find('th').each(function(i, td) {
						var text = jQuery(td).text();
						row.push(text);
					});
					data.push(row);
				});
				// Body
				table.find('tbody tr').each(function(i, tr) {
					var row = [];
					jQuery(tr).find('td').each(function(i, td) {
						var text = jQuery(td).text();
						row.push(text);
					});
					data.push(row);
				});
				var csvContent = "data:text/csv;charset=utf-8,";
				data.forEach(function(infoArray, index){
					dataString = infoArray.join(",");
					csvContent += index < data.length ? dataString+ "\n" : dataString;
				});
				var encodedUri = encodeURI(csvContent);
				var link = document.createElement("a");
				link.setAttribute("href", encodedUri);
				link.setAttribute("download", "media-file-renamer.csv");
				document.body.appendChild(link);
				link.click();
			}

		</script>
		<?php
	}

	/**
	 *
	 * BULK MEDIA RENAME PAGE
	 *
	 */

	 function wp_ajax_mfrh_rename_media() {
		$subaction = $_POST['subaction'];
		if ( $subaction == 'getMediaIds' ) {
			$all = intval( $_POST['all'] );
			global $wpdb;
			$ids = $wpdb->get_col( "SELECT p.ID FROM $wpdb->posts p WHERE post_status = 'inherit' AND post_type = 'attachment'" );
			if ( !$all ) {
				$idsToRemove = $wpdb->get_col( "SELECT m.post_id FROM $wpdb->postmeta m
					WHERE m.meta_key = '_manual_file_renaming' and m.meta_value = 1" );
				$ids = array_values( array_diff( $ids, $idsToRemove ) );
			}
			else {
				// We rename all, so we should unlock everything.
				$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key = '_manual_file_renaming'" );
			}
			$reply = array();
			$reply['ids'] = $ids;
			$reply['total'] = count( $ids );
			echo json_encode( $reply );
			die;
		}
		else if ( $subaction == 'renameMediaId' ) {
			$id = intval( $_POST['id'] );
			$this->rename( $id );
			echo 1;
			die();
		}
		echo 0;
		die();
	}

	 function wp_ajax_mfrh_undo_media() {
		$subaction = $_POST['subaction'];
		if ( $subaction == 'getMediaIds' ) {
			global $wpdb;
			$ids = $wpdb->get_col( "
				SELECT p.ID FROM $wpdb->posts p
				WHERE post_status = 'inherit' AND post_type = 'attachment'" );
			$reply = array();
			$reply['ids'] = $ids;
			$reply['total'] = count( $ids );
			echo json_encode( $reply );
			die;
		}
		else if ( $subaction == 'undoMediaId' ) {
			$id = intval( $_POST['id'] );
			$original_filename = get_post_meta( $id, '_original_filename', true );
			$this->rename( $id, $original_filename );
			echo 1;
			die();
		}
		echo 0;
		die();
	}

	function admin_menu() {
		$method = apply_filters( 'mfrh_method', 'media_title' );
		if ( $method != 'none' ) {
			add_media_page( 'Media File Renamer', __( 'Renamer', 'media-file-renamer' ), 'manage_options', 'rename_media_files', array( $this, 'rename_media_files' ) );
		}
	}

	function wpml_media_is_installed() {
		return defined( 'WPML_MEDIA_VERSION' );
	}

	// To avoid issue with WPML Media for instance
	function is_real_media( $id ) {
		if ( $this->wpml_media_is_installed() ) {
			global $sitepress;
			$language = $sitepress->get_default_language( $id );
			return icl_object_id( $id, 'attachment', true, $language ) == $id;
		}
		return true;
	}

	function is_header_image( $id ) {
		static $headers = false;
		if ( $headers == false ) {
			global $wpdb;
			$headers = $wpdb->get_col( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attachment_is_custom_header'" );
		}
		return in_array( $id, $headers );
	}

	function generate_unique_filename( $actual, $dirname, $filename, $counter = null ) {
		$new_filename = $filename;
		if ( !is_null( $counter ) ) {
			$whereisdot = strrpos( $new_filename, '.' );
			$new_filename = substr( $new_filename, 0, $whereisdot ) . '-' . $counter
				. '.' . substr( $new_filename, $whereisdot + 1 );
		}
		if ( $actual == $new_filename )
			return false;
		if ( file_exists( $dirname . "/" . $new_filename ) )
			return $this->generate_unique_filename( $actual, $dirname, $filename,
				is_null( $counter ) ? 2 : $counter + 1 );
		return $new_filename;
	}

	function get_post_from_media( $id ) {
		global $wpdb;
		$postid = $wpdb->get_var( $wpdb->prepare( "
			SELECT post_parent p
			FROM $wpdb->posts p
			WHERE ID = %d", $id ),
			0, 0 );
		if ( empty( $postid ) )
			return null;
		return get_post( $postid, OBJECT );
	}

	/*****************************************************************************
		RENAME ON UPLOAD
	*****************************************************************************/

	function wp_handle_upload_prefilter( $file ) {

		$this->log( "** On Upload: " . $file['name'] );
		$pp = pathinfo( $file['name'] );

		// If everything's fine, renames in based on the Title in the EXIF
		$method = apply_filters( 'mfrh_method', 'media_title' );
		if ( $method == 'media_title' ) {
			$exif = wp_read_image_metadata( $file['tmp_name'] );
			if ( !empty( $exif ) && isset( $exif[ 'title' ] ) && !empty( $exif[ 'title' ] ) ) {
				$file['name'] = $this->new_filename( null, $exif[ 'title' ] ) . '.' . $pp['extension'];
				$this->log( "New file should be: " . $file['name'] );
				return $file;
			}
		}
		else if ( $method == 'post_title' && isset( $_POST['post_id'] ) && $_POST['post_id'] > 0 ) {
			$post = get_post( $_POST['post_id'] );
			if ( !empty( $post ) && !empty( $post->post_title ) ) {
				$file['name'] = $this->new_filename( null, $post->post_title ) . '.' . $pp['extension'];
				$this->log( "New file should be: " . $file['name'] );
				return $file;
			}
		}

		// Otherwise, let's do the basics based on the filename

		// The name will be modified at this point so let's keep it in a global
		// and re-inject it later
		global $mfrh_title_override;
		$mfrh_title_override = $pp['filename'];
		add_filter( 'wp_read_image_metadata', array( $this, 'wp_read_image_metadata' ), 10, 2 );

		// Modify the filename
		$pp = pathinfo( $file['name'] );
		$file['name'] = $this->new_filename( null, $pp['basename'] );
		return $file;
	}

	function wp_read_image_metadata( $meta, $file ) {
		// Override the title, without this it is using the new filename
		global $mfrh_title_override;
    $meta['title'] = $mfrh_title_override;
    return $meta;
	}

	/****************************************************************************/

	// Return false if everything is fine, otherwise return true with an output.
	function check_attachment( $post, &$output = array(), $manual_filename = null ) {
		$id = $post['ID'];
		$old_filepath = get_attached_file( $id );
		$old_filepath = Meow_MFRH_Core::sensitive_file_exists( $old_filepath );
		$path_parts = pathinfo( $old_filepath );
		//print_r( $path_parts );
		$directory = $path_parts['dirname'];
		$old_filename = $path_parts['basename'];

		// Check if media/file is dead
		if ( !$old_filepath || !file_exists( $old_filepath ) ) {
			delete_post_meta( $id, '_require_file_renaming' );
			return false;
		}

		// Is it forced/manual
		// Check mfrh_new_filename (coming from manual input) if it is different than previous filename
		if ( empty( $manual_filename ) && isset( $post['mfrh_new_filename'] ) ) {
			if ( strtolower( $post['mfrh_new_filename'] ) != strtolower( $old_filename ) )
				$manual_filename =  $post['mfrh_new_filename'];
		}

		if ( !empty( $manual_filename ) ) {
			$new_filename = $manual_filename;
			$output['manual'] = true;
		}
		else {
			$method = apply_filters( 'mfrh_method', 'media_title' );
			if ( $method === 'none') {
				delete_post_meta( $id, '_require_file_renaming' );
				return false;
			}
			if ( get_post_meta( $id, '_manual_file_renaming', true ) ) {
				return false;
			}

			// Skip header images
			if ( $this->is_header_image( $id ) ) {
				delete_post_meta( $id, '_require_file_renaming' );
				return false;
			}
			
			// Get information
			$base_title = $post['post_title'];
			if ( $method == 'post_title' ) {
				$attachedpost = $this->get_post_from_media( $id );
				if ( is_null( $attachedpost ) )
					return false;
				$base_title = $attachedpost->post_title;
			}
			else if ( $method == 'alt_text' ) {
				$image_alt = get_post_meta( $id, '_wp_attachment_image_alt', true );
				if ( is_null( $image_alt ) )
					return false;
				$base_title = $image_alt;
			}
			$new_filename = $this->new_filename( $post, $base_title );
			//$this->log( "New title: $base_title, New filename: $new_filename" );
		}
		
		// If a filename has a counter, and the ideal is without the counter, let's ignore it
		$ideal = preg_replace( '/-[1-9]{1,10}\./', '$1.', $old_filename );
		if ( !$manual_filename ) {
			if ( $ideal == $new_filename ) {
				delete_post_meta( $id, '_require_file_renaming' );
				return false;
			}
		}

		// Filename is equal to sanitized title
		if ( $new_filename == $old_filename ) {
			delete_post_meta( $id, '_require_file_renaming' );
			return false;
		}

		// Check for case issue, numbering
		$new_filepath = trailingslashit( $directory ) . $new_filename;
		$existing_file = Meow_MFRH_Core::sensitive_file_exists( $new_filepath );
		$case_issue = strtolower( $old_filename ) == strtolower( $new_filename );
		if ( $existing_file && !$case_issue ) {
			$is_numbered = apply_filters( 'mfrh_numbered', false );
			if ( $is_numbered ) {
				$new_filename = $this->generate_unique_filename( $ideal, $directory, $new_filename );
				if ( !$new_filename ) {
					delete_post_meta( $id, '_require_file_renaming' );
					return false;
				}
				$new_filepath = trailingslashit( $directory ) . $new_filename;
			}
		}

		// Send info to the requester function
		$output['post_id'] = $id;
		$output['post_name'] = $post['post_name'];
		$output['post_title'] = $post['post_title'];
		$output['current_filename'] = $old_filename;
		$output['current_filepath'] = $old_filepath;
		$output['desired_filename'] = $new_filename;
		$output['desired_filepath'] = $new_filepath;
		$output['case_issue'] = $case_issue;
		$output['manual'] = !empty( $manual_filename );
		$output['locked'] = get_post_meta( $id, '_manual_file_renaming', true );
		$output['desired_filename_exists'] = false;

		// It seems it could be renamed :)
		if ( !get_post_meta( $post['ID'], '_require_file_renaming' ) ) {
			add_post_meta( $post['ID'], '_require_file_renaming', true, true );
		}
		return true;
	}

	function check_text() {
		$issues = array();
		global $wpdb;
		$ids = $wpdb->get_col( "
			SELECT p.ID
			FROM $wpdb->posts p
			WHERE post_status = 'inherit'
			AND post_type = 'attachment'
		" );
		foreach ( $ids as $id )
			if ( $this->check_attachment( get_post( $id, ARRAY_A ), $output ) )
				array_push( $issues, $output );
		return $issues;
	}

	function generate_explanation( $file ) {

		static $previous = array();

		$smallDiv = '<div style="line-height: 12px; font-size: 10px; margin-top: 5px;">';

		if ( $file['post_title'] == "" ) {
			echo " <a class='button-primary' href='post.php?post=" . $file['post_id'] . "&action=edit'>" . __( 'Edit Media', 'media-file-renamer' ) . "</a><br /><small>" . __( 'This title cannot be used for a filename.', 'media-file-renamer' ) . "</small>";
		}
		else if ( $file['desired_filename_exists'] ) {
			echo "<a class='button-primary' href='post.php?post=" . $file['post_id'] . "&action=edit'>" . __( 'Edit Media', 'media-file-renamer' ) . "</a><br />$smallDiv" . __( 'The ideal filename already exists. If you would like to use a count and rename it, enable the <b>Numbered Files</b> option in the plugin settings.', 'media-file-renamer' ) . "</div>";
		}
		else {
			$page = isset( $_GET['page'] ) ? ( '&page=' . $_GET['page'] ) : "";
			$mfrh_scancheck = ( isset( $_GET ) && isset( $_GET['mfrh_scancheck'] ) ) ? '&mfrh_scancheck' : '';
			$mfrh_to_rename = ( !empty( $_GET['to_rename'] ) && $_GET['to_rename'] == 1 ) ? '&to_rename=1' : '';
			$modify_url = "post.php?post=" . $file['post_id'] . "&action=edit";
			$page = isset( $_GET['page'] ) ? ( '&page=' . $_GET['page'] ) : "";

			$isNew = true;
			if ( in_array( $file['desired_filename'], $previous ) )
				$isNew = false;
			else
				array_push( $previous, $file['desired_filename'] );

			echo "<a class='button-primary' href='?" . $page . $mfrh_scancheck . $mfrh_to_rename . "&mfrh_rename=" . $file['post_id'] . "'>" . __( 'Auto-Rename', 'media-file-renamer' ) . "</a>";
			echo "<a title='" . __( 'Click to lock it to manual only.', 'media-file-renamer' ) . "' href='?" . $page . "&mfrh_lock=" . $file['post_id'] . "'><span style='font-size: 16px; margin-top: 5px;' class='dashicons dashicons-unlock'></span></a>";

			if ( $file['case_issue'] ) {
				echo '<br />' . $smallDiv .
					sprintf( __( 'Rename in lowercase, to %s. You can also <a href="%s">edit this media</a>.', 'media-file-renamer' ),
					$file['desired_filename'], $modify_url ) . "</div>";
			}
			else {
				echo '<br />' . $smallDiv .
					sprintf( __( 'Rename to %s. You can also <a href="%s">EDIT THIS MEDIA</a>.', 'media-file-renamer' ),
					$file['desired_filename'], $modify_url ) . "</div>";
			}

			if ( !$isNew ) {
				echo $smallDiv . "<i>";
				echo __( 'The first media you rename will actually get this filename; the next will be either not renamed or will have a counter appended to it.', 'media-file-renamer' );
				echo '</i></div>';
			}
		}
	}

	function rename_media_files() {
		$hide_ads = get_option( 'meowapps_hide_ads' );
		echo '<div class="wrap">';
	  echo $this->mfrh_admin->display_title( "Media File Renamer" );
		echo '<p></p>';
		global $wpdb;

		if ( isset( $_GET ) && isset( $_GET['mfrh_lockall'] ) ) {
			$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key = '_manual_file_renaming'" );
			$wpdb->query( "INSERT INTO $wpdb->postmeta (meta_key, meta_value, post_id)
				SELECT '_manual_file_renaming', 1, p.ID
				FROM $wpdb->posts p WHERE post_status = 'inherit' AND post_type = 'attachment'"
			);
			echo '<div class="updated"><p>';
		  echo __( 'All the media files are now locked.', 'media-file-renamer' );
		  echo '</p></div>';
		}

		if ( isset( $_GET ) && isset( $_GET['mfrh_unlockall'] ) ) {
			$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key = '_manual_file_renaming'" );
		}

		$checkFiles = null;
		if ( isset( $_GET ) && isset( $_GET['mfrh_scancheck'] ) )
			$checkFiles = $this->check_text();
		// FLAGGING
		// if ( get_option( 'mfrh_flagging' ) ) {
		// 	$this->file_counter( $flagged, $total, true );
		// }
		$all_media = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts p WHERE post_status = 'inherit' AND post_type = 'attachment'" );
		$manual_media = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key = '_manual_file_renaming' AND meta_value = 1" );
		?>

		<?php
		if ( !$this->mfrh_admin->is_registered() ) {
		  echo '<div class="updated"><p>';
		  echo __( '<b>The Pro version</b> of the plugin allows you to <b>rename based on the title of the post</b> (product or whatever else) you media is attached to, <b>rename manually</b>, use <b>numbered files</b> (by adding a counter if the filenames are similar), <b>sync the title with your ALT text</b>, UTF8 support (if you need it), a force rename (to repair a broken install), and, more importantly, <b>supports the developer</b> :) The serial key for the Pro has to be inserted in your Meow Apps > File Renamer > Pro. Thank you :)<br /><br /><a class="button-primary" href="http://meowapps.com/media-file-renamer/" target="_blank">Get the serial key for the Pro</a>', 'media-file-renamer' );
		  echo '</p></div>';
		}
		?>

		<h2>Rename in Bulk</h2>

		<?php if ( get_option( 'mfrh_flagging' ) ): ?>
			<p>
				<b>There are <span class='mfrh-flagged' style='color: red;'><?php _e( $flagged ); ?></span> media files flagged for auto-renaming out of <?php _e( $total ); ?> in total.</b> Those are the files that couldn't be renamed on the fly when their names were updated. You can now rename those flagged media, or rename all of them (which will unlock them all and force their renaming). <span style='color: red; font-weight: bold;'>Please backup your uploads folder + DB before using this.</span> If you don't know how, give a try to this: <a href='https://updraftplus.com/?afref=460' target='_blank'>UpdraftPlus</a>.
			</p>
		<?php else: ?>
			<p>
				You might have noticed that some of your media are locked by the file renamer, others are unlocked. Automatically, the plugin locks the media you renamed manually. By default, they are unlocked. Here, you have the choice of rename all the media in your DB or only the ones which are unlocked (to keep the files you renamed manually). <span style='color: red; font-weight: bold;'>Please backup your uploads folder + DB before using this.</span> If you don't know how, give a try to this: <a href='https://updraftplus.com/?afref=460' target='_blank'>UpdraftPlus</a>.
			</p>
		<?php endif; ?>

		<div style='margin-top: 12px; background: #FFF; padding: 5px; border-radius: 4px; height: 28px; box-shadow: 0px 0px 6px #C2C2C2;'>

			<a onclick='mfrh_rename_media(false)' id='mfrh_rename_all_images' class='button-primary'
				style='margin-right: 0px;'><span class="dashicons dashicons-controls-play" style="position: relative; top: 3px; left: -2px;"></span>
				<?php echo sprintf( __( "Rename ALL [%d]", 'media-file-renamer' ), $all_media - $manual_media ); ?>
			</a>
			<a onclick='mfrh_rename_media(true)' id='mfrh_unlock_rename_all_images' class='button-primary'
				style='margin-right: 0px;'><span class="dashicons dashicons-controls-play" style="position: relative; top: 3px; left: -2px;"></span>
				<?php echo sprintf( __( "Unlock ALL & Rename [%d]", 'media-file-renamer' ), $all_media ); ?>
			</a>
			<span style='margin-right: 5px; margin-left: 5px;'>|</span>
			<a href="?page=rename_media_files&mfrh_lockall" id='mfrh_lock_all_images' class='button-primary'
				style='margin-right: 0px;'><span class="dashicons dashicons-controls-play" style="position: relative; top: 3px; left: -2px;"></span>
				<?php echo sprintf( __( "Lock ALL [%d]", 'media-file-renamer' ), $all_media ); ?>
			</a>
			<a href="?page=rename_media_files&mfrh_unlockall" id='mfrh_unblock_all_images' class='button-primary'
				style='margin-right: 0px;'><span class="dashicons dashicons-controls-play" style="position: relative; top: 3px; left: -2px;"></span>
				<?php echo sprintf( __( "Unlock ALL [%d]", 'media-file-renamer' ), $all_media ); ?>
			</a>
			<a onclick='mfrh_undo_media()' id='mfrh_undo_all_images' class='button button-red'
				style='margin-right: 0px; float: right;'><span class="dashicons dashicons-undo" style="position: relative; top: 3px; left: -2px;"></span>
				<?php echo sprintf( __( "Undo ALL [%d]", 'media-file-renamer' ), $all_media ); ?>
			</a>
			<span id='mfrh_progression'></span>

			<?php if ( get_option( 'mfrh_flagging' ) ): ?>
				<?php if ($flagged > 0): ?>
					<a onclick='mfrh_rename_media(false)' id='mfrh_rename_dued_images' class='button-primary'>
						<?php echo sprintf( __( "Rename <span class='mfrh-flagged'>%d</span> flagged media", 'media-file-renamer' ), $flagged ); ?>
					</a>
				<?php else: ?>
					<a id='mfrh_rename_dued_images' class='button-primary'>
						<?php echo sprintf( __( "Rename <span class='mfrh-flagged'>%d</span> flagged media", 'media-file-renamer' ), $flagged ); ?>
					</a>
				<?php endif; ?>
			<?php endif; ?>

		</div>

		<h2>Rename 1 by 1</h2>
		<p>If you want to rename the media this way, I recommend you to do it from the Media Library directly. If you think this "Scan All" is really handy, please tell me that you are using it on the forums. I am currently planning to remove it and moving the "Rename in Bulk" with the settings of File Renamer (to clean the WordPress UI).</p>
		<table class='wp-list-table widefat fixed media' style='margin-top: 15px;'>
			<thead>
				<tr><th><?php _e( 'Title', 'media-file-renamer' ); ?></th><th><?php _e( 'Current Filename', 'media-file-renamer' ); ?></th><th><?php _e( 'Desired Filename', 'media-file-renamer' ); ?></th><th><?php _e( 'Action', 'media-file-renamer' ); ?></th></tr>
			</thead>
			<tfoot>
				<tr><th><?php _e( 'Title', 'media-file-renamer' ); ?></th><th><?php _e( 'Current Filename', 'media-file-renamer' ); ?></th><th><?php _e( 'Desired Filename', 'media-file-renamer' ); ?></th><th><?php _e( 'Action', 'media-file-renamer' ); ?></th></tr>
			</tfoot>
			<tbody>
				<?php
					if ( $checkFiles != null ) {
						foreach ( $checkFiles as $file ) {
							echo "<tr><td><a href='post.php?post=" . $file['post_id'] . "&action=edit'>" . ( $file['post_title'] == "" ? "(no title)" : $file['post_title'] ) . "</a></td>"
								. "<td>" . $file['current_filename'] . "</td>"
								. "<td>" . $file['desired_filename'] . "</td>";
							echo "<td>";
							$this->generate_explanation( $file );
							echo "</td></tr>";
						}
					}
					else if ( isset( $_GET['mfrh_scancheck'] ) && ( $checkFiles == null || count( $checkFiles ) < 1 ) ) {
						?><tr><td colspan='4'><div style='width: 100%; margin-top: 15px; margin-bottom: 15px; text-align: center;'>
							<div style='margin-top: 15px;'><?php _e( 'There are no issues. Cool!<br />Let\'s go visit <a target="_blank" href=\'http://offbeatjapan.org\'>The Offbeat Guide of Japan</a> :)', 'media-file-renamer' ); ?></div>
						</div></td><?php
					}
					else if ( $checkFiles == null ) {
						?><tr><td colspan='4'><div style='width: 100%; text-align: center;'>
							<a class='button-primary' href="?page=rename_media_files&mfrh_scancheck" style='margin-top: 15px; margin-bottom: 15px;'><span class="dashicons dashicons-admin-generic" style="position: relative; top: 3px; left: -2px;"></span>
								<?php _e( "Scan All & Show Issues", 'media-file-renamer' ); ?>
							</a>
						</div></td><?php
					}
				?>
			</tbody>
		</table>

		<h2>Before / After</h2>
		<p>This is useful if you wish to create redirections from your old filenames to your new ones. The CSV file generated by Media File Renamer is compatible with the import function of the <a href="https://wordpress.org/plugins/redirection/" target="_blank">Redirection</a> plugin. The redirections with slugs are already automatically and natively handled by WordPress.</p>

		<div style='margin-top: 12px; background: #FFF; padding: 5px; border-radius: 4px; height: 28px; box-shadow: 0px 0px 6px #C2C2C2;'>

			<a href="?page=rename_media_files&mfrh_beforeafter_filenames" class='button-primary' style='margin-right: 0px;'>
				<span class="dashicons dashicons-media-spreadsheet" style="position: relative; top: 3px; left: -2px;"></span>
				<?php echo _e( "Display Filenames", 'media-file-renamer' ); ?>
			</a>

			<a onclick="mfrh_export_table('#mfrh-before-after')" class='button-primary' style='margin-right: 0px; float: right;'>
				<span class="dashicons dashicons-arrow-down-alt" style="position: relative; top: 3px; left: -2px;"></span>
				<?php echo _e( "Export as CSV", 'media-file-renamer' ); ?>
			</a>

		</div>

		<table id='mfrh-before-after' class='wp-list-table widefat fixed media' style='margin-top: 15px;'>
			<thead>
				<tr><th><?php _e( 'Before', 'media-file-renamer' ); ?></th><th><?php _e( 'After', 'media-file-renamer' ); ?></th></tr>
			</thead>
			<tfoot>
				<tr><th><?php _e( 'Before', 'media-file-renamer' ); ?></th><th><?php _e( 'After', 'media-file-renamer' ); ?></th></tr>
			</tfoot>
			<tbody>
				<?php
					if ( isset( $_GET['mfrh_beforeafter_filenames'] ) || isset( $_GET['mfrh_beforeafter_slugs'] ) ) {
						global $wpdb;
						$results = $wpdb->get_results( "
							SELECT m.post_id as ID, m.meta_value as original_filename, m2.meta_value as current_filename
							FROM {$wpdb->postmeta} m
							JOIN {$wpdb->postmeta} m2 on m2.post_id = m.post_id AND m2.meta_key = '_wp_attached_file'
							WHERE m.meta_key = '_original_filename'" );
						foreach ( $results as $row ) {
							$fullsize_path = wp_get_attachment_url( $row->ID );
							$parts = pathinfo( $fullsize_path );
							$shorten_url = trailingslashit( $parts['dirname'] ) . $row->original_filename;
							if ( isset( $_GET['mfrh_beforeafter_filenames'] ) )
								echo "<tr><td>{$shorten_url}</td><td>$fullsize_path</td></tr>";
							else
								echo "<tr><td>{$row->original_slug}</td><td>{$row->current_slug}</td></tr>";
						}
					}
				?>
			</tbody>
		</table>

		<?php
	}

	/**
	 *
	 * RENAME ON SAVE / PUBLISH
	 * Originally proposed by Ben Heller
	 * Added and modified by Jordy Meow
	 */

	function save_post( $post_id ) {
		$status = get_post_status( $post_id );
		if ( !in_array( $status, array( 'publish', 'draft', 'future', 'private' ) ) )
			return;
		$onsave = get_option( "mfrh_rename_on_save" );
		if ( !$onsave )
			return;
		$args = array( 'post_type' => 'attachment', 'numberposts' => -1, 'post_status' =>'any', 'post_parent' => $post_id );
		$medias = get_posts( $args );
		if ( $medias ) {
			$this->log( '[save_post]' );
			foreach ( $medias as $attach ) {
				// In the past, I used this to detect if the Media Library is NOT used:
				// isset( $attachment['image-size'] );
				$this->rename( $attach->ID );
			}
		}
	}

	/**
	 *
	 * EDITOR
	 *
	 */

	function attachment_fields_to_save( $post, $attachment ) {
		$this->log( '[attachment_fields_to_save]' );
		$post = $this->rename( $post );
		return $post;
	}

	function media_send_to_editor( $html, $id, $attachment ) {
		$this->check_attachment( get_post( $id, ARRAY_A ), $output );
		return $html;
	}

	function add_rename_metabox() {
		add_meta_box( 'mfrh_media', 'Filename', array( $this, 'attachment_fields' ), 'attachment', 'side', 'high' );
	}

	function attachment_fields( $post ) {
		$info = pathinfo( get_attached_file( $post->ID ) );
		$basename = $info['basename'];
		$is_manual = apply_filters( 'mfrh_manual', false );
		$html = '<input type="text" readonly class="widefat" name="mfrh_new_filename" value="' . $basename. '" />';
		$html .= '<p class="description">This feature is for <a target="_blank" href="http://meowapps.com/media-file-renamer/">Pro users</a> only.</p>';
		echo apply_filters( "mfrh_admin_attachment_fields", $html, $post );
		return $post;
	}

	function log_sql( $data, $antidata ) {
		if ( !get_option( 'mfrh_logsql' ) || !$this->mfrh_admin->is_registered() )
			return;
		$fh = fopen( trailingslashit( dirname(__FILE__) ) . 'mfrh_sql.log', 'a' );
		$fh_anti = fopen( trailingslashit( dirname(__FILE__) ) . 'mfrh_sql_revert.log', 'a' );
		$date = date( "Y-m-d H:i:s" );
		fwrite( $fh, "{$data}\n" );
		fwrite( $fh_anti, "{$antidata}\n" );
		fclose( $fh );
		fclose( $fh_anti );
	}

	function log( $data, $inErrorLog = false ) {
		if ( $inErrorLog )
			error_log( $data );
		if ( !get_option( 'mfrh_log' ) ) {
			return;
		}
		$fh = fopen( trailingslashit( dirname(__FILE__) ) . 'media-file-renamer.log', 'a' );
		$date = date( "Y-m-d H:i:s" );
		fwrite( $fh, "$date: {$data}\n" );
		fclose( $fh );
	}

	/**
	 *
	 * GENERATE A NEW FILENAME
	 *
	 */

 	static function replace_special_chars( $str ) {
		 $special_chars = array(
				"å" => "a", "Å" => "a",
				"ä" => "ae", "Ä" => "ae",
				"ö" => "oe", "Ö" => "oe",
				"ü" => "ue", "Ü" => "ue",
				"ß" => "ss", "ẞ" => "ss"
			);
			foreach ( $special_chars as $key => $value )
				$str = str_replace( $key, $value, $str );
			return $str;
	}

	function replace_chars( $str ) {
		$special_chars = array();
		$special_chars = apply_filters( 'mfrh_replace_rules', $special_chars );
		if ( !empty( $special_chars ) )
			foreach ( $special_chars as $key => $value )
				$str = str_replace( $key, $value, $str );
		return $str;
	}

	// NEW MEDIA FILE INFO (depending on the text/filename of the media)
	function new_filename( $media, $text, $manual_filename = null ) {

		$old_filename = null;
		$old_filename_no_ext = null;
		$new_ext = null;

		if ( !empty( $media ) ) {
			// Media already exists (not a fresh upload). Gets filename and ext.
			$old_filepath = get_attached_file( $media['ID'] );
			$pp = pathinfo( $old_filepath );
			$new_ext = empty( $pp['extension'] ) ? "" : $pp['extension'];
			$old_filename = $pp['basename'];
			$old_filename_no_ext = $pp['filename'];
		}
		else {
			// It's an upload, let's check if the extension is provided in the text
			$pp = pathinfo( $text );
			$new_ext = empty( $pp['extension'] ) ? "" : $pp['extension'];
			$text = $pp['filename'];
		}

		// Generate the new filename.
		if ( !empty( $manual_filename ) ) {
			// Filename is forced. Strip the extension. Keeps this extension in $new_ext.
			$pp = pathinfo( $manual_filename );
			$manual_filename = $pp['filename'];
			$new_ext = empty( $pp['extension'] ) ? $new_ext : $pp['extension'];
			$new_filename = $manual_filename;
		}
		else {
			// Filename is generated from $text, without an extension.
			$text = str_replace( ".jpg", "", $text );
			$text = str_replace( ".png", "", $text );
			$text = str_replace( "'", "-", $text );
			$text = strtolower( Meow_MFRH_Core::replace_chars( $text ) );
			$utf8_filename = apply_filters( 'mfrh_utf8', false );
			if ( $utf8_filename )
				$new_filename = sanitize_file_name( $text );
			else
				$new_filename = str_replace( "%", "-", sanitize_title( Meow_MFRH_Core::replace_special_chars( $text ) ) );
		}
		if ( empty( $new_filename ) )
			$new_filename = "empty";

		if ( !$manual_filename )
			$new_filename = apply_filters( 'mfrh_new_filename', $new_filename, $old_filename_no_ext, $media );

		// We know have a new filename, let's add an extension.
		$new_filename = !empty( $new_ext ) ? ( $new_filename . '.' . $new_ext ) : $new_filename;

		return $new_filename;
	}

	// Only replace the first occurence
	function str_replace( $needle, $replace, $haystack ) {
		$pos = strpos( $haystack, $needle );
		if ( $pos !== false )
			$haystack = substr_replace( $haystack, $replace, $pos, strlen( $needle ) );
		return $haystack;
	}

	/**
	 *
	 * RENAME FILES + COFFEE TIME
	 */

	 function call_hooks_rename_url( $post, $orig_image_url, $new_image_url  ) {
		 // With the full URLs
		 do_action( 'mfrh_url_renamed', $post, $orig_image_url, $new_image_url );

		 // With DB URLs
		 $upload_dir = wp_upload_dir();
		 do_action( 'mfrh_url_renamed', $post, str_replace( $upload_dir, "", $orig_image_url ),
		 	str_replace( $upload_dir, "", $new_image_url ) );
	 }

	function rename_file( $old, $new, $case_issue = false ) {
		if ( $case_issue ) {
			if ( !rename( $old, $old . md5( $old ) ) ) {
				$this->log( "The file couldn't be renamed (case issue) from $old to " . $old . md5( $old ) . "." );
				return false;
			}
			if ( !rename( $old . md5( $old ), $new ) ) {
				$this->log( "The file couldn't be renamed (case issue) from " . $old . md5( $old ) . " to $new." );
				return false;
			}
		}
		else if ( ( !rename( $old, $new ) ) ) {
			$this->log( "The file couldn't be renamed from $old to $new." );
			return false;
		}
		return true;
	}

	function rename( $media, $manual_filename = null, $fromMediaLibrary = true ) {
		$id = null;
		$post = null;

		// Check the arguments
		if ( is_numeric( $media ) ) {
			$id = $media;
			$post = get_post( $media, ARRAY_A );
		}
		else if ( is_array( $media ) ) {
			$id = $media['ID'];
			$post = $media;
		}
		else {
			die( 'Media File Renamer: rename() requires the ID or the array for the media.' );
		}

		$force_rename = apply_filters( 'mfrh_force_rename', false );
		$method = apply_filters( 'mfrh_method', 'media_title' );

		// Check attachment
		$need_rename = $this->check_attachment( $post, $output, $manual_filename );
		if ( !$need_rename ) {
			delete_post_meta( $id, '_require_file_renaming' );
			return $post;
		}

		// Prepare the variables
		$old_filepath = $output['current_filepath'];
		$case_issue = $output['case_issue'];
		$new_filepath = $output['desired_filepath'];
		$new_filename = $output['desired_filename'];
		$manual = $output['manual'] || !empty( $manual_filename );
		$path_parts = pathinfo( $old_filepath );
		$directory = $path_parts['dirname']; // '2011/01'
		$old_filename = $path_parts['basename']; // 'whatever.jpeg'

		$this->log( "** Rename Media: " . $old_filename );
		$this->log( "New file should be: " . $new_filename );

		// Check for issues with the files
		if ( !file_exists( $old_filepath ) ) {
			$this->log( "The original file ($old_filepath) cannot be found." );
			return $post;
		}
		if ( !$case_issue && !$force_rename && file_exists( $new_filepath ) ) {
			$this->log( "The new file already exists ($new_filepath). It is not a case issue. Renaming cancelled." );
			return $post;
		}

		// Keep the original filename
		$original_filename = get_post_meta( $id, '_original_filename', true );
		if ( empty( $original_filename ) )
			add_post_meta( $id, '_original_filename', $old_filename, true );

		// Rename the main media file.
		if ( !$this->rename_file( $old_filepath, $new_filepath, $case_issue ) && !$force_rename ) {
			$this->log( "[!] File $old_filepath -> $new_filepath" );
			return $post;
		}
		$this->log( "File\t$old_filepath -> $new_filepath" );
		do_action( 'mfrh_path_renamed', $post, $old_filepath, $new_filepath );

		// The new extension (or maybe it's just the old one)
		$old_ext = $path_parts['extension'];
		$new_ext = $old_ext;
		if ( $manual_filename ) {
			$pp = pathinfo( $manual_filename );
			$new_ext = $pp['extension'];
		}

		// Filenames without extensions
		$noext_old_filename = $this->str_replace( '.' . $old_ext, '', $old_filename );
		$noext_new_filename = $this->str_replace( '.' . $old_ext, '', $new_filename );

		// Update the attachment meta
		$meta = wp_get_attachment_metadata( $id );

		if ( $meta ) {
			if ( isset( $meta['file'] ) && !empty( $meta['file'] ) )
				$meta['file'] = $this->str_replace( $noext_old_filename, $noext_new_filename, $meta['file'] );
			if ( isset( $meta['url'] ) && !empty( $meta['url'] ) && count( $meta['url'] ) > 4 )
				$meta['url'] = $this->str_replace( $noext_old_filename, $noext_new_filename, $meta['url'] );
			else
				$meta['url'] = $noext_new_filename . '.' . $old_ext;
		}

		// Better to check like this rather than with wp_attachment_is_image
		// PDFs also have thumbnails now, since WP 4.7
		$has_thumbnails = isset( $meta['sizes'] );

		// Loop through the different sizes in the case of an image, and rename them.
		if ( $has_thumbnails ) {
			$orig_image_urls = array();
			$orig_image_data = wp_get_attachment_image_src( $id, 'full' );
			$orig_image_urls['full'] = $orig_image_data[0];
			foreach ( $meta['sizes'] as $size => $meta_size ) {
				if ( !isset($meta['sizes'][$size]['file'] ) )
					continue;
				$meta_old_filename = $meta['sizes'][$size]['file'];
				$meta_old_filepath = trailingslashit( $directory ) . $meta_old_filename;
				$meta_new_filename = $this->str_replace( $noext_old_filename, $noext_new_filename, $meta_old_filename );

				// Manual Rename also uses the new extension (if it was not stripped to avoid user mistake)
				if ( $force_rename && !empty( $new_ext ) ) {
					$meta_new_filename = $this->str_replace( $old_ext, $new_ext, $meta_new_filename );
				}

				$meta_new_filepath = trailingslashit( $directory ) . $meta_new_filename;
				$orig_image_data = wp_get_attachment_image_src( $id, $size );
				$orig_image_urls[$size] = $orig_image_data[0];

				// Double check files exist before trying to rename.
				if ( $force_rename || ( file_exists( $meta_old_filepath ) 
						&& ( ( !file_exists( $meta_new_filepath ) ) || is_writable( $meta_new_filepath ) ) ) ) {
					// WP Retina 2x is detected, let's rename those files as well
					if ( function_exists( 'wr2x_get_retina' ) ) {
						$wr2x_old_filepath = $this->str_replace( '.' . $old_ext, '@2x.' . $old_ext, $meta_old_filepath );
						$wr2x_new_filepath = $this->str_replace( '.' . $new_ext, '@2x.' . $new_ext, $meta_new_filepath );
						if ( file_exists( $wr2x_old_filepath ) 
							&& ( ( !file_exists( $wr2x_new_filepath ) ) || is_writable( $wr2x_new_filepath ) ) ) {
							
							// Rename retina file
							if ( !$this->rename_file( $wr2x_old_filepath, $wr2x_new_filepath, $case_issue ) && !$force_rename ) {
								$this->log( "[!] Retina $wr2x_old_filepath -> $wr2x_new_filepath" );
								return $post;
							}
							$this->log( "Retina\t$wr2x_old_filepath -> $wr2x_new_filepath" );
							do_action( 'mfrh_path_renamed', $post, $wr2x_old_filepath, $wr2x_new_filepath );
						}
					}

					// Rename meta file
					if ( !$this->rename_file( $meta_old_filepath, $meta_new_filepath, $case_issue ) && !$force_rename ) {
						$this->log( "[!] File $meta_old_filepath -> $meta_new_filepath" );
						return $post;
					}

					$meta['sizes'][$size]['file'] = $meta_new_filename;

					// Detect if another size has exactly the same filename
					foreach ( $meta['sizes'] as $s => $m ) {
						if ( !isset( $meta['sizes'][$s]['file'] ) )
							continue;
						if ( $meta['sizes'][$s]['file'] ==  $meta_old_filename ) {
							$this->log( "Updated $s based on $size, as they use the same file (probably same size)." );
							$meta['sizes'][$s]['file'] = $meta_new_filename;
						}
					}

					// Success, call other plugins
					$this->log( "File\t$meta_old_filepath -> $meta_new_filepath" );
					do_action( 'mfrh_path_renamed', $post, $meta_old_filepath, $meta_new_filepath );

				}
			}
		}
		else {
			$orig_attachment_url = wp_get_attachment_url( $id );
		}

		// This media doesn't require renaming anymore
		delete_post_meta( $id, '_require_file_renaming' );

		// If it was renamed manually (including undo), lock the file
		if ( $manual )
			add_post_meta( $id, '_manual_file_renaming', true, true );

		// Update metadata
		if ( $meta )
			wp_update_attachment_metadata( $id, $meta );
		update_attached_file( $id, $new_filepath );
		clean_post_cache( $id );

		// Rename slug/permalink
		if ( get_option( "mfrh_rename_slug" ) ) {
			$oldslug = $post['post_name'];
			$info = pathinfo( $new_filepath );
			$newslug = preg_replace( '/\\.[^.\\s]{3,4}$/', '', $info['basename'] );
			$post['post_name'] = $newslug;
			if ( wp_update_post( $post ) )
				$this->log( "Slug\t$oldslug -> $newslug" );
		}

		// Call the actions so that the plugin's plugins can update everything else (than the files)
		if ( $has_thumbnails ) {
			$orig_image_url = $orig_image_urls['full'];
			$new_image_data = wp_get_attachment_image_src( $id, 'full' );
			$new_image_url = $new_image_data[0];
			$this->call_hooks_rename_url( $post, $orig_image_url, $new_image_url );
			if ( !empty( $meta['sizes'] ) ) {
				foreach ( $meta['sizes'] as $size => $meta_size ) {
					$orig_image_url = $orig_image_urls[$size];
					$new_image_data = wp_get_attachment_image_src( $id, $size );
					$new_image_url = $new_image_data[0];
					$this->call_hooks_rename_url( $post, $orig_image_url, $new_image_url );
				}
			}
		}
		else {
			$new_attachment_url = wp_get_attachment_url( $id );
			$this->call_hooks_rename_url( $post, $orig_attachment_url, $new_attachment_url );
		}

		// HTTP REFERER set to the new media link
		if ( isset( $_REQUEST['_wp_original_http_referer'] ) && 
			strpos( $_REQUEST['_wp_original_http_referer'], '/wp-admin/' ) === false ) {
			$_REQUEST['_wp_original_http_referer'] = get_permalink( $id );
		}

		do_action( 'mfrh_media_renamed', $post, $old_filepath, $new_filepath );
		return $post;
	}

	/**
	 *
	 * INTERNAL ACTIONS (HOOKS)
	 * Mostly from the Side-Updates
	 *
	 * Available actions are:
	 * mfrh_path_renamed
	 * mfrh_url_renamed
	 * mfrh_media_renamed
	 *
	 */

	// Register internal actions
	function init_actions() {
		if ( get_option( "mfrh_update_posts", true ) )
			add_action( 'mfrh_url_renamed', array( $this, 'action_update_posts' ), 10, 3 );
		if ( get_option( "mfrh_update_postmeta", true ) )
			add_action( 'mfrh_url_renamed', array( $this, 'action_update_postmeta' ), 10, 3 );
		if ( get_option( "mfrh_rename_guid" ) )
			add_action( 'mfrh_media_renamed', array( $this, 'action_rename_guid' ), 10, 3 );
	}

	// The GUID should never be updated but... this will if the option is checked.
	// [TigrouMeow] It the recent version of WordPress, the GUID is not part of the $post (even though it is in database)
	// Explanation: http://pods.io/2013/07/17/dont-use-the-guid-field-ever-ever-ever/
	function action_rename_guid( $post, $old_filepath, $new_filepath ) {
		$meta = wp_get_attachment_metadata( $post['ID'] );
		$old_guid = get_the_guid( $post['ID'] );
		if ( $meta )
			$new_filepath = wp_get_attachment_url( $post['ID'] );
		global $wpdb;
		$query = $wpdb->prepare( "UPDATE $wpdb->posts SET guid = '%s' WHERE ID = '%d'", $new_filepath,  $post['ID'] );
		$query_revert = $wpdb->prepare( "UPDATE $wpdb->posts SET guid = '%s' WHERE ID = '%d'", $old_guid,  $post['ID'] );
		$this->log_sql( $query, $query_revert );
		$wpdb->query( $query );
		clean_post_cache( $post['ID'] );
		$this->log( "GUID\t$old_guid -> $new_filepath." );
	}

	// Mass update of all the meta with the new filenames
	function action_update_postmeta( $post, $orig_image_url, $new_image_url ) {
		global $wpdb;
		$query = $wpdb->prepare( "UPDATE $wpdb->postmeta 
			SET meta_value = '%s'
			WHERE meta_key <> '_original_filename'
			AND (TRIM(meta_value) = '%s'
			OR TRIM(meta_value) = '%s'
		);", $new_image_url, $orig_image_url, str_replace( ' ', '%20', $orig_image_url ) );
		$query_revert = $wpdb->prepare( "UPDATE $wpdb->postmeta 
			SET meta_value = '%s'
			WHERE meta_key <> '_original_filename'
			AND meta_value = '%s';
		", $orig_image_url, $new_image_url );
		$wpdb->query( $query );
		$this->log_sql( $query, $query_revert );
		$this->log( "Meta\t$orig_image_url -> $new_image_url" );
	}

	// Mass update of all the articles with the new filenames
	function action_update_posts( $post, $orig_image_url, $new_image_url ) {
		global $wpdb;

		// Content
		$query = $wpdb->prepare( "UPDATE $wpdb->posts 
			SET post_content = REPLACE(post_content, '%s', '%s')
			WHERE post_status != 'inherit'
			AND post_status != 'trash'
			AND post_type != 'attachment'
			AND post_type NOT LIKE '%acf-%'
			AND post_type NOT LIKE '%edd_%'
			AND post_type != 'shop_order'
			AND post_type != 'shop_order_refund'
			AND post_type != 'nav_menu_item'
			AND post_type != 'revision'
			AND post_type != 'auto-draft'", $orig_image_url, $new_image_url );
		$query_revert = $wpdb->prepare( "UPDATE $wpdb->posts 
			SET post_content = REPLACE(post_content, '%s', '%s')
			WHERE post_status != 'inherit'
			AND post_status != 'trash'
			AND post_type != 'attachment'
			AND post_type NOT LIKE '%acf-%'
			AND post_type NOT LIKE '%edd_%'
			AND post_type != 'shop_order'
			AND post_type != 'shop_order_refund'
			AND post_type != 'nav_menu_item'
			AND post_type != 'revision'
			AND post_type != 'auto-draft'", $new_image_url, $orig_image_url );
		$wpdb->query( $query );
		$this->log_sql( $query, $query_revert );
		$this->log( "Content\t$orig_image_url -> $new_image_url" );
		
		// Excerpt
		$query = $wpdb->prepare( "UPDATE $wpdb->posts 
			SET post_excerpt = REPLACE(post_excerpt, '%s', '%s')
			WHERE post_status != 'inherit'
			AND post_status != 'trash'
			AND post_type != 'attachment'
			AND post_type NOT LIKE '%acf-%'
			AND post_type NOT LIKE '%edd_%'
			AND post_type != 'shop_order'
			AND post_type != 'shop_order_refund'
			AND post_type != 'nav_menu_item'
			AND post_type != 'revision'
			AND post_type != 'auto-draft'", $orig_image_url, $new_image_url );
		$query_revert = $wpdb->prepare( "UPDATE $wpdb->posts 
			SET post_excerpt = REPLACE(post_excerpt, '%s', '%s')
			WHERE post_status != 'inherit'
			AND post_status != 'trash'
			AND post_type != 'attachment'
			AND post_type NOT LIKE '%acf-%'
			AND post_type NOT LIKE '%edd_%'
			AND post_type != 'shop_order'
			AND post_type != 'shop_order_refund'
			AND post_type != 'nav_menu_item'
			AND post_type != 'revision'
			AND post_type != 'auto-draft'", $new_image_url, $orig_image_url );
		$wpdb->query( $query );
		$this->log_sql( $query, $query_revert );
		$this->log( "Excerpt\t$orig_image_url -> $new_image_url" );
	}
}
