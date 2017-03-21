<?php
namespace OpenSemanticSearch;
/**
 * array_access adds ArrayAccess syntax to a class.
 *
 * @package OpenSemanticSearch
 */
trait array_access {
	private $ossData = [];

	public function __construct( array $data ) {
		$this->ossData = $data;
		parent::__construct();
	}

	public function data() {
		return $this->ossData;
	}

	public function __get( $name ) {
		return $this[ $name ];
	}

	public function __set( $name, $value ) {
		$this[ $name ] = $value;
	}

	public function __unset( $name ) {
		unset( $this[ $name ] );
	}

	/**
	 *
	 * ArrayAccess method
	 *
	 * @param mixed $offset
	 *
	 * @return bool
	 */
	public function offsetExists( $offset ) {
		return array_key_exists( $offset, $this->ossData );
	}

	/**
	 *
	 * ArrayAccess method
	 *
	 * @param mixed $offset
	 *
	 * @return mixed
	 */
	public function offsetGet( $offset ) {
		return $this->ossData[ $offset ];
	}

	/**
	 *
	 * ArrayAccess method
	 *
	 * @param mixed $offset
	 *
	 * @param mixed $value
	 *
	 * @return void
	 */

	public function offsetSet( $offset, $value ) {
		$this->ossData[ $offset ] = $value;
	}

	/**
	 *
	 * ArrayAccess method
	 *
	 * @param mixed $offset
	 *
	 * @return void
	 */
	public function offsetUnset( $offset ) {
		unset( $this->ossData[ $offset ] );
	}
}