<?php
namespace OpenSemanticSearch;
/**
 * OSSResult wraps a result returned from a call to OpenSemanticSearch endpoints.
 *
 * @package OpenSemanticSearch
 */
class OSSResult extends OKResult {

	/**
	 * Return empty list as no models returned by API.
	 * @return \ArrayList
	 */
	public function models() {
		return new \ArrayList();
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
	 * @return null
	 */
	public function query() {
		return null;
	}
}
