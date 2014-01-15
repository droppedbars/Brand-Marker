<?php
/*
Plugin Name: Brand Marker
Plugin URI: http://github.com/droppedbars/Brand-Marker
Description: Automatically add TM or (R) to brands contained in post content, excerpts or titles..
Version: 0.2
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
define( "WP_PLUGIN_INIT", 'init' );
define( "WP_PLUGIN_ADMIN_MENU", 'admin_menu' );
define( "WP_PLUGIN_ADMIN_INIT", 'admin_init' );
define( "WP_PLUGIN_PUBLISH_POST", 'publish_post' );
define( "WP_THE_POST", 'the_post' );
define( "WP_USER_MANAGE_OPTS", 'manage_options' );
define( "WP_THE_CONTENT", 'the_content' );
define( "WP_THE_EXCERPT", 'the_excerpt' );
define( "WP_THE_TITLE", 'the_title' );

/* Function Names */
define( "FNC_INSTALL", 'brand_marker_install' );
define( "FNC_INIT", 'brand_marker_init' );
define( "FNC_ADMIN_MENU", 'brand_marker_menu' );
define( "FNC_REG_SETTINGS", 'brand_marker_register_settings' );
define( "FNC_SANITIZE_OPTS", 'brand_sanitize_options' );
define( "FNC_SETTINGS_PAGE", 'brand_settings_page' );

/* Associate WordPress hooks with functions */
register_activation_hook( __FILE__, FNC_INSTALL );
add_action( WP_PLUGIN_INIT, FNC_INIT );
add_action( WP_PLUGIN_ADMIN_MENU, FNC_ADMIN_MENU );
add_action( WP_PLUGIN_ADMIN_INIT, FNC_REG_SETTINGS );
add_filter( WP_THE_CONTENT, 'brand_update_content' );
add_filter( WP_THE_EXCERPT, 'brand_update_excerpt' );
add_filter( WP_THE_TITLE, 'brand_update_title' );

/* Plugin Variables and Attributes */
define( "PLUGIN_TAG", 'brand_marker' );
define( "BRD_MARKS", 'brand_marks' );
define( "BRD_SETTINGS", 'brand-settings-group' );
define( "BRD_SETTINGS_PAGE_NAME", 'Brand Marker Settings' );
define( "BRD_SETTINGS_NAME", 'Brand Settings' );
define( "BRD_SETTINGS_PAGE_URL", 'brand-settings' );

