<?php
/**
 * Plugin Name: Story Chief WPML
 * Plugin URI: https://storychief.io/wordpress-wpml
 * Description: This plugin lets Storychief and WPML work together.
 * Version: 0.1.0
 * Author: Gregory Claeyssens
 * Author URI: http://storychief.io
 * License: GPL2
 */

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'STORYCHIEF_WPML_VERSION', '0.1.0' );
define( 'STORYCHIEF_WPML__MINIMUM_WP_VERSION', '4.6' );
define( 'STORYCHIEF_WPML__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'STORYCHIEF_WPML__PLUGIN_BASE_NAME', plugin_basename(__FILE__) );


register_activation_hook( __FILE__, array( 'Storychief_WPML', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'Storychief_WPML', 'plugin_deactivation' ) );

require_once( STORYCHIEF_WPML__PLUGIN_DIR . 'class.storychief-wpml.php' );

add_action( 'init', array( 'Storychief_WPML', 'init' ) );

if ( is_admin() ) {
	require_once( STORYCHIEF_WPML__PLUGIN_DIR . 'class.storychief-wpml-admin.php' );
}