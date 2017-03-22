<?php
namespace OpenSemanticSearch;

use Solarium\QueryType\Select\Result\Result;

class SolariumResult extends Result implements ResultInterface {

	/**
	 * @return array
	 */
	public function items() {
		return $this->getDocuments();
	}

	/**
	 * Returns json_decoded response body.
	 * @return string decoded json
	 */
	public function data() {
		return json_decode($this->getResponse()->getBody());
	}

	/**
	 * @return int
	 */
	public function start() {
		return $this->getQuery()->getStart();
	}

	/**
	 * @return int
	 */
	public function limit() {
		return $this->getQuery()->getRows();
	}

	/**
	 * @return bool
	 */
	public function hasItems() {
		return $this->getNumFound() > 0;
	}

	/**
	 * Return opposite of isError
	 * @return bool
	 */
	public function isOK() {
		return $this->isError();
	}

	/**
	 * @return bool
	 */
	public function isError() {
		return !$this->getStatus();
	}

	/**
	 * @return string
	 */
	public function resultMessage() {
		return $this->getResponse()->getStatusMessage();
	}

	public function resultCode() {
		return $this->getResponse()->getStatusCode();
	}

}
