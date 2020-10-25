<?php

class Test_Share_On_Twitter extends \WP_Mock\Tools\TestCase {
	public function setUp() : void {
		\WP_Mock::setUp();
	}

	public function tearDown() : void {
		\WP_Mock::tearDown();
	}

	public function test_share_on_twitter_register() {
		$options = array(
			'twitter_api_key'             => '',
			'twitter_api_secret'          => '',
			'twitter_access_token'        => '',
			'twitter_access_token_secret' => '',
			'twitter_username'            => '',
			'post_types'                  => array(),
		);

		\WP_Mock::userFunction( 'get_option', array(
			'times'  => 1,
			'args'   => array(
				'share_on_twitter_settings',
				$options,
			),
			'return' => $options,
		) );

		$plugin = \Share_On_Twitter\Share_On_Twitter::get_instance();

		\WP_Mock::expectActionAdded( 'plugins_loaded', array( $plugin, 'load_textdomain' ) );

		$plugin->register();

		$this->assertHooksAdded();
	}
}
