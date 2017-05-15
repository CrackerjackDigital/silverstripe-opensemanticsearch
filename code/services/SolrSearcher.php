<?php

namespace OpenSemanticSearch\Services;

use DataObject;
use File;
use InvalidArgumentException;
use OpenSemanticSearch\Interfaces\OSSID;
use OpenSemanticSearch\Interfaces\SearchInterface;
use OpenSemanticSearch\Models\IndexedURL;
use Page;
use SiteTree;

abstract class SolrSearcher extends Service implements SearchInterface {
	const ServiceSolr    = self::TypeSolr;
	const EndpointSearch = 'search';
	const DefaultLimit   = 100;

	/** @var mixed implementation specific options to pass to search library */
	protected $searchOptions;

	/**
	 * Set or get options to pass to implementation
	 *
	 * @param array $options
	 *
	 * @return SearchInterface|mixed
	 * @fluent-setter
	 */
	public function searchOptions($options = null) {
		if (func_num_args()) {
			$this->searchOptions = $options;
			return $this;
		} else {
			return $this->searchOptions;
		}
	}

	/**
	 * Find a single specific indexed File
	 *
	 * @param mixed|File $fileOrIDOrPath
	 *
	 * @param bool       $updateMetaData on the found model, doesn't write it
	 *
	 * @return File|DataObject|null
	 * @throws \InvalidArgumentException
	 */
	public function findFile( $fileOrIDOrPath, $updateMetaData = true ) {
		if ( $fileOrIDOrPath && is_int( $fileOrIDOrPath ) ) {
			$file = File::get()->byID( $fileOrIDOrPath );
		} elseif ( $fileOrIDOrPath instanceof File ) {
			$file = $fileOrIDOrPath;
		} elseif ( $fileOrIDOrPath ) {
			$file = File::get()->filter( [ 'Filename' => $fileOrIDOrPath ] )->first();
		} else {
			throw new \InvalidArgumentException( 'Invalid fileOrID' );
		}

		return $this->find( $file, $updateMetaData );
	}

	/**
	 * Find a single specific indexed page
	 *
	 * @param mixed|\SiteTree $pageOrIDPath
	 *
	 * @param bool            $updateMetaData on the found model, doesn't write it
	 *
	 * @return null|Page|DataObject
	 * @throws InvalidArgumentException
	 */
	public function findPage( $pageOrIDPath, $updateMetaData = true ) {
		if ( $pageOrIDPath && is_int( $pageOrIDPath ) ) {
			$page = SiteTree::get()->byID( $pageOrIDPath );
		} elseif ( $pageOrIDPath instanceof SiteTree ) {
			$page = $pageOrIDPath;
		} elseif ( $pageOrIDPath ) {
			$page = SiteTree::get_by_link( $pageOrIDPath )->first();
		} else {
			throw new \InvalidArgumentException( 'Invalid pageOrIDPath' );
		}

		return $this->find( $page, $updateMetaData );
	}

	/**
	 * Find a single specific indexed url
	 *
	 * @param int|string|OSSID $ossidOrIndexedURL
	 *
	 * @return OSSID|IndexedURL|DataObject
	 * @throws InvalidArgumentException
	 */
	public function findURL( $ossidOrIndexedURL, $updateMetaData = true ) {
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

		return $this->find( $model, $updateMetaData );
	}

}