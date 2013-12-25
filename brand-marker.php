<?php
/*
Plugin Name: Brand Marker
Plugin URI: http://
Description: Automatically add TM or (R) to specified text in posts.
Version: 0.1
Author: Patrick Mauro
Author URI: http://patrick.mauro.ca
License: GPLv2
*/

/*	Copyright 2013 Patrick Mauro (email : patrick@mauro.ca)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should receive a copy of the GNU General Public License
	along with this program; if not, write to the Free Software 
	Foundation, Inc., 51 Franklin St., Fifth Floor, Boston, MA 02110-1301 USA
*/
	/* WordPress Hooks */
	define("WP_PLUGIN_INIT",			'init');
	define("WP_PLUGIN_ADMIN_MENU",		'admin_menu');
	define("WP_PLUGIN_ADMIN_INIT",		'admin_init');
	define("WP_PLUGIN_PUBLISH_POST",	'publish_post');
	// need 'publish_page' as well for pages?
	// alternate use 'the_post' hook, "gets object after query"
	define("WP_USER_MANAGE_OPTS",		'manage_options');

	/* Function Names */
	define("FNC_INSTALL",		'brand_marker_install');
	define("FNC_INIT",			'brand_marker_init');
	define("FNC_ADMIN_MENU",	'brand_marker_menu');
	define("FNC_REG_SETTINGS",	'brand_marker_register_settings');
	define("FNC_UPDATE_POST",	'brand_update_post');
	define("FNC_SANITIZE_OPTS",	'brand_sanitize_options');
	define("FNC_SETTINGS_PAGE",	'brand_settings_page');

	/* Assign the hooks */
	register_activation_hook(__FILE__, FNC_INSTALL);
	add_action(WP_PLUGIN_INIT, FNC_INIT);
	add_action(WP_PLUGIN_ADMIN_MENU, FNC_ADMIN_MENU);
	add_action(WP_PLUGIN_ADMIN_INIT, FNC_REG_SETTINGS);
	add_action(WP_PLUGIN_PUBLISH_POST, FNC_UPDATE_POST);

	/* Plugin Variables and Attributes */
	define("PLUGIN_TAG",				'brand_marker');
	define("BRD_MARKS",					'brand_marks');
	define("BRD_SETTINGS",				'brand-settings-group');
	define("BRD_SETTINGS_PAGE_NAME", 	'Brand Marker Settings');
	define("BRD_SETTINGS_NAME",			'Brand Settings');
	define("BRD_SETTINGS_PAGE_URL",		'brand-settings');

	/* Brand marks */
	define("TRADE_MARK",	'&#8482;');
	define("REG_MARK",		'&reg;');
	define("REG_MARK_2",	'®');
	define("REG_MARK_3",	'&#174;');
	define("REG_MARK_4",	'&reg'); // must occur after &reg; when being removed
	define("REG_MARK_5",	'&#174'); // must occur after &#174; when being removed
	define("TRADE_MARK_2",	'™');
	define("TRADE_MARK_3",	'&trade;');
	define("TRADE_MARK_4",	'&trade'); // must occur after &trade; when being removed
	define("TRADE_MARK_5",	'&#8482'); // must occur after &#8482; when being removed

	/*
	TODO: 
		move brand marks into a loop
		sanitize all inputs and outputs
		support multiple brands
		support for nested brands ('FOO(R)' and 'FOO BAR(R)' are brands, but don't want 'FOO(R) BAR(R)')
		support dynamic number of brands
		check authorization levels
		make work for multi-site
		create uninstall script
		perform brand marking on view instead of publish
		improve comments
		improve settings page
	*/

	function brand_marker_install() {
		//check version compatibility

		//setup default option values
		$brand_marks_arr = array('brand_1' => 'BrandMarker', 'mark_1' => TRADE_MARK);

		update_option (BRD_MARKS, $brand_marks_arr);
	}

	// Initialize the Brand Marker
	function brand_marker_init() {
		// nothing to initialize for this plugin
	}

	// create the Brand Marker sub-menu
	function brand_marker_menu() {
		add_options_page( __(BRD_SETTINGS_PAGE_NAME, PLUGIN_TAG), 
			__(BRD_SETTINGS_NAME, PLUGIN_TAG), 
			WP_USER_MANAGE_OPTS, BRD_SETTINGS_PAGE_URL, FNC_SETTINGS_PAGE);
	}

	// build the plugin settings page
	function brand_settings_page() {
		if ( !current_user_can(WP_USER_MANAGE_OPTS) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}	
		// load options
		$brand_marks_arr = get_option(BRD_MARKS);
		// set options to variables
		$firstbrand = $brand_marks_arr['brand_1'];
		$firstmark = $brand_marks_arr['mark_1'];

		// create form
		echo '<H1>Brand Marker</H1>';
		echo '<H3>List the brandnames that you want &reg; or &#8482; to appear after.';
		echo '<div class="wrap">';
		echo '	<form method="post" action="options.php">';
		echo settings_fields( BRD_SETTINGS);
		echo '		<input type="text" name="'.BRD_MARKS.'[brand_1]" value="'.esc_attr( $firstbrand ).'" size="24">';
		echo '		<select name="'.BRD_MARKS.'[mark_1]">';
		echo '			<option value="REG_MARK" '.selected($firstmark, "REG_MARK").'>'.REG_MARK.'</option>';
		echo '			<option value="TRADE_MARK" '.selected($firstmark, "TRADE_MARK").'>'.TRADE_MARK.'</option>';
		echo '		</select>';
		echo '		<input type="submit" class="button-primary" value="';
		_e( 'Save Changes', PLUGIN_TAG) ;
		echo '" />';
		echo '	</form>';
		echo '</div>';
	}


	function brand_marker_register_settings() {
		// register settings
		register_setting(BRD_SETTINGS, BRD_MARKS, FNC_SANITIZE_OPTS);
	}

	function brand_sanitize_options( $options ) {
		// TODO: sanitize all the inputs
		return $options;
	}

	function brand_removebranding ($content, $brand, $symbol)
	{
		return preg_replace('/\b('.$brand.')'.addslashes($symbol).'/', '${1}', $content);
	}

	function brand_addbrand ($content, $brand, $symbol) {
		return preg_replace('/\b('.$brand.')\b/', '${1}'.addslashes($symbol), $content);
	}

	function brand_setbranding ($content, $brand, $symbol) {
		$temp_storage = brand_removebranding($content, $brand, TRADE_MARK);
		$temp_storage = brand_removebranding($temp_storage, $brand, TRADE_MARK_2);
		$temp_storage = brand_removebranding($temp_storage, $brand, TRADE_MARK_3);
		$temp_storage = brand_removebranding($temp_storage, $brand, TRADE_MARK_4);
		$temp_storage = brand_removebranding($temp_storage, $brand, TRADE_MARK_5);
		$temp_storage = brand_removebranding($temp_storage, $brand, REG_MARK);
		$temp_storage = brand_removebranding($temp_storage, $brand, REG_MARK_2);
		$temp_storage = brand_removebranding($temp_storage, $brand, REG_MARK_3);
		$temp_storage = brand_removebranding($temp_storage, $brand, REG_MARK_4);
		$temp_storage = brand_removebranding($temp_storage, $brand, REG_MARK_5);
		return brand_addbrand($temp_storage, $brand, $symbol);
	}

	function brand_update_post( $post_id ) {
		// load options
		$brand_marks_arr = get_option(BRD_MARKS);
		// set options to variables
		$firstbrand = $brand_marks_arr['brand_1'];
		$firstmark = $brand_marks_arr['mark_1'];

		// get the post
		$post = get_post($post_id, ARRAY_A);
		// perform regex on title
		$post['post_title'] = brand_setbranding($post['post_title'], $firstbrand, constant($firstmark));
		// perform regex on the content
		$post['post_excerpt'] = brand_setbranding($post['post_excerpt'], $firstbrand, constant($firstmark));
		$post['post_content'] = brand_setbranding($post['post_content'], $firstbrand, constant($firstmark));

		// remove and re-add the hook to prevent infinite loops
		remove_action(WP_PLUGIN_PUBLISH_POST, FNC_UPDATE_POST);
		wp_update_post($post);
		add_action(WP_PLUGIN_PUBLISH_POST, FNC_UPDATE_POST);

		return $post_id;
	}
?>