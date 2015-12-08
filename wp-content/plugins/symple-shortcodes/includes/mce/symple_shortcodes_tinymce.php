<?php
/**
 * This file has all the main shortcode functions
 * @package Symple Shortcodes Plugin
 * @since 1.0
 * @author AJ Clarke : http://wpexplorer.com
 * @copyright Copyright (c) 2012, AJ Clarke
 * @link http://wpexplorer.com
 * @License: GNU General Public License version 3.0
 * @License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 *
 * Special thank you to my buddy Syamil @ http://aquagraphite.com/
 */

global $wp_version;
if (version_compare($wp_version, '3.9', '>=')) {
	add_action('admin_head', 'TT_add_my_tc_button');
	function TT_add_my_tc_button() {
		global $typenow;
		// check user permissions
		if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) {
			return;
		}
		// verify the post type
		if( ! in_array( $typenow, array( 'post', 'page' ) ) )
		return;
		// check if WYSIWYG is enabled
		if ( get_user_option('rich_editing') == 'true') {
			add_filter("mce_external_plugins", "TT_add_tinymce_plugin");
			add_filter('mce_buttons', 'TT_register_my_tc_button');
		}
	}
	function TT_add_tinymce_plugin($plugin_array) {
		$plugin_array['TT_tc_button'] = plugins_url( '/js/symple_shortcodes_tinymce_TT.js', __FILE__ ); // CHANGE THE BUTTON SCRIPT HERE
		return $plugin_array;
	}
	function TT_register_my_tc_button($buttons) {
		array_push($buttons, "TT_tc_button");
		return $buttons;
	}
}else{
	class SYMPLE_TinyMCE_Buttons {
		function __construct() {
	    	add_action( 'init', array(&$this,'init') );
	    }
	    function init() {
			if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
				return;		
			if ( get_user_option('rich_editing') == 'true' ) {  
				add_filter( 'mce_external_plugins', array(&$this, 'add_plugin') );  
				add_filter( 'mce_buttons', array(&$this,'register_button') ); 
			}  
	    }  
		function add_plugin($plugin_array) {  
		   $plugin_array['symple_shortcodes'] = plugin_dir_url( __FILE__ ) .'js/symple_shortcodes_tinymce.js';
		   return $plugin_array; 
		}
		function register_button($buttons) {  
		   array_push($buttons, "symple_shortcodes_button");
		   return $buttons; 
		} 	
	}
	$sympleshortcode = new SYMPLE_TinyMCE_Buttons;
}