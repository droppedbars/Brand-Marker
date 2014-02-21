<?php
/*
Plugin Name: Brand-Marker
Plugin URI: http://github.com/droppedbars/Brand-Marker
Description: Never forget to mark your brand or trademarks again. Automatically add TM or (R) to trademarks in post title, excerpt and content. Activate, and open 'Settings->Brand Marker'.  Enter in the brands you wish to have marked and check off case sensitivity and frequency of marking.
Version: 0.4.4
Author: Patrick Mauro
Author URI: http://patrick.mauro.ca
License: GPLv2
*/

/*	Copyright 2014 Patrick Mauro (email : patrick@mauro.ca)

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

require_once( dirname( __FILE__ ) . '/shared-globals.php' );
require_once( dirname( __FILE__ ) . '/models/brmrk_BrandModel.php' );
require_once( dirname( __FILE__ ) . '/brmrk_MarkTags.php' );

// function and global prefix: brmrk / BRMRK

/* Function Names */
define( "BRMRK_FNC_INSTALL", 'brmrk_install' );
define( "BRMRK_FNC_INIT", 'brmrk_init' );
define( "BRMRK_FNC_ADMIN_MENU", 'brmrk_menu' );
define( "BRMRK_FNC_REG_SETTINGS", 'brmrk_register_settings' );
define( "BRMRK_FNC_SANITIZE_OPTS", 'brmrk_sanitize_options' );
define( "BRMRK_FNC_SETTINGS_PAGE", 'brmrk_page' );
define( "BRMRK_FNC_UPDATE_VALUE", 'brmrk_update_value' );
define( "BRMRK_FNC_ADMIN_SCRIPTS", 'brmrk_admin_scripts' );

/* Associate WordPress hooks with functions */
register_activation_hook( __FILE__, BRMRK_FNC_INSTALL );
add_action( BRMRK_WP_PLUGIN_INIT, BRMRK_FNC_INIT );
add_action( BRMRK_WP_PLUGIN_ADMIN_MENU, BRMRK_FNC_ADMIN_MENU );
add_action( BRMRK_WP_PLUGIN_ADMIN_INIT, BRMRK_FNC_REG_SETTINGS );
add_filter( BRMRK_WP_THE_CONTENT, BRMRK_FNC_UPDATE_VALUE );
add_filter( BRMRK_WP_THE_EXCERPT, BRMRK_FNC_UPDATE_VALUE );
add_filter( BRMRK_WP_THE_TITLE, BRMRK_FNC_UPDATE_VALUE );

define( "BRMRK_CASE_SENSITIVE", 'case' );
define( "BRMRK_ONCE_ONLY", 'once' );

/*
	Called via the install hook.
	Ensure this plugin is compatible with the WordPress version.
	Set the default option values and store them into the database.
*/
function brmrk_install() {
	// check the install version
	global $wp_version;
	if ( version_compare( $wp_version, '3.5', '<' ) ) {
//		wp_die( 'This plugin requires WordPress version 3.5 or higher.' );
	}

	if ( ! get_option( BRMRK_MARKS ) ) {
		// TODO: May need a migration script to move old brand_1 to brand_0, or to create brand_0
		$brand_marks_arr = array( 'brand_0' => 'BrandMarker', 'mark_0' => brmrk_MarkTags::TRADEMARK_TAG, 'case_0' => true, 'once_0' => false );
		// update the database with the default option values
		update_option( BRMRK_MARKS, $brand_marks_arr );
	}
}

/*
	Called via the init hook.
	Register javascript and CSS files.
*/
function brmrk_init() {
	wp_register_script( 'brmrk_settings_handler', plugins_url( 'assets/settingsHandler.js', __FILE__ ) );
}

/*
	Called via the admin menu hook.
	Define and create the sub-menu item for the plugin under options menu
*/
function brmrk_menu() {
	$page_hook_suffix = add_options_page( __( BRMRK_SETTINGS_PAGE_NAME, BRMRK_PLUGIN_TAG ),
			__( BRMRK_SETTINGS_NAME, BRMRK_PLUGIN_TAG ),
			BRMRK_WP_USER_MANAGE_OPTS, BRMRK_SETTINGS_PAGE_URL, BRMRK_FNC_SETTINGS_PAGE );

	/*
   * Use the retrieved $page_hook_suffix to hook the function that links our script.
   * This hook invokes the function only on our plugin administration screen,
   * see: http://codex.wordpress.org/Administration_Menus#Page_Hook_Suffix
   */
	add_action( 'admin_print_scripts-' . $page_hook_suffix, BRMRK_FNC_ADMIN_SCRIPTS );
}

