<?php

namespace OpenSemanticSearch\Results;

use OpenSemanticSearch\Interfaces\ResultInterface;

/**
 * Generic response to return if no real response can be constructed due to an error happening.
 *
 * @package OpenSemanticSearch
 */
class ErrorResult extends Result implements ResultInterface {

	// e.g. 'Forbidden' or 'Not Found'
	protected $message;
	// e.g. for HTTP 401 or 403
	protected $code;
	// data this may contain a native message and code so should be extracted accordingly by a derived class.
	protected $data;
	// probably not used in the case of errors
	protected $limit;

	public function __construct( $code = null, $data = null, $message = 'Failed' ) {
		parent::__construct( $code, $data, $message );
	}

	/**
	 * Return an empty ArrayList for an error.
	 *
	 * @return \ArrayList
	 */
	public function models() {
		return new \ArrayList();
	}

	/**
	 * Return the underlying response data, e.g. response body or json decoded array.
	 *
	 * @return mixed
	 */
	public function response() {
		return $this->data;
	}

	/**
	 * Returns if the response was succesfull or not (opposite of isError).
	 *
	 * @return mixed
	 */
	public function isOK() {
		return ! $this->isError();
	}

	/**
	 * Return if the response indicates an error or node.
	 *
	 * @return bool
	 */
	public function isError() {
		return true;
	}

	/**
	 * Return a displayable error message set in ctor.
	 *
	 * @return string
	 */
	public function resultMessage() {
		return $this->message;
	}

	/**
	 * Return the result code set in ctor.
	 *
	 * @return mixed
	 */
	public function resultCode() {
		return $this->code;
	}

	/**
	 * Returns if the response contains response items, such as a list of documents
	 *
	 * @return bool
	 */
	public function hasItems() {
		return false;
	}

	/**
	 * Return the count of items which can be returned, or 0 if none
	 *
	 * @return int
	 */
	public function count() {
		return 0;
	}

	/**
	 * Return the items from the response, e.g. may not be the response itself but a nested array.
	 *
	 * @return \Traversable|array
	 */
	public function items() {
		return [];
	}

	/**
	 * Return the first item index in the result set (e.g if paginated)
	 *
	 * @return int
	 */
	public function start() {
		return 0;
	}


	/**
	 * Return the number of items requested, or null if no limit
	 *
	 * @return int|null
	 */
	public function limit() {
		return $this->limit;
	}
}