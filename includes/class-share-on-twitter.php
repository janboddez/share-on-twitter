<?php
/**
 * Main plugin class.
 *
 * @package Share_On_Twitter
 */

namespace Share_On_Twitter;

/**
 * Main plugin class.
 */
class Share_On_Twitter {
	/**
	 * This plugin's single instance.
	 *
	 * @since 0.1.0
	 *
	 * @var Share_On_Twitter $instance Plugin instance.
	 */
	private static $instance;

	/**
	 * `Options_Handler` instance.
	 *
	 * @since 0.1.0
	 *
	 * @var Options_Handler $instance `Options_Handler` instance.
	 */
	private $options_handler;

	/**
	 * `Post_Handler` instance.
	 *
	 * @since 0.1.0
	 *
	 * @var Post_Handler $instance `Post_Handler` instance.
	 */
	private $post_handler;

	/**
	 * Returns the single instance of this class.
	 *
	 * @since 0.1.0
	 *
	 * @return Share_On_Twitter Single class instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 */
	private function __construct() {
		$this->options_handler = new Options_Handler();
		$this->options_handler->register();

		$this->post_handler = new Post_Handler( $this->options_handler );
		$this->post_handler->register();
	}

	/**
	 * Interacts with WordPress's Plugin API.
	 *
	 * @since 0.1.0
	 */
	public function register() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
	}

	/**
	 * Enables localization.
	 *
	 * @since 0.1.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'share-on-twitter', false, basename( dirname( dirname( __FILE__ ) ) ) . '/languages' );
	}

	/**
	 * Returns `Options_Handler` instance.
	 *
	 * @since 0.1.0
	 *
	 * @return Options_Handler This plugin's `Options_Handler` instance.
	 */
	public function get_options_handler() {
		return $this->options_handler;
	}

	/**
	 * Returns `Post_Handler` instance.
	 *
	 * @since 0.1.0
	 *
	 * @return Post_Handler This plugin's `Post_Handler` instance.
	 */
	public function get_post_handler() {
		return $this->post_handler;
	}
}
