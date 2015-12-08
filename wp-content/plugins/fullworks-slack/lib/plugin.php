<?php
/*
plugin controls, including install and removal

*/
// Plugin translations
function fullworks_slack_load_textdomain() {
  load_plugin_textdomain( 'fullworks-slack', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'fullworks_slack_load_textdomain' );

function fullworks_slack_scripts() {
	wp_enqueue_style( 'fullworks-slack-styles', plugin_dir_url(__FILE__) . 'css/style.css' );
}

add_action( 'wp_enqueue_scripts', 'fullworks_slack_scripts' );


// Plugin instantiation Code
if(class_exists('FullworksSlack'))
{
	// Installation and uninstallation hooks
	register_activation_hook(__FILE__, array('FullworksSlack', 'activate'));
	register_deactivation_hook(__FILE__, array('FullworksSlack', 'deactivate'));
  register_uninstall_hook(__FILE__, array('FullworksSlack', 'uninstall'));
  // shortcodes
  add_shortcode( 'join-slack', array( 'FullworksSlack', 'JoinSlack_shortcode' ) );

	// instantiate the plugin class
	$rightmove_feed = new FullworksSlack();

}




 ?>
