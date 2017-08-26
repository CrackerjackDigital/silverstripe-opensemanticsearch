<?php

namespace OpenSemanticSearch\Interfaces;

interface IndexInterface extends PathMappingInterface, ServiceInterface {
	const ServiceName = 'IndexAdapter';

	/**
	 * @param \DataObject $item e.g Page, File, URL
	 * @param string      $resultMessage should be set to 'OK' or an error message
	 *
	 * @return bool
	 */
	public function add( $item, &$resultMessage = '' );

	/**
	 * @param \DataObject $item
	 * @param string      $resultMessage should be set to 'OK' or an error message
	 *
	 * @return bool
	 */
	public function remove( $item, &$resultMessage = '' );

	/**
	 * @param \DataObject $item
	 * @param string      $resultMessage should be set to 'OK' or an error message
	 *
	 * @return bool
	 */
	public function reindex( $item, &$resultMessage = '' );


}