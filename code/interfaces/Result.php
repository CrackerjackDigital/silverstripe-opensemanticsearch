<?php
namespace OpenSemanticSearch\Interfaces;

interface ResultInterface {

	/**
	 * Return a list of models form the response.
	 *
	 * @return \SS_List
	 */
	public function models();

	/**
	 * Return the items from the response, e.g. may not be the response itself but a nested array.
	 *
	 * @return \Traversable|array
	 */
	public function items();

	/**
	 * Return the response data in a decoded format, e.g if the response is in json, then a json_decoded array
	 *
	 * @return array
	 */
	public function data();

	/**
	 * Returns if the response was succesfull or not (opposite of isError).
	 *
	 * @return mixed
	 */
	public function isOK();

	/**
	 * Return if the response indicates an error or node.
	 *
	 * @return bool
	 */
	public function isError();

	/**
	 * Return a displayable message, e.g. 'OK' or 'Forbidden'
	 *
	 * @return string
	 */
	public function resultMessage();

	/**
	 * Return a code (e.g. an http response may be 200 for OK, or 403 for forbidden).
	 * @return mixed
	 */
	public function resultCode();

	/**
	 * Returns if the response contains response items, such as a list of documents
	 *
	 * @return bool
	 */
	public function hasItems();

	/**
	 * Return the count of items which can be returned, or 0 if none. Depending on implementation this
	 * could be the total count, or the count available from start, or from start+limit.
	 *
	 * @return int
	 */
	public function count();

	/**
	 * Return the first item index in the result set (e.g if paginated)
	 *
	 * @return int
	 */
	public function start();

	/**
	 * Return the number of items requested, or null if no limit
	 *
	 * @return int|null
	 */
	public function limit();

}