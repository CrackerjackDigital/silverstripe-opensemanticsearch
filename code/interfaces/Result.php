<?php
namespace OpenSemanticSearch;


interface ResultInterface extends ServiceInterface {

	/**
	 * Return the items from the response, e.g. may not be the response itself but a nested array.
	 *
	 * @return \Traversable|array
	 */
	public function items();

	/**
	 * Return the response data in a decoded format, e.g if the response is in json, then a json_decoded array
	 * @return array
	 */
	public function data();


	/**
	 * Returns if the response was succesfull or not (opposite of isError).
	 * @return mixed
	 */
	public function isOK();

	/**
	 * Return if the response indicates an error or node.
	 * @return bool
	 */
	public function isError();

	/**
	 * Return a displayable error message if isError returns true
	 * @return string
	 */
	public function errorMessage();

	/**
	 * Returns if the response contains response items, such as a list of documents
	 * @return bool
	 */
	public function hasItems();

	/**
	 * Return the count of items which can be returned, or 0 if none
	 * @return int
	 */
	public function count();

	/**
	 * Return the first item index in the result set (e.g if paginated)
	 * @return int
	 */
	public function start();

}