/* Brand marks */
$REG_MARK   = array(
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
define( 'REG_MARK', '&reg;' );
define( 'TRADE_MARK', '&trade;' );
define( 'BLANK', '' );

/*
	Called via the install hook.
	Ensure this plugin is compatible with the WordPress version.
	Set the default option values and store them into the database.
*/
function brand_marker_install() {
	// check the install version
	global $wp_version;
	if ( version_compare( $wp_version, '3.8', '<' ) ) {
		wp_die( 'This plugin requires WordPress version 3.8 or higher.' );
	}

	$brand_marks_arr = array( 'brand_1' => 'BrandMarker', 'mark_1' => 'TRADE_MARK', 'case_1' => true,
														'brand_2' => '', 'mark_2' => 'BLANK', 'case_2' => false,
														'brand_3' => '', 'mark_3' => 'BLANK', 'case_3' => false,
														'brand_4' => '', 'mark_4' => 'BLANK', 'case_4' => false,
														'brand_5' => '', 'mark_5' => 'BLANK', 'case_5' => false );
	// update the database with the default option values
	update_option( BRD_MARKS, $brand_marks_arr );
}

/*
	Called via the init hook.
*/
function brand_marker_init() {
	// nothing to initialize for this plugin
}

/*
	Called via the admin menu hook.
	Define and create the sub-menu item for the plugin under options menu
*/
function brand_marker_menu() {
	add_options_page( __( BRD_SETTINGS_PAGE_NAME, PLUGIN_TAG ),
			__( BRD_SETTINGS_NAME, PLUGIN_TAG ),
			WP_USER_MANAGE_OPTS, BRD_SETTINGS_PAGE_URL, FNC_SETTINGS_PAGE );
}

/*
	Called via the appropriate sub-menu hook.
	Create the settings page for the plugin
		Escapes the branding since it goes into a text field.
		No escaping is done on the trademark since it is only compared and not printed.
*/
function brand_settings_page() {
	if ( ! current_user_can( WP_USER_MANAGE_OPTS ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	// load options
	$brand_marks_arr = get_option( BRD_MARKS );
	// set options to variables
	$brand_1 = esc_attr( $brand_marks_arr['brand_1'] );
	$mark_1  = $brand_marks_arr['mark_1'];
	$case_1  = $brand_marks_arr['case_1'];
	$brand_2 = esc_attr( $brand_marks_arr['brand_2'] );
	$mark_2  = $brand_marks_arr['mark_2'];
	$case_2  = $brand_marks_arr['case_2'];
	$brand_3 = esc_attr( $brand_marks_arr['brand_3'] );
	$mark_3  = $brand_marks_arr['mark_3'];
	$case_3  = $brand_marks_arr['case_3'];
	$brand_4 = esc_attr( $brand_marks_arr['brand_4'] );
	$mark_4  = $brand_marks_arr['mark_4'];
	$case_4  = $brand_marks_arr['case_4'];
	$brand_5 = esc_attr( $brand_marks_arr['brand_5'] );
	$mark_5  = $brand_marks_arr['mark_5'];
	$case_5  = $brand_marks_arr['case_5'];

	// create form
	echo '<H1>Brand Marker</H1>';
	echo '<H3>List the brand names that you want ' . REG_MARK . ' or ' . TRADE_MARK . ' to appear after.';
	echo '<div class="wrap">';
	echo '	<form method="post" action="options.php">';
	settings_fields( BRD_SETTINGS );
	echo '		<input type="text" name="' . BRD_MARKS . '[brand_1]" value="' . $brand_1 . '" size="24">';
	echo '		<select name="' . BRD_MARKS . '[mark_1]">';
	echo '			<option value="BLANK" ' . selected( $mark_1, "BLANK" ) . '>' . BLANK . '</option>';
	echo '			<option value="REG_MARK" ' . selected( $mark_1, "REG_MARK" ) . '>' . REG_MARK . '</option>';
	echo '			<option value="TRADE_MARK" ' . selected( $mark_1, "TRADE_MARK" ) . '>' . TRADE_MARK . '</option>';
	echo '		</select>';
	echo '		<input type="checkbox" name="' . BRD_MARKS . '[case_1]" value="' . $case_1 . '" ' . checked( $case_1, true, false ) . '>Case Sensitive';
	echo '		<br>';

	echo '		<input type="text" name="' . BRD_MARKS . '[brand_2]" value="' . $brand_2 . '" size="24">';
	echo '		<select name="' . BRD_MARKS . '[mark_2]">';
	echo '			<option value="BLANK" ' . selected( $mark_2, "BLANK" ) . '>' . BLANK . '</option>';
	echo '			<option value="REG_MARK" ' . selected( $mark_2, "REG_MARK" ) . '>' . REG_MARK . '</option>';
	echo '			<option value="TRADE_MARK" ' . selected( $mark_2, "TRADE_MARK" ) . '>' . TRADE_MARK . '</option>';
	echo '		</select>';
	echo '		<input type="checkbox" name="' . BRD_MARKS . '[case_2]" value="' . $case_2 . '"' . checked( $case_2, true, false ) . '>Case Sensitive';
	echo '		<br>';

	echo '		<input type="text" name="' . BRD_MARKS . '[brand_3]" value="' . $brand_3 . '" size="24">';
	echo '		<select name="' . BRD_MARKS . '[mark_3]">';
	echo '			<option value="BLANK" ' . selected( $mark_3, "BLANK" ) . '>' . BLANK . '</option>';
	echo '			<option value="REG_MARK" ' . selected( $mark_3, "REG_MARK" ) . '>' . REG_MARK . '</option>';
	echo '			<option value="TRADE_MARK" ' . selected( $mark_3, "TRADE_MARK" ) . '>' . TRADE_MARK . '</option>';
	echo '		</select>';
	echo '		<input type="checkbox" name="' . BRD_MARKS . '[case_3]" value="' . $case_3 . '"' . checked( $case_3, true, false ) . '>Case Sensitive';
	echo '		<br>';

	echo '		<input type="text" name="' . BRD_MARKS . '[brand_4]" value="' . $brand_4 . '" size="24">';
	echo '		<select name="' . BRD_MARKS . '[mark_4]">';
	echo '			<option value="BLANK" ' . selected( $mark_4, "BLANK" ) . '>' . BLANK . '</option>';
	echo '			<option value="REG_MARK" ' . selected( $mark_4, "REG_MARK" ) . '>' . REG_MARK . '</option>';
	echo '			<option value="TRADE_MARK" ' . selected( $mark_4, "TRADE_MARK" ) . '>' . TRADE_MARK . '</option>';
	echo '		</select>';
	echo '		<input type="checkbox" name="' . BRD_MARKS . '[case_4]" value="' . $case_4 . '"' . checked( $case_4, true, false ) . '>Case Sensitive';
	echo '		<br>';

	echo '		<input type="text" name="' . BRD_MARKS . '[brand_5]" value="' . $brand_5 . '" size="24">';
	echo '		<select name="' . BRD_MARKS . '[mark_5]">';
	echo '			<option value="BLANK" ' . selected( $mark_5, "BLANK" ) . '>' . BLANK . '</option>';
	echo '			<option value="REG_MARK" ' . selected( $mark_5, "REG_MARK" ) . '>' . REG_MARK . '</option>';
	echo '			<option value="TRADE_MARK" ' . selected( $mark_5, "TRADE_MARK" ) . '>' . TRADE_MARK . '</option>';
	echo '		</select>';
	echo '		<input type="checkbox" name="' . BRD_MARKS . '[case_5]" value="' . $case_5 . '"' . checked( $case_5, true, false ) . '>Case Sensitive';
	echo '		<br>';


	echo '		<input type="submit" class="button-primary" value="';
	_e( 'Save Changes', PLUGIN_TAG );
	echo '" />';
	echo '	</form>';
	echo '</div>';
}

/*
	Store the settings after the user has submitted the settings form.
*/
function brand_marker_register_settings() {
	// register settings
	register_setting( BRD_SETTINGS, BRD_MARKS, FNC_SANITIZE_OPTS );
}

/*
 * Sanitize the options set in the options page;
 * It copies the expected options into a second hash so as to remove any unexpected values
 * All options at this time are text, so just sanitizes the them as text fields.
 */
function brand_sanitize_options( $options ) {

	$sanitized_options['brand_1'] = ( ! empty( $options['brand_1'] ) ) ? sanitize_text_field( $options['brand_1'] ) : '';
	$sanitized_options['brand_2'] = ( ! empty( $options['brand_2'] ) ) ? sanitize_text_field( $options['brand_2'] ) : '';
	$sanitized_options['brand_3'] = ( ! empty( $options['brand_3'] ) ) ? sanitize_text_field( $options['brand_3'] ) : '';
	$sanitized_options['brand_4'] = ( ! empty( $options['brand_4'] ) ) ? sanitize_text_field( $options['brand_4'] ) : '';
	$sanitized_options['brand_5'] = ( ! empty( $options['brand_5'] ) ) ? sanitize_text_field( $options['brand_5'] ) : '';

	$sanitized_options['mark_1'] = ( ! empty( $options['mark_1'] ) ) ? sanitize_text_field( $options['mark_1'] ) : '';
	$sanitized_options['mark_2'] = ( ! empty( $options['mark_2'] ) ) ? sanitize_text_field( $options['mark_2'] ) : '';
	$sanitized_options['mark_3'] = ( ! empty( $options['mark_3'] ) ) ? sanitize_text_field( $options['mark_3'] ) : '';
	$sanitized_options['mark_4'] = ( ! empty( $options['mark_4'] ) ) ? sanitize_text_field( $options['mark_4'] ) : '';
	$sanitized_options['mark_5'] = ( ! empty( $options['mark_5'] ) ) ? sanitize_text_field( $options['mark_5'] ) : '';

	if ( isset( $options['case_1'] ) ) {
		$sanitized_options['case_1'] = true;
	} else {
		$sanitized_options['case_1'] = false;
	}
	if ( isset( $options['case_2'] ) ) {
		$sanitized_options['case_2'] = true;
	} else {
		$sanitized_options['case_2'] = false;
	}
	if ( isset( $options['case_3'] ) ) {
		$sanitized_options['case_3'] = true;
	} else {
		$sanitized_options['case_3'] = false;
	}
	if ( isset( $options['case_4'] ) ) {
		$sanitized_options['case_4'] = true;
	} else {
		$sanitized_options['case_4'] = false;
	}
	if ( isset( $options['case_5'] ) ) {
		$sanitized_options['case_5'] = true;
	} else {
		$sanitized_options['case_5'] = false;
	}


	return $sanitized_options;
}

/*
	Search $content string for all occurrences of $brand and remove $symbol if it trails it.
*/
function brand_removebranding( $content, $brand, $symbol, $case ) {
	$case ? $reg_trail = 'i' : $reg_trail = '';

	return preg_replace( '/\b(' . $brand . ')' . addslashes( $symbol ) . '/' . $reg_trail, '${1}', $content );
}

/*
	Search $content for all occurrences of $brand and add $symbol after it.
*/
function brand_addbrand( $content, $brand, $symbol, $case ) {
	$case ? $reg_trail = '' : $reg_trail = 'i';

	return preg_replace( '/\b(' . $brand . ')\b/' . $reg_trail, '${1}' . addslashes( $symbol ), $content );
}

/*
	Parse $content, ensuring occurrences of $brand have the appropriate trademark symbol afterwards
*/
function brand_setbranding( $content, $brand, $symbol, $case ) {
	global $REG_MARK;
	global $TRADE_MARK;

	if ( ( isset( $brand ) ) && ( strlen( trim( $brand ) ) > 0 ) ) {
		$temp_storage = $content;

		foreach ( $REG_MARK as $value ) {
			$temp_storage = brand_removebranding( $temp_storage, trim( $brand ), $value, $case );
		}
		foreach ( $TRADE_MARK as $value ) {
			$temp_storage = brand_removebranding( $temp_storage, trim( $brand ), $value, $case );
		}

		return brand_addbrand( $temp_storage, trim( $brand ), $symbol, $case );
	} else {
		return $content;
	}
}

/*
 * Update the content with branding
 * Escapes only the trademarks since they get printed onto the HTML.  The brand itself is not printed, so not escaped.
 */
function brand_update_content( $content ) {
	$brand_marks_arr = get_option( BRD_MARKS );

	// set options to variables
	$brand_1 = $brand_marks_arr['brand_1'];
	$mark_1  = esc_html( $brand_marks_arr['mark_1'] );
	$case_1  = $brand_marks_arr['case_1'];
	$brand_2 = $brand_marks_arr['brand_2'];
	$mark_2  = esc_html( $brand_marks_arr['mark_2'] );
	$case_2  = $brand_marks_arr['case_2'];
	$brand_3 = $brand_marks_arr['brand_3'];
	$mark_3  = esc_html( $brand_marks_arr['mark_3'] );
	$case_3  = $brand_marks_arr['case_3'];
	$brand_4 = $brand_marks_arr['brand_4'];
	$mark_4  = esc_html( $brand_marks_arr['mark_4'] );
	$case_4  = $brand_marks_arr['case_4'];
	$brand_5 = $brand_marks_arr['brand_5'];
	$mark_5  = esc_html( $brand_marks_arr['mark_5'] );
	$case_5  = $brand_marks_arr['case_5'];

	$content = brand_setbranding( $content, $brand_1, constant( $mark_1 ), ( $case_1 ) ? TRUE : FALSE );
	$content = brand_setbranding( $content, $brand_2, constant( $mark_2 ), ( $case_2 ) ? TRUE : FALSE );
	$content = brand_setbranding( $content, $brand_3, constant( $mark_3 ), ( $case_3 ) ? TRUE : FALSE );
	$content = brand_setbranding( $content, $brand_4, constant( $mark_4 ), ( $case_4 ) ? TRUE : FALSE );
	$content = brand_setbranding( $content, $brand_5, constant( $mark_5 ), ( $case_5 ) ? TRUE : FALSE );

	return $content;
}

/*
 * Update the excerpt with branding
 * Escapes only the trademarks since they get printed onto the HTML.  The brand itself is not printed, so not escaped.
 */
function brand_update_excerpt( $excerpt ) {
	$brand_marks_arr = get_option( BRD_MARKS );

	// set options to variables
	$brand_1 = $brand_marks_arr['brand_1'];
	$mark_1  = esc_html( $brand_marks_arr['mark_1'] );
	$case_1  = $brand_marks_arr['case_1'];
	$brand_2 = $brand_marks_arr['brand_2'];
	$mark_2  = esc_html( $brand_marks_arr['mark_2'] );
	$case_2  = $brand_marks_arr['case_2'];
	$brand_3 = $brand_marks_arr['brand_3'];
	$mark_3  = esc_html( $brand_marks_arr['mark_3'] );
	$case_3  = $brand_marks_arr['case_3'];
	$brand_4 = $brand_marks_arr['brand_4'];
	$mark_4  = esc_html( $brand_marks_arr['mark_4'] );
	$case_4  = $brand_marks_arr['case_4'];
	$brand_5 = $brand_marks_arr['brand_5'];
	$mark_5  = esc_html( $brand_marks_arr['mark_5'] );
	$case_5  = $brand_marks_arr['case_5'];

	$excerpt = brand_setbranding( $excerpt, $brand_1, constant( $mark_1 ), ( $case_1 ) ? TRUE : FALSE );
	$excerpt = brand_setbranding( $excerpt, $brand_2, constant( $mark_2 ), ( $case_2 ) ? TRUE : FALSE );
	$excerpt = brand_setbranding( $excerpt, $brand_3, constant( $mark_3 ), ( $case_3 ) ? TRUE : FALSE );
	$excerpt = brand_setbranding( $excerpt, $brand_4, constant( $mark_4 ), ( $case_4 ) ? TRUE : FALSE );
	$excerpt = brand_setbranding( $excerpt, $brand_5, constant( $mark_5 ), ( $case_5 ) ? TRUE : FALSE );

	return $excerpt;
}

/*
 * Update the title with branding
 * Escapes only the trademarks since they get printed onto the HTML.  The brand itself is not printed, so not escaped.
 */
function brand_update_title( $title ) {
	$brand_marks_arr = get_option( BRD_MARKS );

	// set options to variables
	$brand_1 = $brand_marks_arr['brand_1'];
	$mark_1  = esc_html( $brand_marks_arr['mark_1'] );
	$case_1  = $brand_marks_arr['case_1'];
	$brand_2 = $brand_marks_arr['brand_2'];
	$mark_2  = esc_html( $brand_marks_arr['mark_2'] );
	$case_2  = $brand_marks_arr['case_2'];
	$brand_3 = $brand_marks_arr['brand_3'];
	$mark_3  = esc_html( $brand_marks_arr['mark_3'] );
	$case_3  = $brand_marks_arr['case_3'];
	$brand_4 = $brand_marks_arr['brand_4'];
	$mark_4  = esc_html( $brand_marks_arr['mark_4'] );
	$case_4  = $brand_marks_arr['case_4'];
	$brand_5 = $brand_marks_arr['brand_5'];
	$mark_5  = esc_html( $brand_marks_arr['mark_5'] );
	$case_5  = $brand_marks_arr['case_5'];

	$title = brand_setbranding( $title, $brand_1, constant( $mark_1 ), ( $case_1 ) ? TRUE : FALSE );
	$title = brand_setbranding( $title, $brand_2, constant( $mark_2 ), ( $case_2 ) ? TRUE : FALSE );
	$title = brand_setbranding( $title, $brand_3, constant( $mark_3 ), ( $case_3 ) ? TRUE : FALSE );
	$title = brand_setbranding( $title, $brand_4, constant( $mark_4 ), ( $case_4 ) ? TRUE : FALSE );
	$title = brand_setbranding( $title, $brand_5, constant( $mark_5 ), ( $case_5 ) ? TRUE : FALSE );

	return $title;
}