<?php

namespace OpenSemanticSearch\Services;

use File;
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
	 * @param mixed|\File $fileOrIDOrPath
	 *
	 * @return mixed
	 * @throws \InvalidArgumentException
	 *
	 */
	public function findFile( $fileOrIDOrPath ) {
		if ( $fileOrIDOrPath && is_int( $fileOrIDOrPath ) ) {
			$file = SiteTree::get()->byID( $fileOrIDOrPath );
		} elseif ( $fileOrIDOrPath instanceof File ) {
			$file = $fileOrIDOrPath;
		} elseif ( $fileOrIDOrPath ) {
			$file = File::get()->filter( [ 'Filename' => $fileOrIDOrPath ] )->first();
		} else {
			throw new \InvalidArgumentException( 'Invalid fileOrID' );
		}

		return $this->find( $file );
	}

	/**
	 * @param mixed|\SiteTree $pageOrIDPath
	 *
	 * @return mixed
	 * @throws \InvalidArgumentException
	 * @throws \OpenSemanticSearch\Exceptions\SearchException
	 */
	public function findPage( $pageOrIDPath ) {
		if ( $pageOrIDPath && is_int( $pageOrIDPath ) ) {
			$page = SiteTree::get()->byID( $pageOrIDPath );
		} elseif ( $pageOrIDPath instanceof SiteTree ) {
			$page = $pageOrIDPath;
		} elseif ( $pageOrIDPath ) {
			$page = SiteTree::get_by_link( $pageOrIDPath )->first();
		} else {
			throw new \InvalidArgumentException( 'Invalid pageOrIDPath' );
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
			throw new \InvalidArgumentException( 'Invalid ossidOrIndexedURL' );
		}

		return $this->find( $model );
	}

}