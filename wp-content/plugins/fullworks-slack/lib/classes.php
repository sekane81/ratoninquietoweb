<?php
/*
main classes for processing

*/

if(!class_exists('FullworksSlack'))
{
	class FullworksSlack
	{

		protected $version;

		/**
		 * Construct the plugin object
		 */
		public function __construct()
		{
			if( is_admin() )
    			$fullworksslack_settings_page = new FullworksSlackSettingsPage();
		} // END public function __construct

		/**
		 * Activate the plugin
		 */
		public  static function activate()
		{

		} // END public static function activate

		/**
		 * Deactivate the plugin
		 */
		public static function deactivate()
		{

		} // END public static function deactivate

    public static function uninstall()
		{


		} // END public static function uninstall
		public static function JoinSlack_shortcode( $instance ) {
			$instance = shortcode_atts( array(
									'title' => __('Join Slack','join-slack'),
									'text'             => '',
									'after_text'       => '',
						      'button_text'      => __('Go','join-slack'),
						      'image'            => plugin_dir_url(__FILE__) . 'images/slack_rgb-300x88.png'


								), $atts, 'join-slack' );
				$widg = array(
									'before_widget'	 	=> '<div class="joinslack-widget">',
									'after_widget' 		=> '</div>',
									'before_title' 		=> '<h2>',
									'after_title' 		=> '</h2>',
				);

				$output.=JoinSlack_Widget::render($widg,$instance);
				return $output;
			}

	}
}

 ?>
