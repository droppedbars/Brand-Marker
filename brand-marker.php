<?php
/*
Plugin Name: Brand Marker
Plugin URI: http://
Description: Automatically add TM or (R) to specified text in posts.
Version: 0.1.1
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

	/* Associate WordPress hooks with functions */
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
	$REG_MARK = array(
		'&reg;',
		'&reg',
		'®',
		'&#174;',
		'&#174'
	);
	$TRADE_MARK = array(
		'&trade;',
		'&trade',
		'™',
		'&#8482;',
		'&#8482'
	); 
	define("REG_MARK",   '&reg;');
	define("TRADE_MARK", '&trade;');
    define("BLANK",      '');

	/*
	TODO: 
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

	/* 
		Called via the install hook.
		Ensure this plugin is compatible with the WordPress version.
		Set the defaul option values and store them into the database.
	*/
	function brand_marker_install() {
		//check version compatibility
		// TODO
		// setup default option values
		$brand_marks_arr = array('brand_1' => 'BrandMarker', 'mark_1' => 'TRADE_MARK',
                                'brand_2' => '', 'mark_2' => '',
                                'brand_3' => '', 'mark_3' => '',
                                'brand_4' => '', 'mark_4' => '',
                                'brand_5' => '', 'mark_5' => '');
		// update the database with the default option values
		update_option (BRD_MARKS, $brand_marks_arr);
	}

	/*
		Called via the init hook.
	*/
	function brand_marker_init() {
		// nothing to initialize for this plugin
	}

	/*
		Called via the admin menu hook.
		Define and create the submenu item for the plugin under options menu
	*/
	function brand_marker_menu() {
		add_options_page( __(BRD_SETTINGS_PAGE_NAME, PLUGIN_TAG), 
			__(BRD_SETTINGS_NAME, PLUGIN_TAG), 
			WP_USER_MANAGE_OPTS, BRD_SETTINGS_PAGE_URL, FNC_SETTINGS_PAGE);
	}

	/*
		Called via the appropriate sub-menu hook.
		Create the settings page for the plugin
	*/
	function brand_settings_page() {
		if ( !current_user_can(WP_USER_MANAGE_OPTS) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}	
		// load options
		$brand_marks_arr = get_option(BRD_MARKS);
		// set options to variables
		$brand_1 = $brand_marks_arr['brand_1'];
		$mark_1 = $brand_marks_arr['mark_1'];
        $brand_2 = $brand_marks_arr['brand_2'];
        $mark_2 = $brand_marks_arr['mark_2'];
        $brand_3 = $brand_marks_arr['brand_3'];
        $mark_3 = $brand_marks_arr['mark_3'];
        $brand_4 = $brand_marks_arr['brand_4'];
        $mark_4 = $brand_marks_arr['mark_4'];
        $brand_5 = $brand_marks_arr['brand_5'];
        $mark_5 = $brand_marks_arr['mark_5'];

		// create form
		echo '<H1>Brand Marker</H1>';
		echo '<H3>List the brandnames that you want '.REG_MARK.' or '.TRADE_MARK.' to appear after.';
		echo '<div class="wrap">';
		echo '	<form method="post" action="options.php">';
		settings_fields(BRD_SETTINGS);
		echo '		<input type="text" name="'.BRD_MARKS.'[brand_1]" value="'.esc_attr( $brand_1 ).'" size="24">';
		echo '		<select name="'.BRD_MARKS.'[mark_1]">';
        echo '			<option value="BLANK" '.selected($mark_1, "BLANK").'>'.BLANK.'</option>';
		echo '			<option value="REG_MARK" '.selected($mark_1, "REG_MARK").'>'.REG_MARK.'</option>';
		echo '			<option value="TRADE_MARK" '.selected($mark_1, "TRADE_MARK").'>'.TRADE_MARK.'</option>';
		echo '		</select><br>';

        echo '		<input type="text" name="'.BRD_MARKS.'[brand_2]" value="'.esc_attr( $brand_2 ).'" size="24">';
        echo '		<select name="'.BRD_MARKS.'[mark_2]">';
        echo '			<option value="BLANK" '.selected($mark_2, "BLANK").'>'.BLANK.'</option>';
        echo '			<option value="REG_MARK" '.selected($mark_2, "REG_MARK").'>'.REG_MARK.'</option>';
        echo '			<option value="TRADE_MARK" '.selected($mark_2, "TRADE_MARK").'>'.TRADE_MARK.'</option>';
        echo '		</select><br>';

        echo '		<input type="text" name="'.BRD_MARKS.'[brand_3]" value="'.esc_attr( $brand_3 ).'" size="24">';
        echo '		<select name="'.BRD_MARKS.'[mark_3]">';
        echo '			<option value="BLANK" '.selected($mark_3, "BLANK").'>'.BLANK.'</option>';
        echo '			<option value="REG_MARK" '.selected($mark_3, "REG_MARK").'>'.REG_MARK.'</option>';
        echo '			<option value="TRADE_MARK" '.selected($mark_3, "TRADE_MARK").'>'.TRADE_MARK.'</option>';
        echo '		</select><br>';

        echo '		<input type="text" name="'.BRD_MARKS.'[brand_4]" value="'.esc_attr( $brand_4 ).'" size="24">';
        echo '		<select name="'.BRD_MARKS.'[mark_4]">';
        echo '			<option value="BLANK" '.selected($mark_4, "BLANK").'>'.BLANK.'</option>';
        echo '			<option value="REG_MARK" '.selected($mark_4, "REG_MARK").'>'.REG_MARK.'</option>';
        echo '			<option value="TRADE_MARK" '.selected($mark_4, "TRADE_MARK").'>'.TRADE_MARK.'</option>';
        echo '		</select><br>';

        echo '		<input type="text" name="'.BRD_MARKS.'[brand_5]" value="'.esc_attr( $brand_5 ).'" size="24">';
        echo '		<select name="'.BRD_MARKS.'[mark_5]">';
        echo '			<option value="BLANK" '.selected($mark_5, "BLANK").'>'.BLANK.'</option>';
        echo '			<option value="REG_MARK" '.selected($mark_5, "REG_MARK").'>'.REG_MARK.'</option>';
        echo '			<option value="TRADE_MARK" '.selected($mark_5, "TRADE_MARK").'>'.TRADE_MARK.'</option>';
        echo '		</select></br>';


        echo '		<input type="submit" class="button-primary" value="';
		_e( 'Save Changes', PLUGIN_TAG) ;
		echo '" />';
		echo '	</form>';
		echo '</div>';
	}

	/*
		Store the settings after the user has submitted the settings form.
	*/
	function brand_marker_register_settings() {
		// register settings
		register_setting(BRD_SETTINGS, BRD_MARKS, FNC_SANITIZE_OPTS);
	}

	function brand_sanitize_options( $options ) {
		// TODO: sanitize all the inputs
		return $options;
	}

	/*
		Search $content string for all occurances of $brand and remove $symbol if it trails it.
	*/
	function brand_removebranding ($content, $brand, $symbol)
	{
		return preg_replace('/\b('.$brand.')'.addslashes($symbol).'/', '${1}', $content);
	}

	/*
		Search $content for all occurances of $brand and ad $symbol after it.
	*/
	function brand_addbrand ($content, $brand, $symbol) {
		return preg_replace('/\b('.$brand.')\b/', '${1}'.addslashes($symbol), $content);
	}

	/*
		Parse $content, ensuring occurances of $brand have the appropriate trademark symbol afterwards
	*/
	function brand_setbranding ($content, $brand, $symbol) {
		global $REG_MARK;
		global $TRADE_MARK;

		$temp_storage = $content;

		foreach($REG_MARK as $value) 
			$temp_storage = brand_removebranding($temp_storage, $brand, $value);
		foreach($TRADE_MARK as $value)
			$temp_storage = brand_removebranding($temp_storage, $brand, $value);
		return brand_addbrand($temp_storage, $brand, $symbol);
	}

	/*
		Called via update_post hook.
		Ensures the branding matches the plugin options for the updated post.
	*/
	function brand_update_post( $post_id ) {
		// load options
		$brand_marks_arr = get_option(BRD_MARKS);

		// set options to variables
		$brand_1 = $brand_marks_arr['brand_1'];
		$mark_1 = $brand_marks_arr['mark_1'];
        $brand_2 = $brand_marks_arr['brand_2'];
        $mark_2 = $brand_marks_arr['mark_2'];
        $brand_3 = $brand_marks_arr['brand_3'];
        $mark_3 = $brand_marks_arr['mark_3'];
        $brand_4 = $brand_marks_arr['brand_4'];
        $mark_4 = $brand_marks_arr['mark_4'];
        $brand_5 = $brand_marks_arr['brand_5'];
        $mark_5 = $brand_marks_arr['mark_5'];

		// get the post
		$post = get_post($post_id, ARRAY_A);
		// perform regex on title
		$post['post_title']   = brand_setbranding($post['post_title'], $brand_1, constant($mark_1));
		$post['post_excerpt'] = brand_setbranding($post['post_excerpt'], $brand_1, constant($mark_1));
		$post['post_content'] = brand_setbranding($post['post_content'], $brand_1, constant($mark_1));

        $post['post_title']   = brand_setbranding($post['post_title'], $brand_2, constant($mark_2));
        $post['post_excerpt'] = brand_setbranding($post['post_excerpt'], $brand_2, constant($mark_2));
        $post['post_content'] = brand_setbranding($post['post_content'], $brand_2, constant($mark_2));

        $post['post_title']   = brand_setbranding($post['post_title'], $brand_3, constant($mark_3));
        $post['post_excerpt'] = brand_setbranding($post['post_excerpt'], $brand_3, constant($mark_3));
        $post['post_content'] = brand_setbranding($post['post_content'], $brand_3, constant($mark_3));

        $post['post_title']   = brand_setbranding($post['post_title'], $brand_4, constant($mark_4));
        $post['post_excerpt'] = brand_setbranding($post['post_excerpt'], $brand_4, constant($mark_4));
        $post['post_content'] = brand_setbranding($post['post_content'], $brand_4, constant($mark_4));

        $post['post_title']   = brand_setbranding($post['post_title'], $brand_5, constant($mark_5));
        $post['post_excerpt'] = brand_setbranding($post['post_excerpt'], $brand_5, constant($mark_5));
        $post['post_content'] = brand_setbranding($post['post_content'], $brand_5, constant($mark_5));

        // remove and re-add the hook to prevent infinite loops
		remove_action(WP_PLUGIN_PUBLISH_POST, FNC_UPDATE_POST);
		wp_update_post($post);
		add_action(WP_PLUGIN_PUBLISH_POST, FNC_UPDATE_POST);

		return $post_id;
	}