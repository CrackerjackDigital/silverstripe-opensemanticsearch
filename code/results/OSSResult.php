<?php
namespace OpenSemanticSearch;
/**
 * OSSResult wraps a result returned from a call to OpenSemanticSearch endpoints.
 *
 * @package OpenSemanticSearch
 */
class OSSResult extends OKResult {

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
