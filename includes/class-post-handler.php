<?php
/**
 * Handles posting to Mastodon and the like.
 *
 * @package Share_On_Twitter
 */

namespace Share_On_Twitter;

use \Share_On_Twitter\Abraham\TwitterOAuth\TwitterOAuth;

/**
 * Post handler class.
 */
class Post_Handler {
	/**
	 * Array that holds this plugin's settings.
	 *
	 * @since 0.1.0
	 *
	 * @var array $options Plugin options.
	 */
	private $options = array();

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param Options_Handler $options_handler This plugin's `Options_Handler`.
	 */
	public function __construct( Options_Handler $options_handler = null ) {
		if ( null !== $options_handler ) {
			$this->options = $options_handler->get_options();
		}
	}

	/**
	 * Interacts with WordPress's Plugin API.
	 *
	 * @since 0.1.0
	 */
	public function register() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'transition_post_status', array( $this, 'update_meta' ), 11, 3 );
		add_action( 'transition_post_status', array( $this, 'tweet' ), 999, 3 ); // After the previous function's run.

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_share_on_twitter_unlink_url', array( $this, 'unlink_url' ) );
	}

	/**
	 * Registers a new meta box.
	 *
	 * @since 0.1.0
	 */
	public function add_meta_box() {
		if ( empty( $this->options['post_types'] ) ) {
			// Sharing disabled for all post types.
			return;
		}

		// Add meta box, for those post types that are supported.
		add_meta_box(
			'share-on-twitter',
			__( 'Share on Twitter', 'share-on-twitter' ),
			array( $this, 'render_meta_box' ),
			(array) $this->options['post_types'],
			'side',
			'default'
		);
	}

	/**
	 * Renders meta box.
	 *
	 * @since 0.1.0
	 *
	 * @param WP_Post $post Post being edited.
	 */
	public function render_meta_box( $post ) {
		wp_nonce_field( basename( __FILE__ ), 'share_on_twitter_nonce' ); ?>
		<label>
			<input type="checkbox" name="share_on_twitter" value="1" <?php checked( in_array( get_post_meta( $post->ID, '_share_on_twitter', true ), array( '', '1' ), true ) ); ?>>
			<?php esc_html_e( 'Share on Twitter', 'share-on-twitter' ); ?>
		</label>
		<?php
		$url = get_post_meta( $post->ID, '_share_on_twitter_url', true );

		if ( '' !== $url && false !== wp_http_validate_url( $url ) ) :
			$url_parts = wp_parse_url( $url );

			$display_url  = '<span class="screen-reader-text">' . $url_parts['scheme'] . '://';
			$display_url .= ( ! empty( $url_parts['user'] ) ? $url_parts['user'] . ( ! empty( $url_parts['pass'] ) ? ':' . $url_parts['pass'] : '' ) . '@' : '' ) . '</span>';
			$display_url .= '<span class="ellipsis">' . substr( $url_parts['host'] . $url_parts['path'], 0, 20 ) . '</span><span class="screen-reader-text">' . substr( $url_parts['host'] . $url_parts['path'], 20 ) . '</span>';
			?>
			<p class="description">
				<?php /* translators: toot URL */ ?>
				<?php printf( esc_html__( 'Shared at %s', 'share-on-twitter' ), '<a class="url" href="' . esc_url( $url ) . '">' . $display_url . '</a>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php /* translators: "unlink" link text */ ?>
				<a href="#" class="unlink"><?php esc_html_e( 'Unlink', 'share-on-twitter' ); ?></a>
			</p>
			<?php
		endif;
	}

	/**
	 * Deletes a post's Twitter URL.
	 *
	 * Should only ever be called through AJAX.
	 *
	 * @since 0.1.0
	 */
	public function unlink_url() {
		if ( ! isset( $_POST['share_on_twitter_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['share_on_twitter_nonce'] ), basename( __FILE__ ) ) ) {
			status_header( 400 );
			esc_html_e( 'Missing or invalid nonce.', 'share-on-twitter' );
			wp_die();
		}

		if ( ! isset( $_POST['post_id'] ) || ! ctype_digit( $_POST['post_id'] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			status_header( 400 );
			esc_html_e( 'Missing or incorrect post ID.', 'share-on-twitter' );
			wp_die();
		}

		if ( ! current_user_can( 'edit_post', intval( $_POST['post_id'] ) ) ) {
			status_header( 400 );
			esc_html_e( 'Insufficient rights.', 'share-on-twitter' );
			wp_die();
		}

		// Have WordPress forget the Mastodon URL.
		if ( '' !== get_post_meta( intval( $_POST['post_id'] ), '_share_on_twitter_url', true ) ) {
			delete_post_meta( intval( $_POST['post_id'] ), '_share_on_twitter_url' );
		}

		wp_die();
	}

	/**
	 * Adds admin scripts and styles.
	 *
	 * @since 0.1.0
	 *
	 * @param string $hook_suffix Current WP-Admin page.
	 */
	public function enqueue_scripts( $hook_suffix ) {
		if ( 'post-new.php' !== $hook_suffix && 'post.php' !== $hook_suffix ) {
			// Not an "Edit Post" screen.
			return;
		}

		global $post;

		if ( empty( $post ) ) {
			// Can't do much without a `$post` object.
			return;
		}

		if ( ! in_array( $post->post_type, (array) $this->options['post_types'], true ) ) {
			// Unsupported post type.
			return;
		}

		// Enqueue CSS and JS.
		wp_enqueue_style( 'share-on-twitter', plugins_url( '/assets/share-on-twitter.css', dirname( __FILE__ ) ), array(), '0.6.1' );
		wp_enqueue_script( 'share-on-twitter', plugins_url( '/assets/share-on-twitter.js', dirname( __FILE__ ) ), array( 'jquery' ), '0.6.1', false );
		wp_localize_script(
			'share-on-twitter',
			'share_on_twitter_obj',
			array(
				'message' => esc_attr__( 'Forget this URL?', 'share-on-twitter' ), // Confirmation message.
				'post_id' => $post->ID, // Pass current post ID to JS.
			)
		);
	}

	/**
	 * Handles metadata.
	 *
	 * @since 0.1.0
	 *
	 * @param string  $new_status Old post status.
	 * @param string  $old_status New post status.
	 * @param WP_Post $post       Post object.
	 */
	public function update_meta( $new_status, $old_status, $post ) {
		if ( wp_is_post_revision( $post->ID ) || wp_is_post_autosave( $post->ID ) ) {
			// Prevent double posting.
			return;
		}

		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return;
		}

		if ( ! isset( $_POST['share_on_twitter_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['share_on_twitter_nonce'] ), basename( __FILE__ ) ) ) {
			// Nonce missing or invalid.
			return;
		}

		if ( ! in_array( $post->post_type, (array) $this->options['post_types'], true ) ) {
			// Unsupported post type.
			return;
		}

		if ( isset( $_POST['share_on_twitter'] ) && ! post_password_required( $post ) ) {
			// If sharing enabled and post not password-protected.
			update_post_meta( $post->ID, '_share_on_twitter', '1' );
		} else {
			update_post_meta( $post->ID, '_share_on_twitter', '0' );
		}
	}

	/**
	 * Shares a post on Twitter.
	 *
	 * @since 0.1.0
	 *
	 * @param string  $new_status New post status.
	 * @param string  $old_status Old post status.
	 * @param WP_Post $post       Post object.
	 */
	public function tweet( $new_status, $old_status, $post ) {
		if ( wp_is_post_revision( $post->ID ) || wp_is_post_autosave( $post->ID ) ) {
			// Prevent accidental double posting.
			return;
		}

		$is_enabled = ( '1' === get_post_meta( $post->ID, '_share_on_twitter', true ) ? true : false );

		if ( ! apply_filters( 'share_on_twitter_enabled', $is_enabled, $post->ID ) ) {
			// Disabled for this post.
			return;
		}

		if ( '' !== get_post_meta( $post->ID, '_share_on_twitter_url', true ) ) {
			// Prevent duplicate toots.
			return;
		}

		if ( 'publish' !== $new_status ) {
			// Status is something other than `publish`.
			return;
		}

		if ( post_password_required( $post ) ) {
			// Post is password-protected.
			return;
		}

		if ( ! in_array( $post->post_type, (array) $this->options['post_types'], true ) ) {
			// Unsupported post type.
			return;
		}

		if ( empty( $this->options['twitter_api_key'] ) ) {
			return;
		}

		if ( empty( $this->options['twitter_api_secret'] ) ) {
			return;
		}

		if ( empty( $this->options['twitter_access_token'] ) ) {
			return;
		}

		if ( empty( $this->options['twitter_access_token_secret'] ) ) {
			return;
		}

		$status  = wp_strip_all_tags(
			html_entity_decode( get_the_title( $post->ID ), ENT_QUOTES | ENT_HTML5, get_bloginfo( 'charset' ) ) // Avoid double-encoded HTML entities.
		);
		$status .= ' ' . esc_url_raw( get_permalink( $post->ID ) );

		$status = apply_filters( 'share_on_twitter_status', $status, $post );
		$args   = apply_filters( 'share_on_twitter_tweet_args', array( 'status' => $status ) );

		if ( apply_filters( 'share_on_twitter_cutoff', false ) ) {
			// May render hashtags or URLs, or unfiltered HTML, at the very end
			// of a toot unusable. Also, Mastodon may not even use a multibyte
			// check. To do: test better?
			$args['status'] = mb_substr( $args['status'], 0, 278, get_bloginfo( 'charset' ) ) . ' …';
		}

		// And now, images.
		$thumbnail = null;
		$media     = array();

		if ( has_post_thumbnail( $post->ID ) && apply_filters( 'share_on_twitter_featured_image', true, $post ) ) {
			// Include featured image.
			$thumbnail = get_post_thumbnail_id( $post->ID );
			$media[]   = $thumbnail;
		}

		if ( apply_filters( 'share_on_twitter_attached_images', true, $post ) ) {
			// Include all attached images.
			$images = get_attached_media( 'image', $post->ID );

			if ( ! empty( $images ) && is_array( $images ) ) {
				foreach ( $images as $image ) {
					// Skip the post's featured image, which we tackle
					// separately.
					if ( ! empty( $thumbnail ) && $thumbnail === $image->ID ) {
						continue;
					}

					$media[] = $image->ID;
				}
			}
		}

		if ( ! empty( $media ) ) {
			// Loop through the resulting image IDs.
			for ( $i = 0; $i < 4; $i++ ) {
				$media_id = $this->upload_image( $media[ $i ], $connection );

				if ( ! empty( $media_id ) ) {
					// The image got uploaded OK.
					$media_ids[] = $media_id;
				}
			}
		}

		if ( ! empty( $media_ids ) ) {
			$args['media_ids'] = implode( ',', $media_ids );
		}

		$connection = new TwitterOAuth(
			$this->options['twitter_api_key'],
			$this->options['twitter_api_secret'],
			$this->options['twitter_access_token'],
			$this->options['twitter_access_token_secret']
		);

		$response = $connection->post(
			'statuses/update',
			$args
		);

		if ( ! empty( $response->id_str ) && post_type_supports( $post->post_type, 'custom-fields' ) ) {
			update_post_meta( $post->ID, '_share_on_twitter_id', $response->id_str );

			if ( ! empty( $this->options['twitter_username'] ) ) {
				update_post_meta( $post->ID, '_share_on_twitter_url', 'https://twitter.com/' . $this->options['twitter_username'] . '/status/' . $response->id_str );
			}
		} else {
			// Provided debugging's enabled, let's store the ( somehow faulty )
			// response.
			error_log( print_r( $response, true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
		}
	}

	/**
	 * Uploads an image and returns a ( single ) media ID.
	 *
	 * @since  0.1.0
	 *
	 * @param int          $image_id   Image ID.
	 * @param TwitterOAuth $connection TwitterOAuth object.
	 *
	 * @return string|null Unique media ID, or nothing on failure.
	 */
	private function upload_image( $image_id, $connection ) {
		$url   = '';
		$image = wp_get_attachment_image_src( $image_id, 'large' );

		if ( ! empty( $image[0] ) ) {
			$url = $image[0];
		} else {
			// Get the original image URL.
			$url = wp_get_attachment_url( $image_id );
		}

		$uploads   = wp_upload_dir();
		$file_path = str_replace( $uploads['baseurl'], $uploads['basedir'], $url );

		if ( ! is_file( $file_path ) ) {
			// File doesn't seem to exist.
			return;
		}

		$response = $connection->upload(
			'media/upload',
			array(
				'media' => $file_path,
			)
		);

		if ( ! empty( $response->media_id_string ) ) {
			return $response->media_id_string;
		}

		// Provided debugging's enabled, let's store the ( somehow faulty )
		// response.
		error_log( print_r( $response, true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
	}
}
