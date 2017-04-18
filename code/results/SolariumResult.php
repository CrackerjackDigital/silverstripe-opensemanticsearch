<?php
namespace OpenSemanticSearch\Results;

use OpenSemanticSearch\Interfaces\ResultInterface;
use OpenSemanticSearch\Interfaces\ServiceInterface;
use OpenSemanticSearch\Models\IndexedURL;
use OpenSemanticSearch\Services\SolariumSearcher;
use Solarium\QueryType\Select\Result\Result;

class SolariumResult extends Result implements ResultInterface {

	/**
	 * @return array
	 */
	public function items() {
		return $this->getDocuments();
	}

	/**
	 * Turn a decoded response from e.g. 'search' into SilverStripe models in a list, e.g of File and Page models.
	 *
	 * @param int $include bitfield of what models to include in results
	 *
	 * @return \ArrayList
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

				if ( $scheme == 'file' ) {
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

				if ( $scheme == 'file' ) {
					if ( ( $include & ServiceInterface::IncludeFiles ) === ServiceInterface::IncludeFiles ) {
						if ( $filePathName = $service->remoteToLocalPath( $id ) ) {
							if ( $file = $files->find( 'Filename', $filePathName ) ) {
								$models->push( $file );
							}
						}
					}
				} else if ( $scheme == 'http' || $scheme == 'https' ) {
					$path = parse_url( $id, PHP_URL_PATH );

					if ( \Director::is_site_url( $id ) ) {
						if ( ( $include & ServiceInterface::IncludeLocalPages ) === ServiceInterface::IncludeLocalPages ) {
							if ( $page = \SiteTree::get_by_link( $path ) ) {
								$models->push( $page );
							}
						}
					} else if ( ( $include & ServiceInterface::IncludeRemoteURLs ) === ServiceInterface::IncludeRemoteURLs ) {
						$models->push( new IndexedURL( [ 'URI' => $path ] ) );
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
	public function data() {
		return json_decode( $this->getResponse()->getBody() );
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

}
