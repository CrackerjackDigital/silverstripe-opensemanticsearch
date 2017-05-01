<?php
namespace OpenSemanticSearch\Results;

use OpenSemanticSearch\Interfaces\ResultInterface;

abstract class Result extends \Object implements ResultInterface {
	protected $data;

	protected $message;

	protected $code;

	protected $start = 0;

	protected $limit = null;

	/**
	 * Result constructor.
	 *
	 * @param mixed $code
	 * @param mixed $data raw data, e.g. a response body
	 * @param mixed $message
	 */
	public function __construct( $code = null, $data = null, $message = null ) {
		$this->code = $code;
		$this->message = $message;
		$this->data($data);

		parent::__construct();
	}

	/**
	 * Return iterable array of items from the data. If one item should still
	 * be iterable with single item.
	 *
	 * @return \Traversable|array
	 */
	abstract public function items();

	/**
	 * Return the response data in a decoded format, e.g if the response is in json, then a json_decoded array.
	 * If set to a custom object then may also return that object, implementation of other methods
	 * will have to deal with extracting information from that object instead of using defaults in this class
	 * (e.g. count) which expect an array or equivalent to be present e.g. from json_decode.
	 *
	 * @param mixed $data set data to this if provided.
	 *
	 * @return array
	 */
	public function data( $data = null ) {
		if (func_num_args()) {
			$this->data = $data;
		}
		return $this->data;
	}

	/**
	 * @return bool
	 */
	public function hasItems() {
		return $this->count() > 0;
	}

	/**
	 * @return int
	 */
	public function count() {
		return count( $this->items() );
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
	 * @return int
	 */
	public function start() {
		return $this->start;
	}

	public function limit() {
		return $this->limit;
	}

}