<?php

namespace OpenSemanticSearch\Interfaces;

interface IndexInterface extends PathMappingInterface, ServiceInterface {
	const ServiceName = 'IndexService';

	/**
	 * @param \DataObject $item
	 * @param string      $resultMessage
	 *
	 * @return mixed
	 */
	public function add( $item, &$resultMessage = '' );

	/**
	 * @param \DataObject $item
	 * @param string      $resultMessage
	 *
	 * @return mixed
	 */
	public function remove( $item, &$resultMessage = '' );

	/**
	 * @param \DataObject $item
	 * @param string      $resultMessage
	 *
	 * @return mixed
	 */
	public function reindex( $item, &$resultMessage = '' );


	/**
	 * @param string $localPath relative to assets folder or absolute from wb root root of file to add to index.
	 *
	 * @return bool
	 * @api
	 */
	public function addFile( $localPath );

	/**
	 * @param string $localPath relative to assets folder or absolute from wb root root of file to add to index.
	 *
	 * @return bool
	 * @api
	 */
	public function addDirectory( $localPath );

	/**
	 * Add a specific page to the index by page id or page object. This will be added as it can be seen rendered in the browser.
	 *
	 * @param \Page|int $pageOrID
	 *
	 * @return bool
	 */
	public function addPage( $pageOrID );

	/**
	 * Add a url to the index, no further checks are made e.g. to check the url is one from this site but it is indexed verbatim.
	 *
	 * @param $url
	 *
	 * @return bool
	 */
	public function addURL( $url );

	/**
	 * Removes a file or directory from index.
	 *
	 * @param string $localPath relative to assets folder or absolute from web root of file to add to index.
	 *
	 * @return bool
	 * @api
	 */
	public function removeFilePath( $localPath );

	/**
	 * @param \Page|int $pageOrID
	 *
	 * @return bool
	 */
	public function removePage( $pageOrID );

	/**
	 * @param string $url
	 *
	 * @return mixed
	 */
	public function removeURL( $url );

}