<?php

class Meow_MFRH_Core {

	public $admin = null;
	public $pro = false;
	public $is_rest = false;
	public $is_cli = false;
	public $method = 'media_title';
	public $upload_folder = null;
	public $site_url = null;
	public $contentDir = null; // becomes 'wp-content/uploads'
	private $allow_usage = null;
	private $allow_setup = null;
	private $images_only = false;

	public function __construct() {
		$this->site_url = get_site_url();
		$this->upload_folder = wp_upload_dir();
		$this->contentDir = substr( $this->upload_folder['baseurl'], 1 + strlen( $this->site_url ) );
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	function init() {

		// This should be checked after the init (is_rest checks the capacities)
		$this->is_rest = MeowCommon_Helpers::is_rest();
		$this->is_cli = defined( 'WP_CLI' ) && WP_CLI;
		$this->images_only = get_option( 'mfrh_images_only', false ) === "1";

		// Check the roles
		$this->allow_usage = apply_filters( 'mfrh_allow_usage', current_user_can( 'administrator' ) );
		$this->allow_setup = apply_filters( 'mfrh_allow_setup', current_user_can( 'manage_options' ) );
		if ( !$this->is_cli && !$this->allow_usage ) {
			return;
		}

		// Languages
		load_plugin_textdomain( MFRH_DOMAIN, false, basename( MFRH_PATH ) . '/languages' );

		// Part of the core, settings and stuff
		$this->admin = new Meow_MFRH_Admin( $this->allow_setup );
		if ( class_exists( 'MeowPro_MFRH_Core' ) ) {
			new MeowPro_MFRH_Core( $this, $this->admin );
			$this->pro = true;
		}

		// Initialize
		$this->method = apply_filters( 'mfrh_method', get_option( 'mfrh_auto_rename', 'media_title' ) );
		add_filter( 'attachment_fields_to_save', array( $this, 'attachment_fields_to_save' ), 20, 2 );

		// Only for REST
		if ( $this->is_rest ) {
			new Meow_MFRH_Rest( $this );
		}

		// Side-updates should be ran for CLI and REST
		if ( is_admin() || $this->is_rest || $this->is_cli ) {
			new Meow_MFRH_Updates( $this );
		}

		// Admin screens
		if ( is_admin() ) {
			new Meow_MFRH_UI( $this );
			if ( get_option( 'mfrh_rename_on_save', false ) ) {
				add_action( 'save_post', array( $this, 'save_post' ) );
			}
			if ( get_option( 'mfrh_on_upload', false ) ) {
				add_filter( 'wp_handle_upload_prefilter', array( $this, 'wp_handle_upload_prefilter' ), 10, 2 );
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
	static function sensitive_file_exists( $filename ) {

		$original_filename = $filename;
		$caseInsensitive = get_option( 'mfrh_case_insensitive_check', false );
		// if ( !$sensitive_check ) {
		// 	$exists = file_exists( $filename );
		// 	return $exists ? $filename : null;
		// }

		$output = false;
		$directoryName = mfrh_dirname( $filename );
		$fileArray = glob( $directoryName . '/*', GLOB_NOSORT );
		$i = ( $caseInsensitive ) ? "i" : "";

		// Check if \ is in the string
		if ( preg_match( "/\\\|\//", $filename) ) {
			$array = preg_split("/\\\|\//", $filename);
			$filename = $array[count( $array ) -1];
		}
		// Compare filenames
		foreach ( $fileArray as $file ) {
			if ( preg_match( "/\/" . preg_quote( $filename ) . "$/{$i}", $file ) ) {
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

	/**
	 * Returns all the media sharing the same file
	 * @param string $file The attached file path
	 * @param int|array $excludes The post ID(s) to exclude from the results
	 * @return array An array of IDs
	 */
	function get_posts_by_attached_file( $file, $excludes = null ) {
		global $wpdb;
		$r = array ();
		$q = <<< SQL
SELECT post_id
FROM {$wpdb->postmeta}
WHERE meta_key = '%s'
AND meta_value = '%s'
SQL;
		$rows = $wpdb->get_results( $wpdb->prepare( $q, '_wp_attached_file', _wp_relative_upload_path( $file ) ), OBJECT );
		if ( $rows && is_array( $rows ) ) {
			if ( !is_array( $excludes ) )
				$excludes = $excludes ? array ( (int) $excludes ) : array ();

			foreach ( $rows as $item ) {
				$id = (int) $item->post_id;
				if ( in_array( $id, $excludes ) ) continue;
				$r[] = $id;
			}
			$r = array_unique( $r );
		}
		return $r;
	}

	/*****************************************************************************
		RENAME ON UPLOAD
	*****************************************************************************/

	function wp_handle_upload_prefilter( $file ) {

		$this->log( "⏰ Event: New Upload (" . $file['name'] . ")" );
		$pp = mfrh_pathinfo( $file['name'] );

		// If everything's fine, renames in based on the Title in the EXIF
		switch ( $this->method ) {
		case 'media_title':
			$exif = wp_read_image_metadata( $file['tmp_name'] );
			if ( !empty( $exif ) && isset( $exif[ 'title' ] ) && !empty( $exif[ 'title' ] ) ) {
				$new_filename = $this->new_filename( $exif[ 'title' ], $file['name'] );
				if ( !is_null( $new_filename ) ) {
					$file['name'] = $new_filename;
					$this->log( "New file should be: " . $file['name'] );
				}
				return $file;
			}
			break;
		case 'post_title':
			if ( !isset( $_POST['post_id'] ) || $_POST['post_id'] < 1 ) break;
			$post = get_post( $_POST['post_id'] );
			if ( !empty( $post ) && !empty( $post->post_title ) ) {
				$new_filename = $this->new_filename( $post->post_title, $file['name'] );
				if ( !is_null( $new_filename ) ) {
					$file['name'] = $new_filename;
					$this->log( "New file should be: " . $file['name'] );
				}
				return $file;
			}
			break;
		case 'post_acf_field':
			if ( !isset( $_POST['post_id'] ) || $_POST['post_id'] < 1 ) break;
			$acf_field_name = get_option('mfrh_acf_field_name', false);
			if ($acf_field_name) {
				$new_filename = $this->new_filename( get_field($acf_field_name, $_POST['post_id']), $file['name'] );
				if ( !is_null( $new_filename ) ) {
					$file['name'] = $new_filename;
					$this->log( "New file should be: " . $file['name'] );
				}
				return $file;
			}
			break;
		}
		// Otherwise, let's do the basics based on the filename

		// The name will be modified at this point so let's keep it in a global
		// and re-inject it later
		global $mfrh_title_override;
		$mfrh_title_override = $pp['filename'];
		add_filter( 'wp_read_image_metadata', array( $this, 'wp_read_image_metadata' ), 10, 2 );

		// Modify the filename
		$pp = mfrh_pathinfo( $file['name'] );
		$new_filename = $this->new_filename( $pp['filename'], $file['name'] );
		if ( !is_null( $new_filename ) ) {
			$file['name'] = $new_filename;
		}
		return $file;
	}

	function wp_read_image_metadata( $meta, $file ) {
		// Override the title, without this it is using the new filename
		global $mfrh_title_override;
    $meta['title'] = $mfrh_title_override;
    return $meta;
	}

	/****************************************************************************/

	// Return false if everything is fine, otherwise return true with an output
	// which details the conditions and results about the renaming.
	function check_attachment( $post, &$output = array(), $manual_filename = null ) {
		$id = $post['ID'];
		$old_filepath = get_attached_file( $id );
		$old_filepath = Meow_MFRH_Core::sensitive_file_exists( $old_filepath );
		$path_parts = mfrh_pathinfo( $old_filepath );

		if ( $this->images_only && $post['post_mime_type'] !== 'image/jpeg' ) {
			return false;
		}

		// If the file doesn't exist, let's not go further.
		if ( !isset( $path_parts['dirname'] ) || !isset( $path_parts['basename'] ) )
			return false;

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
			if ( $this->method === 'none') {
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

			$base_for_rename = apply_filters( 'mfrh_base_for_rename', $post['post_title'], $id );
			$new_filename = $this->new_filename( $base_for_rename, $old_filename, null, $post );
			if ( is_null( $new_filename ) ) {
				return false; // Leave it as it is
			}
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
		$ideal_filename = $new_filename;
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
				$existing_file = Meow_MFRH_Core::sensitive_file_exists( $new_filepath );
			}
		}

		// Send info to the requester function
		$output['post_id'] = $id;
		$output['post_name'] = $post['post_name'];
		$output['post_title'] = $post['post_title'];
		$output['current_filename'] = $old_filename;
		$output['current_filepath'] = $old_filepath;
		$output['ideal_filename'] = $ideal_filename;
		$output['proposed_filename'] = $new_filename;
		$output['desired_filepath'] = $new_filepath;
		$output['case_issue'] = $case_issue;
		$output['manual'] = !empty( $manual_filename );
		$output['locked'] = get_post_meta( $id, '_manual_file_renaming', true );
		$output['proposed_filename_exists'] = !!$existing_file;
		$output['original_image'] = null;
		
		// If the ideal filename already exists
		// Maybe that's the original_image! If yes, we should let it go through
		// as the original_rename will be renamed into another filename anyway.
		if ( !!$existing_file ) {
			$meta = wp_get_attachment_metadata( $id );
			if ( isset( $meta['original_image'] ) && $new_filename === $meta['original_image'] ) {
				$output['original_image'] = $meta['original_image'];
				$output['proposed_filename_exists'] = false;
			}
		}

		// Set the '_require_file_renaming', even though it's not really used at this point (but will be,
		// with the new UI).
		if ( !get_post_meta( $post['ID'], '_require_file_renaming', true ) && !$output['locked']) {
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
		$args = array( 'post_type' => 'attachment', 'numberposts' => -1, 'post_status' =>'any', 'post_parent' => $post_id );
		$medias = get_posts( $args );
		if ( $medias ) {
			$this->log( '⏰ Event: Save Post' );
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
		$this->log( '⏰ Event: Save Attachment' );
		$post = $this->rename( $post );
		return $post;
	}

	function logs_directory_check() {
		if ( !file_exists( MFRH_PATH . '/logs/' ) ) {
			mkdir( MFRH_PATH . '/logs/', 0777 );
		}
	}

	function log_sql( $data, $antidata ) {
		if ( !get_option( 'mfrh_logsql' ) || !$this->admin->is_registered() )
			return;
		$this->logs_directory_check();
		$fh = fopen( MFRH_PATH . '/logs/mfrh_sql.log', 'a' );
		$fh_anti = fopen( MFRH_PATH . '/logs/mfrh_sql_revert.log', 'a' );
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
		$this->logs_directory_check();
		$fh = fopen( MFRH_PATH . '/logs/media-file-renamer.log', 'a' );
		$date = date( "Y-m-d H:i:s" );
		fwrite( $fh, "$date: {$data}\n" );
		fclose( $fh );
	}

	/**
	 *
	 * GENERATE A NEW FILENAME
	 *
	 */

	function replace_chars( $str ) {
		$special_chars = array();
		$special_chars = apply_filters( 'mfrh_replace_rules', $special_chars );
		if ( !empty( $special_chars ) )
			foreach ( $special_chars as $key => $value )
				$str = str_replace( $key, $value, $str );
		return $str;
	}

	/**
	 * Transform full width hyphens and other variety hyphens in half size into simple hyphen,
	 * and avoid consecutive hyphens and also at the beginning and end as well.
	 */
	function format_hyphens( $str ) {
		$hyphen = '-';
		$hyphens = [
			'﹣', '－', '−', '⁻', '₋',
			'‐', '‑', '‒', '–', '—',
			'―', '﹘', 'ー','ｰ',
		];
		$str = str_replace( $hyphens, $hyphen, $str );
		// remove at the beginning and end.
		$beginning = mb_substr( $str, 0, 1 );
		if ( $beginning === $hyphen ) {
			$str = mb_substr( $str, 1 );
		}
		$end = mb_substr( $str, -1 );
		if ( $end === $hyphen ) {
			$str = mb_strcut( $str, 0, mb_strlen( $str ) - 1 );
		}
		$str = preg_replace( '/-{2,}/u', '-', $str );
		$str = trim( $str, implode( '', $hyphens ) );
		return $str;
	}

	/**
	 * Computes the ideal filename based on a text
	 * @param array $media
	 * @param string $text
	 * @param string $manual_filename
	 * @return string|NULL If the resulting filename had no any valid characters, NULL is returned
	 */
	function new_filename( $text, $current_filename, $manual_filename = null, $media = null ) {

		// Gather the base values.

		if ( empty( $current_filename ) && !empty( $media ) ) {
			$current_filename = get_attached_file( $media['ID'] );
		}

		$pp = mfrh_pathinfo( $current_filename );
		$new_ext = empty( $pp['extension'] ) ? '' : $pp['extension'];
		$old_filename_no_ext = $pp['filename'];
		$text = empty( $text ) ? $old_filename_no_ext : $text;

		// Generate the new filename.

		if ( !empty( $manual_filename ) ) {
			// Forced filename (manual or undo, basically). Keep this extension in $new_ext.
			$manual_pp = mfrh_pathinfo( $manual_filename );
			$manual_filename = $manual_pp['filename'];
			$new_ext = empty( $manual_pp['extension'] ) ? $new_ext : $manual_pp['extension'];
			$new_filename = $manual_filename;
		}
		else {
			// Filename is generated from $text, without an extension.

			// Those are basically errors, when titles are generated from filename
			$text = str_replace( ".jpg", "", $text );
			$text = str_replace( ".png", "", $text );
			
			// Related to English
			$text = str_replace( "'s", "", $text );
			$text = str_replace( "n\'t", "nt", $text );
			$text = preg_replace( "/\'m/i", "-am", $text );

			// We probably do not want those neither
			$text = str_replace( "'", "-", $text );
			$text = preg_replace( "/\//s", "-", $text );
			$text = str_replace( ['.','…'], "", $text );

			$text = $this->replace_chars( $text );
			// Changed strolower to mb_strtolower... 
			if ( function_exists( 'mb_strtolower' ) ) {
				$text = mb_strtolower( $text );
			}
			else {
				$text = strtolower( $text );
			}
			$text = sanitize_file_name( $text );
			$new_filename = $this->format_hyphens( $text );
			$new_filename = trim( $new_filename, '-.' );
		}

		if ( empty( $manual_filename ) ) {
			$new_filename = $this->format_hyphens( $new_filename );
		}

		if ( !$manual_filename ) {
			$new_filename = apply_filters( 'mfrh_new_filename', $new_filename, $old_filename_no_ext, $media );
			$new_filename = sanitize_file_name( $new_filename );
		}

		// If the resulting filename had no any valid character, return NULL
		if ( empty( $new_filename ) ) {
			return null;
		}

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

		// From a url to the shortened and cleaned url (for example '2025/02/file.png')
		function clean_url( $url ) {
			$dirIndex = strpos( $url, $this->contentDir );
			if ( empty( $url ) || $dirIndex === false ) {
				$finalUrl =  null;
			}
			else {
				$finalUrl = urldecode( substr( $url, 1 + strlen( $this->contentDir ) + $dirIndex ) );
			}
			return $finalUrl;
		}

	 function call_hooks_rename_url( $post, $orig_image_url, $new_image_url  ) {
		 // With the full URLs
		 do_action( 'mfrh_url_renamed', $post, $orig_image_url, $new_image_url );
		 // With clean URLs relative to /uploads
		 do_action( 'mfrh_url_renamed', $post, $this->clean_url( $orig_image_url ), $this->clean_url( $new_image_url ) );
		 // With DB URLs (honestly, not sure about this...)
		//  $upload_dir = wp_upload_dir();
		//  do_action( 'mfrh_url_renamed', $post, str_replace( $upload_dir, "", $orig_image_url ),
		//  	str_replace( $upload_dir, "", $new_image_url ) );
	 }

	function rename_file( $old, $new, $case_issue = false ) {
		// Some plugins can create custom thumbnail folders instead in the same folder, so make sure
		// the thumbnail folders are available.
		wp_mkdir_p( dirname($new) );

		// If there is a case issue, that means the system doesn't make the difference between AA.jpg and aa.jpg even though WordPress does.
		// In that case it is important to rename the file to a temporary filename in between like: AA.jpg ➡️ TMP.jpg ➡️ aa.jpg.
		if ( $case_issue ) {
			if ( !rename( $old, $old . md5( $old ) ) ) {
				$this->log( "🚫 The file couldn't be renamed (case issue) from $old to " . $old . md5( $old ) . "." );
				return false;
			}
			if ( !rename( $old . md5( $old ), $new ) ) {
				$this->log( "🚫 The file couldn't be renamed (case issue) from " . $old . md5( $old ) . " to $new." );
				return false;
			}
		}
		else if ( ( !rename( $old, $new ) ) ) {
			$this->log( "🚫 The file couldn't be renamed from $old to $new." );
			return false;
		}
		return true;
	}

	function move( $media, $newPath ) {
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
			die( 'Media File Renamer: move() requires the ID or the array for the media.' );
		}

		// Prepare the variables
		$orig_attachment_url = null;
		$old_filepath = get_attached_file( $id );
		$path_parts = mfrh_pathinfo( $old_filepath );
		$old_ext = $path_parts['extension'];
		$upload_dir = wp_upload_dir();
		$old_directory = trim( str_replace( $upload_dir['basedir'], '', $path_parts['dirname'] ), '/' ); // '2011/01'
		$new_directory = trim( $newPath, '/' );
		$filename = $path_parts['basename']; // 'whatever.jpeg'
		$new_filepath = trailingslashit( trailingslashit( $upload_dir['basedir'] ) . $new_directory ) . $filename;

		$this->log( "🏁 Move Media: " . $filename );
		$this->log( "The new directory will be: " . mfrh_dirname( $new_filepath ) );

		// Create the directory if it does not exist
		if ( !file_exists( mfrh_dirname( $new_filepath ) ) ) {
			mkdir( mfrh_dirname( $new_filepath ), 0777, true );
		}

		// There is no support for UNDO (as the current process of Media File Renamer doesn't keep the path for the undo, only the filename... so the move breaks this - let's deal with this later).

		// Move the main media file
		if ( !$this->rename_file( $old_filepath, $new_filepath ) ) {
			$this->log( "🚫 File $old_filepath ➡️ $new_filepath" );
			return false;
		}
		$this->log( "✅ File $old_filepath ➡️ $new_filepath" );
		do_action( 'mfrh_path_renamed', $post, $old_filepath, $new_filepath );

		// Update the attachment meta
		$meta = wp_get_attachment_metadata( $id );

		if ( $meta ) {
			if ( isset( $meta['file'] ) && !empty( $meta['file'] ) )
				$meta['file'] = $this->str_replace( $old_directory, $new_directory, $meta['file'] );
			if ( isset( $meta['url'] ) && !empty( $meta['url'] ) && strlen( $meta['url'] ) > 4 )
				$meta['url'] = $this->str_replace( $old_directory, $new_directory, $meta['url'] );
			//wp_update_attachment_metadata( $id, $meta );
		}

		// Better to check like this rather than with wp_attachment_is_image
		// PDFs also have thumbnails now, since WP 4.7
		$has_thumbnails = isset( $meta['sizes'] );

		if ( $has_thumbnails ) {

			// Support for the original image if it was "-rescaled".
			$is_scaled_image = isset( $meta['original_image'] ) && !empty( $meta['original_image'] );
			if ( $is_scaled_image ) {
				$meta_old_filename = $meta['original_image'];
				$meta_old_filepath = trailingslashit( $upload_dir['basedir'] ) . trailingslashit( $old_directory ) . $meta_old_filename;
				$meta_new_filepath = trailingslashit( $upload_dir['basedir'] ) . trailingslashit( $new_directory ) . $meta_old_filename;
				if ( !$this->rename_file( $meta_old_filepath, $meta_new_filepath ) ) {
					$this->log( "🚫 File $meta_old_filepath ➡️ $meta_new_filepath" );
				}
				else {
					$this->log( "✅ File $meta_old_filepath ➡️ $meta_new_filepath" );
					do_action( 'mfrh_path_renamed', $post, $meta_old_filepath, $meta_new_filepath );
				}
			}

			// Image Sizes (Thumbnails)
			$orig_image_urls = array();
			$orig_image_data = wp_get_attachment_image_src( $id, 'full' );
			$orig_image_urls['full'] = $orig_image_data[0];
			foreach ( $meta['sizes'] as $size => $meta_size ) {
				if ( !isset($meta['sizes'][$size]['file'] ) )
					continue;
				$meta_old_filename = $meta['sizes'][$size]['file'];
				$meta_old_filepath = trailingslashit( $upload_dir['basedir'] ) . trailingslashit( $old_directory ) . $meta_old_filename;
				$meta_new_filepath = trailingslashit( $upload_dir['basedir'] ) . trailingslashit( $new_directory ) . $meta_old_filename;
				$orig_image_data = wp_get_attachment_image_src( $id, $size );
				$orig_image_urls[$size] = $orig_image_data[0];

				// Double check files exist before trying to rename.
				if ( file_exists( $meta_old_filepath )
						&& ( ( !file_exists( $meta_new_filepath ) ) || is_writable( $meta_new_filepath ) ) ) {
					// WP Retina 2x is detected, let's rename those files as well
					if ( function_exists( 'wr2x_get_retina' ) ) {
						$wr2x_old_filepath = $this->str_replace( '.' . $old_ext, '@2x.' . $old_ext, $meta_old_filepath );
						$wr2x_new_filepath = $this->str_replace( '.' . $old_ext, '@2x.' . $old_ext, $meta_new_filepath );
						if ( file_exists( $wr2x_old_filepath )
							&& ( ( !file_exists( $wr2x_new_filepath ) ) || is_writable( $wr2x_new_filepath ) ) ) {

							// Rename retina file
							if ( !$this->rename_file( $wr2x_old_filepath, $wr2x_new_filepath ) ) {
								$this->log( "🚫 Retina $wr2x_old_filepath ➡️ $wr2x_new_filepath" );
								return $post;
							}
							$this->log( "✅ Retina $wr2x_old_filepath ➡️ $wr2x_new_filepath" );
							do_action( 'mfrh_path_renamed', $post, $wr2x_old_filepath, $wr2x_new_filepath );
						}
					}

					// Rename meta file
					if ( !$this->rename_file( $meta_old_filepath, $meta_new_filepath ) ) {
						$this->log( "🚫 File $meta_old_filepath ➡️ $meta_new_filepath" );
						return false;
					}

					// Success, call other plugins
					$this->log( "✅ File $meta_old_filepath ➡️ $meta_new_filepath" );
					do_action( 'mfrh_path_renamed', $post, $meta_old_filepath, $meta_new_filepath );

				}
			}
		}
		else {
			$orig_attachment_url = wp_get_attachment_url( $id );
		}

		// Update DB: Media and Metadata
		update_attached_file( $id, $new_filepath );
		if ( $meta ) {
			wp_update_attachment_metadata( $id, $meta );
		}
		clean_post_cache( $id ); // TODO: Would be good to know what this WP function actually does (might be useless)

		// Post actions
		$this->call_post_actions( $id, $post, $meta, $has_thumbnails, $orig_image_urls, $orig_attachment_url );
		do_action( 'mfrh_media_renamed', $post, $old_filepath, $new_filepath, false );
		return true;
	}

	// Call the actions so that the plugin's plugins can update everything else (than the files)
	// Called by rename() and move()
	function call_post_actions( $id, $post, $meta, $has_thumbnails, $orig_image_urls, $orig_attachment_url ) {
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
	}
	
	function undo( $mediaId ) {
		$original_filename = get_post_meta( $mediaId, '_original_filename', true );
		if ( empty( $original_filename ) ) {
			return true;
		}
		$res = $this->rename( $mediaId, $original_filename, true );
		if (!!$res) {
			delete_post_meta( $mediaId, '_original_filename' );
		}
		return $res;
	}

	function rename( $media, $manual_filename = null, $undo = false ) {
		$id = null;
		$post = null;

		// This filter permits developers to allow or not the renaming of certain files.
		$allowed = apply_filters( 'mfrh_allow_rename', true, $media, $manual_filename );
		if ( !$allowed ) {
			return $post;
		}

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

		// Check attachment
		$need_rename = $this->check_attachment( $post, $output, $manual_filename );
		if ( !$need_rename ) {
			delete_post_meta( $id, '_require_file_renaming' );
			return $post;
		}

		// Prepare the variables
		$orig_attachment_url = null;
		$old_filepath = $output['current_filepath'];
		$case_issue = $output['case_issue'];
		$new_filepath = $output['desired_filepath'];
		$new_filename = $output['proposed_filename'];
		$manual = $output['manual'] || !empty( $manual_filename );
		$path_parts = mfrh_pathinfo( $old_filepath );
		$directory = $path_parts['dirname']; // Directory where the files are, under 'uploads', such as '2011/01'
		$old_filename = $path_parts['basename']; // 'whatever.jpeg'
		// Get old extension and new extension
		$old_ext = $path_parts['extension'];
		$new_ext = $old_ext;
		if ( $manual_filename ) {
			$pp = mfrh_pathinfo( $manual_filename );
			$new_ext = $pp['extension'];
		}
		$noext_old_filename = $this->str_replace( '.' . $old_ext, '', $old_filename ); // Old filename without extension
		$noext_new_filename = $this->str_replace( '.' . $old_ext, '', $new_filename ); // New filename without extension


		$this->log( "🏁 Rename Media: " . $old_filename );
		$this->log( "New file will be: " . $new_filename );

		// Check for issues with the files
		if ( !file_exists( $old_filepath ) ) {
			$this->log( "The original file ($old_filepath) cannot be found." );
			return $post;
		}

		// Get the attachment meta
		$meta = wp_get_attachment_metadata( $id );

		// Get the information about the original image
		// (which means the current file is a rescaled version of it)
		$is_scaled_image = isset( $meta['original_image'] ) && !empty( $meta['original_image'] );
		$original_is_ideal = $is_scaled_image ? $new_filename === $meta['original_image'] : false;

		if ( !$original_is_ideal && !$case_issue && !$force_rename && file_exists( $new_filepath ) ) {
			$this->log( "The new file already exists ($new_filepath). It is not a case issue. Renaming cancelled." );
			return $post;
		}

		// Keep the original filename (that's for the "Undo" feature)
		$original_filename = get_post_meta( $id, '_original_filename', true );
		if ( empty( $original_filename ) )
			add_post_meta( $id, '_original_filename', $old_filename, true );

		// Support for the original image if it was "-rescaled".
		// We should rename the -rescaled image first, as it could cause an issue
		// if renamed after the main file. In fact, the original file might have already
		// the best filename and evidently, the "-rescaled" one not.
		if ( $is_scaled_image ) {
			$meta_old_filename = $meta['original_image'];
			$meta_old_filepath = trailingslashit( $directory ) . $meta_old_filename;
			// In case of the undo, since we do not have the actual real original filename for that un-scaled image,
			// we make sure the -scaled part of the original filename is not used (that could bring some confusion otherwise).
			$meta_new_filename = preg_replace( '/\-scaled$/', '', $noext_new_filename ) . '-mfrh-original.' . $new_ext;
			$meta_new_filepath = trailingslashit( $directory ) . $meta_new_filename;
			if ( !$this->rename_file( $meta_old_filepath, $meta_new_filepath, $case_issue ) && !$force_rename ) {
				$this->log( "🚫 File $meta_old_filepath ➡️ $meta_new_filepath" );
				return $post;
			}
			// Manual Rename also uses the new extension (if it was not stripped to avoid user mistake)
			if ( $force_rename && !empty( $new_ext ) ) {
				$meta_new_filename = $this->str_replace( $old_ext, $new_ext, $meta_new_filename );
			}
			$this->log( "✅ File $old_filepath ➡️ $new_filepath" );
			do_action( 'mfrh_path_renamed', $post, $old_filepath, $new_filepath );
			$meta['original_image'] = $meta_new_filename;
		}

		// Rename the main media file.
		if ( !$this->rename_file( $old_filepath, $new_filepath, $case_issue ) && !$force_rename ) {
			$this->log( "🚫 File $old_filepath ➡️ $new_filepath" );
			return $post;
		}
		$this->log( "✅ File $old_filepath ➡️ $new_filepath" );
		do_action( 'mfrh_path_renamed', $post, $old_filepath, $new_filepath );

		// Rename the main media file in WebP if it exists.
		$this->rename_webp_file_if_exist( $old_filepath, $old_ext, $new_filepath,
			$new_ext, $case_issue, $force_rename, $post );

		if ( $meta ) {
			if ( isset( $meta['file'] ) && !empty( $meta['file'] ) )
				$meta['file'] = $this->str_replace( $noext_old_filename, $noext_new_filename, $meta['file'] );
			if ( isset( $meta['url'] ) && !empty( $meta['url'] ) && strlen( $meta['url'] ) > 4 )
				$meta['url'] = $this->str_replace( $noext_old_filename, $noext_new_filename, $meta['url'] );
			else
				$meta['url'] = $noext_new_filename . '.' . $old_ext;
		}

		// Better to check like this rather than with wp_attachment_is_image
		// PDFs also have thumbnails now, since WP 4.7
		$has_thumbnails = isset( $meta['sizes'] );

		// Loop through the different sizes in the case of an image, and rename them.
		if ( $has_thumbnails ) {

			// In the case of a -scaled image, we need to update the next_old_filename.
			// next_old_filename is based on the filename of the main file, but since
			// it contains '-scaled' but not its thumbnails, we need to modify it here.
			// $noext_new_filename is to support this in case of undo.
			if ( $is_scaled_image ) {
				$noext_new_filename = preg_replace( '/\-scaled$/', '', $noext_new_filename );
				$noext_old_filename = preg_replace( '/\-scaled$/', '', $noext_old_filename );
			}

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
				if ( $force_rename || ( file_exists( $meta_old_filepath ) && 
						( ( !file_exists( $meta_new_filepath ) ) || is_writable( $meta_new_filepath ) ) ) ) {
					// WP Retina 2x is detected, let's rename those files as well
					if ( function_exists( 'wr2x_get_retina' ) ) {
						$wr2x_old_filepath = $this->str_replace( '.' . $old_ext, '@2x.' . $old_ext, $meta_old_filepath );
						$wr2x_new_filepath = $this->str_replace( '.' . $new_ext, '@2x.' . $new_ext, $meta_new_filepath );
						if ( file_exists( $wr2x_old_filepath )
							&& ( ( !file_exists( $wr2x_new_filepath ) ) || is_writable( $wr2x_new_filepath ) ) ) {

							// Rename retina file
							if ( !$this->rename_file( $wr2x_old_filepath, $wr2x_new_filepath, $case_issue ) && !$force_rename ) {
								$this->log( "🚫 Retina $wr2x_old_filepath ➡️ $wr2x_new_filepath" );
								return $post;
							}
							$this->log( "✅ Retina $wr2x_old_filepath ➡️ $wr2x_new_filepath" );
							do_action( 'mfrh_path_renamed', $post, $wr2x_old_filepath, $wr2x_new_filepath );
						}
					}
					// If webp file existed, that one as well.
					$this->rename_webp_file_if_exist( $meta_old_filepath, $old_ext, $meta_new_filepath,
						$new_ext, $case_issue, $force_rename, $post );

					// Rename meta file
					if ( !$this->rename_file( $meta_old_filepath, $meta_new_filepath, $case_issue ) && !$force_rename ) {
						$this->log( "🚫 File $meta_old_filepath ➡️ $meta_new_filepath" );
						return $post;
					}

					$meta['sizes'][$size]['file'] = $meta_new_filename;
					foreach ( $meta['sizes'] as $s => $m ) {
						// Detect if another size has exactly the same filename
						if ( !isset( $meta['sizes'][$s]['file'] ) )
							continue;
						if ( $meta['sizes'][$s]['file'] ==  $meta_old_filename ) {
							$this->log( "✅ Updated $s based on $size, as they use the same file (probably same size)." );
							$meta['sizes'][$s]['file'] = $meta_new_filename;
						}
					}

					// Success, call other plugins
					$this->log( "✅ File $meta_old_filepath ➡️ $meta_new_filepath" );
					do_action( 'mfrh_path_renamed', $post, $meta_old_filepath, $meta_new_filepath );

				}
			}
		}
		else {
			$orig_attachment_url = wp_get_attachment_url( $id );
		}

		// Update Renamer Meta
		delete_post_meta( $id, '_require_file_renaming' ); // This media doesn't require renaming anymore
		if ( $manual ) // If it was renamed manually (including undo), lock the file
			add_post_meta( $id, '_manual_file_renaming', true, true ); 

		// Update DB: Media and Metadata
		if ( $meta )
			wp_update_attachment_metadata( $id, $meta );
		update_attached_file( $id, $new_filepath );
		clean_post_cache( $id ); // TODO: Would be good to know what this WP function actually does (might be useless)

		// Rename slug/permalink
		if ( get_option( "mfrh_rename_slug" ) ) {
			$oldslug = $post['post_name'];
			$info = mfrh_pathinfo( $new_filepath );
			$newslug = preg_replace( '/\\.[^.\\s]{3,4}$/', '', $info['basename'] );
			$post['post_name'] = $newslug;
			if ( wp_update_post( $post ) )
				$this->log( "🚀 Slug $oldslug ➡️ $newslug" );
		}

		// Post actions
		$this->call_post_actions( $id, $post, $meta, $has_thumbnails, $orig_image_urls, $orig_attachment_url );
		do_action( 'mfrh_media_renamed', $post, $old_filepath, $new_filepath, $undo );
		return $post;
	}

	/**
	 * Rename webp file only if existed.
	 */
	function rename_webp_file_if_exist( $old_filepath, $old_ext, $new_finepath, 
		$new_ext, $case_issue, $force_rename, $post ) {

		// Two WebP patterns exist: filename.webp and filename.ext.webp

		if ( $old_ext === 'pdf' & $new_ext === 'pdf' ) {
			$old_ext = 'jpg';
			$new_ext = 'jpg';
		} 

		$webps = [
			[
				'old' => $this->str_replace( '.' . $old_ext, '.webp', $old_filepath ),
				'new' => $this->str_replace( '.' . $new_ext, '.webp', $new_finepath ),
			],
			[
				'old' => $this->str_replace( '.' . $old_ext, '.' . $old_ext . '.webp', $old_filepath ),
				'new' => $this->str_replace( '.' . $new_ext, '.' . $new_ext . '.webp', $new_finepath ),
			],
		];

		// // TODO: Without this check, the code following actually doesn't work with PDF Thumbnails (because the old_ext and new_ext doesn't correspond to jpg, which is used for the thumbnails in the PDF case, and not .pdf). In fact, the code after that should be rewritten.
		// if ( !preg_match( '/\.webp$/', $old_filepath ) ) {
		// 	return;
		// }

		foreach ( $webps as $webp ) {
			$is_webp = preg_match( '/\.webp$/', $webp['old'] );
			$old_file_ok = $is_webp && file_exists( $webp['old'] );
			$new_file_ok = ( ( !file_exists( $webp['new'] ) ) || is_writable( $webp['new'] ) );

			if ( $old_file_ok && $new_file_ok ) {
				// Rename webp file
				if ( !$this->rename_file( $webp['old'], $webp['new'], $case_issue ) && !$force_rename ) {
					$this->log( "🚫 WebP $webp[old] ➡️ $webp[new]" );
					return $post;
				}
				$this->log( "✅ WebP $webp[old] ➡️ $webp[new]" );
				do_action( 'mfrh_path_renamed', $post, $webp['old'], $webp['new'] );
			}
		}
	}

	/**
	 * Locks a post to be manual-rename only
	 * @param int|WP_Post $post The post to lock
	 * @return True on success, false on failure
	 */
	function lock( $post ) {
		//TODO: We should probably only take an ID as the argument
		$id = $post instanceof WP_Post ? $post->ID : $post;
		delete_post_meta( $id, '_require_file_renaming' );
		update_post_meta( $id, '_manual_file_renaming', true, true );
		return true;
	}

	/**
	 * Unlocks a locked post
	 * @param int|WP_Post $post The post to unlock
	 * @return True on success, false on failure
	 */
	function unlock( $post ) {
		delete_post_meta( $post instanceof WP_Post ? $post->ID : $post, '_manual_file_renaming' );
		return true;
	}

	/**
	 * Determines whether a post is locked
	 * @param int|WP_Post $post The post to check
	 * @return Boolean
	 */
	function is_locked( $post ) {
		return get_post_meta( $post instanceof WP_Post ? $post->ID : $post, '_manual_file_renaming', true ) === true;
	}
}
