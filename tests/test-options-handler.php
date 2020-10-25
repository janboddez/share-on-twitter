<?php

class Test_Options_Handler extends \WP_Mock\Tools\TestCase {
	public function setUp() : void {
		\WP_Mock::setUp();
	}

	public function tearDown() : void {
		\WP_Mock::tearDown();
	}

	public function test_options_handler_register() {
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

		$options_handler = new \Share_On_Twitter\Options_Handler();

		\WP_Mock::expectActionAdded( 'admin_menu', array( $options_handler, 'create_menu' ) );

		$options_handler->register();

		$this->assertHooksAdded();
	}

	public function test_options_handler_add_settings() {
		$options_handler = new \Share_On_Twitter\Options_Handler();

		\WP_Mock::userFunction( 'add_options_page', array(
			'times' => 1,
			'args'  => array(
				'Share on Twitter',
				'Share on Twitter',
				'manage_options',
				'share-on-twitter',
				array( $options_handler, 'settings_page' )
			),
		) );

		\WP_Mock::expectActionAdded( 'admin_init', array( $options_handler, 'add_settings' ) );

		$options_handler->create_menu();

		$this->assertHooksAdded();
	}
}
