<?php
namespace OpenSemanticSearch;

abstract class OKResult extends \Object implements ResultInterface {
	protected $data;

	protected $message;

	protected $code;

	protected $start = 0;

	protected $limit = null;

	public function __construct( $data, $message = null, $code = null ) {
		$this->data = $data;
		$this->message = $message;
		$this->code = $code;
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
	 * Returns the code passed in ctor.
	 *
	 * @return mixed
	 */
	public function resultCode() {
		return $this->code;
	}

	/**
	 * Returns the message passed in ctor.
	 *
	 * @return mixed
	 */
	public function resultMessage() {
		return $this->message;
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