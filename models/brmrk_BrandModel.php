<?php
/**
 * Created by PhpStorm.
 * User: patrick
 * Date: 14-02-08
 * Time: 11:34 PM
 */

class brmrk_BrandModel {
	private $_brand = '';
	private $_mark = '';
	private $_case_sensitive = false;
	private $_apply_only_once = false;

	public function __construct($brand, $mark, $case_sensitive, $apply_only_once) {
		$this->set_brand($brand);
		$this->set_mark($mark);
		$case_sensitive ? $this->set_case_sensitive() : $this->set_case_insensitive();
		$apply_only_once ? $this->set_apply_only_once() : $this->set_apply_more_than_once();
	}

	public function get_brand() {
		return $this->_brand;
	}

	public function set_brand($newBrand) {
		//TODO: sanitize input
		$this->_brand = $newBrand;
	}

	public function get_mark() {
		return $this->_mark;
	}

	public function set_mark($new_mark) {
		//TODO: create enum structure for trademark options
		$this->_mark = $new_mark;
	}

	public function is_case_sensitive() {
		return $this->_case_sensitive;
	}

	public function set_case_sensitive() {
		$this->_case_sensitive = true;
	}

	public function set_case_insensitive() {
		$this->_case_sensitive = false;
	}

	public function apply_only_once() {
		return $this->_apply_only_once;
	}

	public function set_apply_only_once() {
		$this->_apply_only_once = true;
	}

	public function set_apply_more_than_once() {
		$this->_apply_only_once = false;
	}
} 