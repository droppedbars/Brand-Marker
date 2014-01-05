<?php
/**
 * Created by PhpStorm.
 * User: patrick
 * Date: 14-01-04
 * Time: 10:02 PM
 */

/*
 * Represents a brand name and trademark symbol pair.
 * In future may include information such as usage frequency, time to use (subject, body, etc)
 */
class brmark_Brand {
    var $brandName = "";
    var $tradeMark = "";
    // var frequency;

    var $REG_MARK = array(
        '&reg;',
        '&reg',
        '®',
        '&#174;',
        '&#174'
    );
    var $TRADE_MARK = array(
        '&trade;',
        '&trade',
        '™',
        '&#8482;',
        '&#8482'
    );

    function __construct() {
        $this->$brandName = "";
        $this->$tradeMark = ""; // need a null trademark?
    }

    function __construct1($name, $mark) {
        $this->$brandName = $name;
        $this->$tradeMark = $mark;
    }

    function returnTradeMarkSymbol($var) {
        // note, strtolower only works on A-Z
        if (in_array(strtolower($var), $this->$REG_MARK, true)) {
            return $this->$REG_MARK[0];
        } else if (in_array(strtolower($var), $this->$TRADE_MARK, true)) {
            return $this->TRADE_MARK[0];
        } else {
            return null;
        }
    }

    function getBrandName() {
        return $this->$brandName;
    }

    function setBrandName($var) {
        $this->$brandName = $var;
    }

    function getTradeMark() {
        return $this->$tradeMark;
    }

    function setTradeMark($var) {
        $this->$tradeMark = $this->returnTradeMarkSymbol($var);
    }
} 