<?php
/**
 * Plugin Name:       Share on Twitter
 * Description:       Easily share WordPress posts on Twitter.
 * Plugin URI:        https://jan.boddez.net/wordpress/share-on-twitter
 * GitHub Plugin URI: https://github.com/janboddez/share-on-twitter
 * Author:            Jan Boddez
 * Author URI:        https://jan.boddez.net/
 * License: GNU       General Public License v3
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       share-on-twitter
 * Version:           0.2.0
 *
 * @author  Jan Boddez <jan@janboddez.be>
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 * @package Share_On_Twitter
 */

namespace Share_On_Twitter;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Composer's autoloader.
require dirname( __FILE__ ) . '/build/vendor/autoload.php';

require dirname( __FILE__ ) . '/includes/class-options-handler.php';
require dirname( __FILE__ ) . '/includes/class-post-handler.php';
require dirname( __FILE__ ) . '/includes/class-share-on-twitter.php';

Share_On_Twitter::get_instance()->register();
