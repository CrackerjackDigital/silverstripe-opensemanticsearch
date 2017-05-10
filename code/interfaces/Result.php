<?php

namespace OpenSemanticSearch\Interfaces;

use ArrayList;
use DataList;

interface ResultInterface {

	/**
	 * Return a list of models form the response.
	 *
	 * @param bool  $updateMetaData if true the update the models which are returned
	 *                              with meta data from the search result. Doesn't write the
	 *                              meta data changes though.
	 *
	 * @param mixed $include        what models to include in the results
	 *
	 * @return DataList|ArrayList
	 */
	public function models( $updateMetaData = false, $include = ServiceInterface::IncludeAll );

	/**
	 * Return the items from the response, e.g. may not be the response itself but a nested array.
	 *
	 * @return \Traversable|array
	 */
	public function items();

	/**
	 * Return the raw response data, not decoded
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
	 *
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