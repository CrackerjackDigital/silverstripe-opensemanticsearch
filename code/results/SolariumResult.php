<?php

namespace OpenSemanticSearch\Results;

use OpenSemanticSearch\Interfaces\ResultInterface;
use OpenSemanticSearch\Interfaces\ServiceInterface;
use OpenSemanticSearch\Models\IndexedURL;
use OpenSemanticSearch\Services\SolariumSearcher;
use OpenSemanticSearch\Traits\json;
use Modular\Interfaces\HTTP as HTTP;

class SolariumResult extends Result implements ResultInterface {
	use json;

	public function __construct( $code = null, $data = null, $message = null ) {
		parent::__construct( $code, $data, $message );
	}

	/**
	 * @return \Traversable|array
	 */
	public function items() {
		$items   = [];
		$decoded = $this->decode( $this->data() );

		return $items;
	}

	/**
	 * Turn a decoded response from e.g. 'search' into SilverStripe models in a list, e.g of File and Page models.
	 *
	 * @param int $include bitfield of what models to include in results
	 *
	 * @return \ArrayList
	 * @throws \InvalidArgumentException
	 */
	public function models( $include = ServiceInterface::IncludeAll ) {
		$models = new \ArrayList();

		$service = SolariumSearcher::get();

		if ( $this->hasItems() ) {
			$items = $this->items();

			$files = [];

			foreach ( $items as $item ) {
				$id     = $item['id'];
				$scheme = parse_url( $id, PHP_URL_SCHEME );

				if ( $scheme == HTTP::SchemeFile ) {
					if ( ( $include & ServiceInterface::IncludeFiles ) === ServiceInterface::IncludeFiles ) {
						if ( $filePathName = $service->remoteToLocalPath( $id ) ) {
							$files[] = $filePathName;
						}
					}
				}
			}
			// preload files in one hit then can do a 'find' in them.
			// TODO is this actually faster? Theoretically cuts down number of database accesses...
			$files = \File::get()->filter( 'Filename', $files );
			foreach ( $items as $item ) {
				$id     = $item['id'];
				$scheme = parse_url( $id, PHP_URL_SCHEME );

				if ( HTTP::PartScheme == HTTP::SchemeFile ) {
					if ( ( $include & ServiceInterface::IncludeFiles ) === ServiceInterface::IncludeFiles ) {
						if ( $filePathName = $service->remoteToLocalPath( $id ) ) {
							if ( !$file = $files->find( 'Filename', $filePathName ) ) {
								$file = new \File();
							}
							$file->updateOSSMetaData($item);
							$models->push( $file );
						}
					}
				} else if ( in_array( $scheme, [ HTTP::SchemeHTTP, HTTP::SchemeHTTPS ] ) ) {
					$path = parse_url( $id, PHP_URL_PATH );

					if ( \Director::is_site_url( $id ) ) {
						if ( ( $include & ServiceInterface::IncludeLocalPages ) === ServiceInterface::IncludeLocalPages ) {
							if ( !$page = \SiteTree::get_by_link( $path ) ) {
								$page = new \Page();
								$page->updateOSSMetaData($item);
							}
							$models->push( $page );
						}
					} else if ( ( $include & ServiceInterface::IncludeRemoteURLs ) === ServiceInterface::IncludeRemoteURLs ) {
						$models->push( new IndexedURL( [ IndexedURL::URLField => $path ] ) );
					}
				}
			}
		}

		return $models;
	}

	/**
	 * Returns json_decoded response body.
	 *
	 * @return string decoded json
	 */
	public function data( $data = null ) {
		return $this->data;
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
	 *
	 * @return bool
	 */
	public function isOK() {
		return $this->isError();
	}

	/**
	 * @return bool
	 */
	public function isError() {
		return ! $this->getStatus();
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

	/**
	 * Return the count of items which can be returned, or 0 if none. Depending on implementation this
	 * could be the total count, or the count available from start, or from start+limit.
	 *
	 * @return int
	 */
	public function count() {
		// TODO: Implement count() method.
	}
}
