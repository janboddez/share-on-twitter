<?php
/**
 * Handles WP Admin settings pages and the like.
 *
 * @package Share_On_Twitter
 */

namespace Share_On_Twitter;

/**
 * Options handler class.
 */
class Options_Handler {
	/**
	 * Plugin options.
	 *
	 * @since 0.1.0
	 *
	 * @var array $options Plugin options.
	 */
	private $options = array(
		'twitter_api_key'             => '',
		'twitter_api_secret'          => '',
		'twitter_access_token'        => '',
		'twitter_access_token_secret' => '',
		'twitter_username'            => '',
		'twitter_use_v2_api'          => false,
		'post_types'                  => array(),
	);

	/**
	 * WordPress's default post types.
	 *
	 * @since 0.1.0
	 *
	 * @var array Post types that should never be crossposted.
	 */
	const DEFAULT_POST_TYPES = array(
		'page',
		'attachment',
		'revision',
		'nav_menu_item',
		'custom_css',
		'customize_changeset',
		'user_request',
		'oembed_cache',
		'wp_block',
		'wp_template',
		'wp_template_part',
		'wp_global_styles',
		'wp_navigation',
		'genesis_custom_block', // Not default, but whatever.
	);

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		$this->options = get_option(
			'share_on_twitter_settings',
			$this->options
		);
	}

	/**
	 * Interacts with WordPress's Plugin API.
	 *
	 * @since 0.1.0
	 */
	public function register() {
		add_action( 'admin_menu', array( $this, 'create_menu' ) );
	}

	/**
	 * Registers the plugin settings page.
	 *
	 * @since 0.1.0
	 */
	public function create_menu() {
		add_options_page(
			__( 'Share on Twitter', 'share-on-twitter' ),
			__( 'Share on Twitter', 'share-on-twitter' ),
			'manage_options',
			'share-on-twitter',
			array( $this, 'settings_page' )
		);
		add_action( 'admin_init', array( $this, 'add_settings' ) );
	}

	/**
	 * Registers the actual options.
	 *
	 * @since 0.1.0
	 */
	public function add_settings() {
		register_setting(
			'share-on-twitter-settings-group',
			'share_on_twitter_settings',
			array( 'sanitize_callback' => array( $this, 'sanitize_settings' ) )
		);
	}

	/**
	 * Handles submitted options.
	 *
	 * @since 0.1.0
	 *
	 * @param array $settings Settings as submitted through WP Admin.
	 *
	 * @return array Options to be stored.
	 */
	public function sanitize_settings( $settings ) {
		$this->options['post_types'] = array();

		if ( isset( $settings['post_types'] ) && is_array( $settings['post_types'] ) ) {
			// Post types considered valid.
			$supported_post_types = array_diff(
				get_post_types(),
				self::DEFAULT_POST_TYPES
			);

			foreach ( $settings['post_types'] as $post_type ) {
				if ( in_array( $post_type, $supported_post_types, true ) ) {
					// Valid post type. Add to array.
					$this->options['post_types'][] = $post_type;
				}
			}
		}

		if ( isset( $settings['twitter_username'] ) ) {
			$this->options['twitter_username'] = sanitize_text_field( $settings['twitter_username'] );
		}

		if ( isset( $settings['twitter_api_key'] ) ) {
			$this->options['twitter_api_key'] = $settings['twitter_api_key'];
		}

		if ( isset( $settings['twitter_api_secret'] ) ) {
			$this->options['twitter_api_secret'] = $settings['twitter_api_secret'];
		}

		if ( isset( $settings['twitter_access_token'] ) ) {
			$this->options['twitter_access_token'] = $settings['twitter_access_token'];
		}

		if ( isset( $settings['twitter_access_token_secret'] ) ) {
			$this->options['twitter_access_token_secret'] = $settings['twitter_access_token_secret'];
		}

		$this->options['twitter_use_v2_api'] = false;

		if ( isset( $settings['twitter_use_v2_api'] ) ) {
			$this->options['twitter_use_v2_api'] = true;
		}

		// Updated settings.
		return $this->options;
	}

	/**
	 * Echoes the plugin options form. Handles the OAuth flow, too, for now.
	 *
	 * @since 0.1.0
	 */
	public function settings_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Share on Twitter', 'share-on-twitter' ); ?></h1>

			<h2><?php esc_html_e( 'Settings', 'share-on-twitter' ); ?></h2>
			<form method="post" action="options.php">
				<?php
				// Print nonces and such.
				settings_fields( 'share-on-twitter-settings-group' );

				// Post types considered valid.
				$supported_post_types = array_diff(
					get_post_types(),
					self::DEFAULT_POST_TYPES
				);
				?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="share_on_twitter_settings[twitter_username]"><?php esc_html_e( 'Username', 'share-on-twitter' ); ?></label></th>
						<td><input type="text" id="share_on_twitter_settings[twitter_username]" name="share_on_twitter_settings[twitter_username]" style="min-width: 33%;" value="<?php echo esc_attr( $this->options['twitter_username'] ); ?>" />
						<p class="description"><?php esc_html_e( 'Your Twitter handle (without &ldquo;@&rdquo;).', 'share-on-twitter' ); ?></p></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Supported Post Types', 'share-on-twitter' ); ?></th>
						<td><ul style="list-style: none; margin-top: 4px;">
							<?php
							foreach ( $supported_post_types as $post_type ) :
								$post_type = get_post_type_object( $post_type );
								?>
								<li><label><input type="checkbox" name="share_on_twitter_settings[post_types][]" value="<?php echo esc_attr( $post_type->name ); ?>" <?php checked( in_array( $post_type->name, $this->options['post_types'], true ) ); ?>><?php echo esc_html( $post_type->labels->singular_name ); ?></label></li>
								<?php
							endforeach;
							?>
						</ul>
						<p class="description"><?php esc_html_e( 'Post types for which sharing to Twitter is possible. (Sharing can still be disabled on a per-post basis.)', 'share-on-twitter' ); ?></p></td>
					</tr>
					<tr valign="top">
						<td colspan="2" style="padding-left: 0;">
							<?php /* translators: URL of Twitter's developer dashboard */ ?>
							<?php printf( __( 'This plugin requires you sign up for a developer account over at %s, and register a new app with read + write access.', 'share_on_twitter' ), '<a href="https://developer.twitter.com/en/portal/dashboard">https://developer.twitter.com/en/portal/dashboard</a>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><br />
							<?php esc_html_e( 'After doing so, you&rsquo;ll be given a set of secrets, which WordPress needs in order to talk to the Twitter API.', 'share_on_twitter' ); ?>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Twitter API version', 'share-on-twitter' ); ?></th>
						<td><label><input type="checkbox" name="share_on_twitter_settings[twitter_use_v2_api]" value="1" <?php checked( ! empty( $this->options['twitter_use_v2_api'] ) ); ?> /> <?php esc_html_e( 'Use Twitter’s API v2' ); ?></label>
						<p class="description"><?php esc_html_e( 'Default to Twitter’s newer v2 API.', 'share-on-twitter' ); ?></p></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="share_on_twitter_settings[twitter_api_key]"><?php esc_html_e( 'API key', 'share-on-twitter' ); ?></label></th>
						<td><input type="text" id="share_on_twitter_settings[twitter_api_key]" name="share_on_twitter_settings[twitter_api_key]" style="min-width: 33%;" value="<?php echo esc_attr( $this->options['twitter_api_key'] ); ?>" />
						<p class="description"><?php esc_html_e( 'Your API key.', 'share-on-twitter' ); ?></p></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="share_on_twitter_settings[twitter_api_secret]"><?php esc_html_e( 'API secret', 'share-on-twitter' ); ?></label></th>
						<td><input type="text" id="share_on_twitter_settings[twitter_api_secret]" name="share_on_twitter_settings[twitter_api_secret]" style="min-width: 33%;" value="<?php echo esc_attr( $this->options['twitter_api_secret'] ); ?>" />
						<p class="description"><?php esc_html_e( 'Your API secret.', 'share-on-twitter' ); ?></p></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="share_on_twitter_settings[twitter_access_token]"><?php esc_html_e( 'Access Token', 'share-on-twitter' ); ?></label></th>
						<td><input type="text" id="share_on_twitter_settings[twitter_access_token]" name="share_on_twitter_settings[twitter_access_token]" style="min-width: 33%;" value="<?php echo esc_attr( $this->options['twitter_access_token'] ); ?>" />
						<p class="description"><?php esc_html_e( 'Your access token.', 'share-on-twitter' ); ?></p></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="share_on_twitter_settings[twitter_access_token_secret]"><?php esc_html_e( 'Access Token Secret', 'share-on-twitter' ); ?></label></th>
						<td><input type="text" id="share_on_twitter_settings[twitter_access_token_secret]" name="share_on_twitter_settings[twitter_access_token_secret]" style="min-width: 33%;" value="<?php echo esc_attr( $this->options['twitter_access_token_secret'] ); ?>" />
						<p class="description"><?php esc_html_e( 'Your access token secret.', 'share-on-twitter' ); ?></p></td>
					</tr>
				</table>
				<p class="submit"><?php submit_button( __( 'Save Changes' ), 'primary', 'submit', false ); ?></p>
			</form>
		</div>
		<?php
	}

	/**
	 * Returns the plugin options.
	 *
	 * @since 0.1.0
	 *
	 * @return array Plugin options.
	 */
	public function get_options() {
		return $this->options;
	}
}
