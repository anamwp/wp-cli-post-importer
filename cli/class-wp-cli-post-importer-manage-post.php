<?php
/**
 * Summary.
 *
 * Description.
 *
 * @since Version 3 digits
 */
class WP_CLI_Post_Importer_Manage_Post {
	/**
	 * API URL
	 *
	 * @var string
	 */
	private static $api_url = 'https://dummyjson.com/posts';
	/**
	 * Initiate class
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}
	/**
	 * Initiate WP CLI commands
	 */
	public function init() {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command( 'start import-posts', array( $this, 'import_posts' ) );
			WP_CLI::add_command( 'start delete-posts', array( $this, 'delete_posts' ) );
		}
	}
	/**
	 * Fetch products from dummyjson.com/products
	 *
	 * @return Array
	 */
	public function gs_fetch_posts_from_api() {
		$response = wp_remote_get( self::$api_url );
		if ( is_wp_error( $response ) ) {
			\WP_CLI::error( 'Failed to fetch data from the API' );
			return array();
		}
		$response_body     = wp_remote_retrieve_body( $response );
		$response_body_obj = json_decode( $response_body, true );
		$post_arr          = $response_body_obj['posts'];
		/**
		 * If no posts found then return error
		 */
		if ( empty( $post_arr ) ) {
			\WP_CLI::error( 'No posts found' );
			return array();
		}
		return $post_arr;
	}
	/**
	 * Check if product exists
	 *
	 * @param string $post_title - post title.
	 * @return Array
	 */
	public function gs_check_post_exists( $post_title ) {
		$post_id    = '';
		$query_args = array(
			'post_type'      => 'post',
			'post_status'    => 'any',
			'title'          => $post_title,
			'posts_per_page' => 1,
		);

		// Query the database.
		$query = new \WP_Query( $query_args );

		// Check if a post was found.
		if ( $query->have_posts() ) {
			$query->the_post();
			$post_id = get_the_ID();
			wp_reset_postdata();
			return array(
				'post_id'     => $post_id,
				'post_status' => true,
			);
		} else {
			return array(
				'post_id'     => false,
				'post_status' => false,
			);
		}
	}
	/**
	 * Manage posts to insert
	 *
	 * @param [array] $post - post data array.
	 */
	private function gs_manage_posts( $post ) {
		$post_exists = $this->gs_check_post_exists( $post['title'] );
		if ( $post_exists['post_status'] ) {
			\WP_CLI::warning( 'Post already exists: ' . $post['title'] );
			return false;
		} else {
			\WP_CLI::line( 'Importing post: ' . $post['title'] );
			$post_id = wp_insert_post(
				array(
					'post_title'   => $post['title'],
					'post_content' => $post['body'],
					'post_status'  => 'publish',
					'post_author'  => 1,
					'post_type'    => 'post',
				)
			);
			/**
			 * If something wrong then return false
			 */
			if ( is_wp_error( $post_id ) || ! $post_id ) {
				\WP_CLI::warning( 'Failed to insert post: ' . $post['title'] );
				return false;
			}
			$this->handle_featured_image_insert( $post_id, $post['title'], 1, $post['title'], $post['title'] );
			\WP_CLI::log( "✅ Post is inserted. Now adding Meta for - {$post['title']}" );

			if ( ! empty( $post['tags'] ) ) {
				$tag_assign_status = wp_set_object_terms( $post_id, $post['tags'], 'post_tag' );
				if ( is_wp_error( $tag_assign_status ) ) {
					\WP_CLI::warning( 'Failed to assign tags: ' . $post['category'] );
				} else {
					\WP_CLI::log( '✅ Tag assigned on - ' . $post['title'] );
				}
			}
			/**
			 * Log the info in the wp-cli
			 */
			\WP_CLI::success( 'Post inserted: -' . $post['title'] );
		}
	}
	/**
	 * Handle the insertion of a featured image for a post.
	 *
	 * @param [string] $post_id - post id.
	 * @param [string] $post_title - post title.
	 * @return void
	 */
	private function handle_featured_image_insert( $post_id, $post_title, $post_author_id = 1, $alt_text = '', $caption = '' ) {
		global $wp_filesystem;
		// Initialize the WordPress filesystem.
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}
		// Set the dimensions of the placeholder image.
		$width  = 1200;
		$height = 800;

		// Generate a random image URL from Picsum.photos.
		$random_id = wp_rand( 1, 10000 );
		$image_url = "https://picsum.photos/{$width}/{$height}?random={$random_id}";

		// Fetch the image content to resolve redirects.
		$response = wp_remote_get( $image_url, array( 'redirection' => 5 ) );

		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
			WP_CLI::warning( "❌ Failed to fetch image from Picsum.photos for post {$post_id}." );
			return;
		}

		// Get the body content (image binary).
		$image_data = wp_remote_retrieve_body( $response );

		// Generate a temporary file name for the image.
		$temp_file = wp_tempnam( $image_url ); // Returns a filename of a temporary unique file.
		if ( ! $temp_file ) {
			WP_CLI::warning( "❌ Failed to create temporary file for image for post {$post_id}." );
			return;
		}

		// Write the image content to the temporary file.
		// file_put_contents( $temp_file, $image_data );

		// Use WP_Filesystem to write the image data to the temporary file.
		if ( ! $wp_filesystem->put_contents( $temp_file, $image_data, FS_CHMOD_FILE ) ) {
			WP_CLI::warning( '❌ Failed to write image data to temporary file using WP_Filesystem.' );
			return;
		}

		// Use the temporary file to upload the image.
		$file_array = array(
			'name'     => "picsum-{$random_id}.jpg",
			'tmp_name' => $temp_file,
		);

		$image_id = media_handle_sideload( $file_array, $post_id, $post_title );

		// Clean up the temporary file.
		wp_delete_file( $temp_file );

		if ( is_wp_error( $image_id ) ) {
			WP_CLI::warning( "❌ Failed to download image for post {$post_id}: " . $image_id->get_error_message() );
			return;
		}

		// Update the author of the uploaded image
		wp_update_post([
			'ID'          => $image_id,
			'post_author' => $post_author_id,
			'post_excerpt' => $caption,
			'post_content' => $caption,
		]);
		// Update the alt text.
		if (!empty( $alt_text ) ) {
			update_post_meta( $image_id, '_wp_attachment_image_alt', sanitize_text_field( $alt_text ) );
		}

		// Set the image as the featured image for the post.
		set_post_thumbnail( $post_id, $image_id );
		WP_CLI::line( "✅ Assigned featured image for post {$post_title}" );
	}
	/**
	 * Run the import process
	 */
	public function import_posts() {
		$posts_arr = $this->gs_fetch_posts_from_api();
		if ( empty( $posts_arr ) ) {
			\WP_CLI::error( '❗️ No posts found' );
		}
		foreach ( $posts_arr as $post ) {
			if ( $post ) {
				$this->gs_manage_posts( $post );
			}
		}
	}
	/**
	 * Run the delete process
	 *
	 * @return void
	 */
	public function delete_posts() {
		$posts_arr = $this->gs_fetch_posts_from_api();
		if ( empty( $posts_arr ) ) {
			\WP_CLI::error( '❗️ No posts found' );
		}
		foreach ( $posts_arr as $post ) {
			if ( $post ) {
				$post_exists = $this->gs_check_post_exists( $post['title'] );

				if ( $post_exists['post_status'] ) {
					$this->manage_delete_posts( $post_exists['post_id'] );
				} else {
					\WP_CLI::warning( '❗️ Post not found: ' . $post['title'] );
				}
			}
		}
	}
	/**
	 * Manage single delete post
	 *
	 * @param [string] $post_id - post id.
	 * @return void
	 */
	private function manage_delete_posts( $post_id ) {
		// Get all attachments associated with the post
		$attachments = get_attached_media('', $post_id);

		// Delete each attachment
		\WP_CLI::line( 'Deleting media for post - ' . $post_id );
		foreach ($attachments as $attachment) {
			wp_delete_attachment($attachment->ID, true); // 'true' ensures permanent deletion
		}
		/**
		 * Delete the post
		 */
		wp_delete_post( $post_id, true );
		\WP_CLI::success( '✅ Product deleted: ' . $post_id );
	}
}
