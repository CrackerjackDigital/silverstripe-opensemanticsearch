<?php

namespace OpenSemanticSearch\Services;

use OpenSemanticSearch\Interfaces\OSSID;
use OpenSemanticSearch\Interfaces\SearchInterface;
use OpenSemanticSearch\Models\IndexedURL;
use SiteTree;

abstract class SolrSearcher extends Service implements SearchInterface {
	const ServiceSolr    = self::TypeSolr;
	const EndpointSearch = 'search';

	/**
	 * Find a specific indexed document by id (path)
	 *
	 * @param mixed|\File $fileOrID
	 *
	 * @return mixed
	 * @throws \InvalidArgumentException
	 *
	 */
	public function findFile( $fileOrID ) {
		if ( $fileOrID && is_int( $fileOrID ) ) {
			$file = SiteTree::get()->byID( $fileOrID );
		} elseif ( $fileOrID instanceof \File ) {
			$file = $fileOrID;
		} else {
			throw new \InvalidArgumentException( 'fileOrID' );
		}

		return $this->find( $file );
	}

	/**
	 * @param mixed|\SiteTree $pageOrID
	 *
	 * @return mixed
	 * @throws \InvalidArgumentException
	 * @throws \OpenSemanticSearch\Exceptions\SearchException
	 */
	public function findPage( $pageOrID ) {
		if ( $pageOrID && is_int( $pageOrID ) ) {
			$page = SiteTree::get()->byID( $pageOrID );
		} elseif ( $pageOrID instanceof SiteTree ) {
			$page = $pageOrID;
		} else {
			throw new \InvalidArgumentException( 'fileOrID' );
		}

		return $this->find( $page );
	}

	/**
	 * @param int|string|OSSID $ossidOrIndexedURL
	 *
	 * @return mixed
	 * @throws \InvalidArgumentException
	 */
	public function findURL( $ossidOrIndexedURL ) {
		if ( $ossidOrIndexedURL instanceof OSSID ) {

			$model = $ossidOrIndexedURL;

		} elseif ( $ossidOrIndexedURL && is_int( $ossidOrIndexedURL ) ) {

			$model = IndexedURL::get()->byID( $ossidOrIndexedURL );

		} elseif ( is_string( $ossidOrIndexedURL ) ) {

			$model = new IndexedURL( [
				IndexedURL::URLField => $ossidOrIndexedURL,
			] );

		} else {
			throw new \InvalidArgumentException( 'fileOrID' );
		}

		return $this->find( $model );
	}

}