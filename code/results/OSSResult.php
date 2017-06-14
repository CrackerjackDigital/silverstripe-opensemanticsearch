<?php

namespace OpenSemanticSearch\Results;

use OpenSemanticSearch\Interfaces\ServiceInterface;

/**
 * OSSResult wraps a result returned from a call to OpenSemanticSearch endpoints.
 *
 * @package OpenSemanticSearch
 */
class OSSResult extends Result {

	public function __construct( $code = null, $data = null, $message = 'OK' ) {
		parent::__construct( $code, $data, $message );
	}

	/**
	 * OSS service responses don't have items, return an empty array.
	 * @return array
	 */
	public function items() {
		return [];
	}

	/**
	 * Return empty list as no models returned by API.
	 *
	 * @param bool      $updateMetaData if true then models will be updated from returned search results
	 *                                  via MetaDataExtension.updateOSSMetaData()
	 *
	 * @param int|mixed $include
	 *
	 * @return \ArrayList
	 */
	public function models($updateMetaData = false, $include = ServiceInterface::IncludeAll) {
		return new \ArrayList($this->items());
	}


	/**
	 * @return null
	 */
	public function query() {
		return null;
	}
}
