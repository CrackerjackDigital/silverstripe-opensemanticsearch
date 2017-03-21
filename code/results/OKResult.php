<?php
namespace OpenSemanticSearch;

abstract class OKResult extends \Object implements ResultInterface {
	private $data;

	private $start = 0;

	private $limit = null;

	public function __construct( $data ) {
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
	 * We presume an OK result is not an Error.
	 *
	 * @return bool
	 */
	public function isError() {
		return false;
	}

	/**
	 * @return \Traversable|array
	 */
	public function items() {
		return $this->data();
	}

	/**
	 * @return int
	 */
	public function start() {
		return $this->start;
	}

	public function limit() {
		return $this->limit;
	}

}