/*
 * Load any already registered CSS or Javascript files
 */
function brmrk_admin_scripts() {
	/* Link our already registered script to a page */
	wp_enqueue_script( 'brmrk_settings_handler' );
}

/*
 * return an array of brmrk_BrandModel objects from the wordpress options array.
 */
function brmrk_generateBrandObjects( $brand_marks_arr ) {
	$iterator     = 0;
	$brandObjects = Array();
	if (is_array($brand_marks_arr)) {
		foreach ( $brand_marks_arr as $key => $value ) {
			if ( preg_match( '/^brand_(.*[0-9]$)/', $key, $matches ) === 1 ) {
				$matched_iterator        = $matches[1];
				$brandObjects[$iterator] = new brmrk_BrandModel( $brand_marks_arr['brand_' . $matched_iterator], $brand_marks_arr['mark_' . $matched_iterator], $brand_marks_arr['case_' . $matched_iterator], $brand_marks_arr['once_' . $matched_iterator] );
				$iterator ++;
			}
		}
	}
	return $brandObjects;
}

/*
	Called via the appropriate sub-menu hook.
	Create the settings page for the plugin
		Escapes the branding since it goes into a text field.
		No escaping is done on the trademark since it is only compared and not printed.
*/
function brmrk_page() {
	if ( ! current_user_can( BRMRK_WP_USER_MANAGE_OPTS ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}


	// load options
	$brand_marks_arr = get_option( BRMRK_MARKS );
	// set options to variables
	$brands = brmrk_generateBrandObjects( $brand_marks_arr );

	// create form
	echo '<h1>Brand-Marker</h1>';
	echo '<h3>List the brand names that you want ' . brmrk_MarkTags::REGISTERED . ' or ' . brmrk_MarkTags::TRADE_MARK . ' to appear after.</h3>';
	echo '<ul><li>The marking of brands will occur in the order as they are listed here.<br>';
	echo '<li>Any existing trademark symbol on the specified brands will be removed prior to the application of what is selected here.<br>';
	echo '<li>If left <em>Case Sensitive</em> is left unchecked then the mark will be applied regardless of the case.<br>';
	echo '<li>If <em>Apply Only Once</em> is checked, the brand will be marked only the first time it is found.  This applies separately to each title, excerpt and content.<br>';
	echo '</ul>';
	echo '<div class="wrap">';
	echo '	<form method="post" action="options.php">';
	settings_fields( BRMRK_SETTINGS );
	echo '<input type="hidden" value="5" id="rowCounter" />';
	echo '<div id="brmrk_brandRows">';
	for ( $i = 0; $i < sizeof( $brands ); $i ++ ) {
		echo '		<div id="brmrk_row_' . $i . '">';
		echo '			<input type="button" class="button-primary" value="-" onclick="brmrk_removeRowOnClick(\'brmrk_row_' . $i . '\')"/>';
		echo '			<input type="text" name="' . BRMRK_MARKS . '[brand_' . $i . ']" value="' . $brands[$i]->get_brand() . '" size="24">';
		echo '			<select name="' . BRMRK_MARKS . '[mark_' . $i . ']">';
		echo '				<option value="' . brmrk_MarkTags::BLANK_TAG . '" ' . selected( $brands[$i]->get_mark(), brmrk_MarkTags::BLANK_TAG ) . '>' . brmrk_MarkTags::BLANK . '</option>';
		echo '				<option value="' . brmrk_MarkTags::REGISTERED_TAG . '" ' . selected( $brands[$i]->get_mark(), brmrk_MarkTags::REGISTERED_TAG ) . '>' . brmrk_MarkTags::REGISTERED . '</option>';
		echo '				<option value="' . brmrk_MarkTags::TRADEMARK_TAG . '" ' . selected( $brands[$i]->get_mark(), brmrk_MarkTags::TRADEMARK_TAG ) . '>' . brmrk_MarkTags::TRADE_MARK . '</option>';
		echo '			</select>';
		echo '			<label><input type="checkbox" name="' . BRMRK_MARKS . '[case_' . $i . ']" value="' . BRMRK_CASE_SENSITIVE . '" ' . checked( $brands[$i]->is_case_sensitive(), true, false ) . '>Case Sensitive</label>';
		echo '			<label><input type="checkbox" name="' . BRMRK_MARKS . '[once_' . $i . ']" value="' . BRMRK_ONCE_ONLY . '" ' . checked( $brands[$i]->apply_only_once(), true, false ) . '>Apply Only Once</label>';
		echo '			<br>';
		echo '		</div>';
	}
	echo '</div>';
	echo ' 		<input type="button" class="button-primary" value="Add Brand" onclick="brmrk_addRowOnClick()"/><p>';

	echo '		<input type="submit" class="button-primary" value="';
	_e( 'Save Changes', BRMRK_PLUGIN_TAG );
	echo '" />';
	echo '	</form>';
	echo '</div>';
}

/*
	Store the settings after the user has submitted the settings form.
*/
function brmrk_register_settings() {
	// register settings
	register_setting( BRMRK_SETTINGS, BRMRK_MARKS, BRMRK_FNC_SANITIZE_OPTS );
}

/*
 * Sanitize the options set in the options page;
 * It copies the expected options into a second hash so as to remove any unexpected values
 * All options at this time are text, so just sanitizes the them as text fields.
 */
function brmrk_sanitize_options( $options ) {
	if ( ! is_null( $options ) ) {
		$sanitized_options = array();
		$iterator          = 0;
		foreach ( $options as $key => $value ) {
			// look for a 'brand_XXX' item and work from it, otherwise move on
			if ( preg_match( '/^brand_(.*[0-9]$)/', $key, $matches ) === 1 ) {
				$matched_iterator = $matches[1];
				$brand            = sanitize_text_field( $value );
				// make sure there is a value in the brand field before saving it
				if ( strlen( $brand ) > 0 ) {
					$sanitized_options['brand_' . $iterator] = ( ! empty( $brand ) ) ? $brand : '';
					$sanitized_options['mark_' . $iterator]  = ( ! empty( $options['mark_' . $matched_iterator] ) ) ? sanitize_text_field( $options['mark_' . $matched_iterator] ) : '';

					if ( isset( $options['case_' . $matched_iterator] ) ) {
						$sanitized_options['case_' . $iterator] = true;
					} else {
						$sanitized_options['case_' . $iterator] = false;
					}
					if ( isset( $options['once_' . $matched_iterator] ) ) {
						$sanitized_options['once_' . $iterator] = true;
					} else {
						$sanitized_options['once_' . $iterator] = false;
					}
					$iterator ++;
				}
			}
		}

		return $sanitized_options;
	} else {
		return null;
	}
}

/*
	Search $content string for all occurrences of $brand and remove $symbol if it trails it.
*/
function brmrk_removebranding( $content, brmrk_BrandModel $brand, $mark ) {
	$brand->is_case_sensitive() ? $reg_trail = 'i' : $reg_trail = '';

	return preg_replace( '/\b(' . trim( $brand->get_brand() ) . ')' . addslashes( $mark ) . '/' . $reg_trail, '${1}', $content );
}

/*
	Search $content for all occurrences of $brand and add $symbol after it.
*/
function brmrk_addbrand( $content, brmrk_BrandModel $brand ) {

	// if it's case sensitive, then we need to add 'i' to the regex
	$brand->is_case_sensitive() ? $reg_trail = '' : $reg_trail = 'i';

	if ( $brand->apply_only_once() ) {
		return preg_replace( '/\b(' . trim( $brand->get_brand() ) . ')\b/' . $reg_trail, '${1}' . addslashes( brmrk_MarkTags::get_mark( $brand->get_mark() ) ), $content, 1 );
	} else {
		return preg_replace( '/\b(' . trim( $brand->get_brand() ) . ')\b/' . $reg_trail, '${1}' . addslashes( brmrk_MarkTags::get_mark( $brand->get_mark() ) ), $content );
	}
}

/*
	Parse $content, ensuring occurrences of $brand have the appropriate trademark symbol afterwards
*/
function brmrk_setbranding( $content, brmrk_BrandModel $brand ) {
	$name = $brand->get_brand();

	if ( ( isset( $name ) ) && ( strlen( trim( $name ) ) > 0 ) ) {
		$temp_storage = $content;

		foreach ( brmrk_MarkTags::get_array_registered_marks() as $value ) {
			$temp_storage = brmrk_removebranding( $temp_storage, $brand, $value );
		}
		foreach ( brmrk_MarkTags::get_array_trade_marks() as $value ) {
			$temp_storage = brmrk_removebranding( $temp_storage, $brand, $value );
		}

		return brmrk_addbrand( $temp_storage, $brand );
	} else {
		return $content;
	}
}

/*
 * Used as a hook for content, excerpt and title.  Update the content with the appropriate brand markings
 */
function brmrk_update_value( $value ) {
	$brand_marks_arr = get_option( BRMRK_MARKS );
	// set options to variables
	$brands = brmrk_generateBrandObjects( $brand_marks_arr );
	foreach ( $brands as $brand ) {
		$value = brmrk_setbranding( $value, $brand );
	}

	return $value;
}
