<?php
namespace OpenSemanticSearch;

use Solarium\Core\Client\Response;
use Solarium\QueryType\Select\Result\Result;

require_once( __DIR__ . '/../traits/array_access.php' );

class SolrJSONResult extends Result implements \ArrayAccess, ResultInterface {
	use array_access;


	/**
	 * @return array
	 */
	public function items() {
		return $this->hasItems() ? $this['response']['docs'] : [];
	}

	/**
	 * @return int
	 */
	public function start() {
		return $this->hasItems() ? $this['response']['start'] : 0;
	}

	/**
	 * @return int
	 */
	public function count() {
		return $this->hasItems() ? $this['response']['numFound'] : 0;
	}

	/**
	 * @return bool
	 */
	public function hasItems() {
		return isset($this['response']);
	}

	/**
	 * Return opposite of isError
	 * @return bool
	 */
	public function isOK() {
		return !$this->isError();
	}

	/**
	 * @return bool
	 */
	public function isError() {
		return isset( $this['error'] );
	}

	/**
	 * @return string
	 */
	public function errorMessage() {
		return $this->isError() ? $this['error']['msg'] : '';
	}

	/**
	 * @return int
	 */
	public function errorCode() {
		return $this->isError() ? $this['error']['code'] : 0;
	}

}
