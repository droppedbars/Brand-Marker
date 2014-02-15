<?php

/**
 * Created by PhpStorm.
 * User: patrick
 * Date: 14-02-11
 * Time: 8:56 PM
 */
class brmrk_MarkTags {
	const REGISTERED = '&reg;';
	const TRADE_MARK = '&trade;';
	const BLANK      = '';

	const TRADEMARK_TAG  = 'BRMRK_TRADE_MARK';
	const REGISTERED_TAG = 'BRMRK_REGISTERED';
	const BLANK_TAG      = 'BRMRK_BLANK';

	private static $REG_MARKS = array(
			'&reg;',
			'&reg',
			'®',
			'&#174;',
			'&#174'
	);
	private static $TRADE_MARKS = array(
			'&trade;',
			'&trade',
			'™',
			'&#8482;',
			'&#8482'
	);

	public static function get_mark( $mark_type ) {
		switch ( $mark_type ) {
			case brmrk_MarkTags::TRADEMARK_TAG:
				return brmrk_MarkTags::TRADE_MARK;
			case brmrk_MarkTags::REGISTERED_TAG:
				return brmrk_MarkTags::REGISTERED;
			default:
				return brmrk_MarkTags::BLANK;
		}
	}

	public static function get_array_registered_marks() {
		return brmrk_MarkTags::$REG_MARKS;
	}

	public static function get_array_trade_marks() {
		return brmrk_MarkTags::$TRADE_MARKS;
	}
} 