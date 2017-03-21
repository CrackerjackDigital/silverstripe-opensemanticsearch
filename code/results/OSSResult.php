<?php
namespace OpenSemanticSearch;

class OSSResult extends \Object implements ResultInterface {
	private $data;

	public function __construct($data) {
		$this->data = $data;
		parent::__construct();
	}

	public function data() {
		return $this->data;
	}

	/**
	 * @return mixed
	 */
	public function response() {
		return $this->data;
	}

	/**
	 * Return opposite of isError
	 *
	 * @return bool
	 */
	public function isOK() {
		return ! $this->isError();
	}

	/**
	 * @return bool
	 */
	public function isError() {
		return !(bool)$this->data;
	}

	public function errorMessage() {
		return "Request returned '" . print_r($this->data, true) . "'";
	}

	/**
	 * @return bool
	 */
	public function hasItems() {
		return false;
	}

	/**
	 * @return int
	 */
	public function count() {
		return 0;
	}

	/**
	 * @return \Traversable|array
	 */
	public function items() {
		return [];
	}

	/**
	 * @return int
	 */
	public function start() {
		return 0;
	}

	/**
	 * @return null
	 */
	public function query() {
		return null;
	}
}
