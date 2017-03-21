<?php
namespace OpenSemanticSearch;
/**
 * Generic response to return if no real response can be constructed due to an error happening.
 *
 * @package OpenSemanticSearch
 */
class ErrorResult extends \Object implements ResultInterface {

	// message to display
	private $message;
	// additional data
	private $data;

	public function __construct($message, $data) {
		$this->message = $message;
		$this->data = $data;
		parent::__construct();
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
		return !$this->isError();
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
	 * Return a displayable error message if isError returns true
	 *
	 * @return string
	 */
	public function errorMessage() {
		return $this->message;
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
}