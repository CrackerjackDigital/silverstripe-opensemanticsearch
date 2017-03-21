<?php
namespace OpenSemanticSearch;

/**
 * Add to a service to encode/decode json request/response.
 *
 * @package OpenSemanticSearch
 */
trait json {
	/**
	 * Encode request data as json string or '' if nothing passed.
	 * @param $requestData
	 * @return string
	 */
	public function encode($requestData) {
		return $requestData ? json_encode($requestData) : '';
	}

	/**
	 * Turn text into decoded json, or null if nothing passed.
	 * @param $responseBody
	 * @return mixed|null
	 */
	public function decode($responseBody) {
		return $responseBody ? json_decode($responseBody, true) : null;
	}
}