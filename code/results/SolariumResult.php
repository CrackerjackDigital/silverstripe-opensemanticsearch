<?php

namespace OpenSemanticSearch\Results;

use File;
use OpenSemanticSearch\Exceptions\Exception;
use OpenSemanticSearch\Extensions\MetaDataExtension;
use OpenSemanticSearch\Interfaces\ResultInterface;
use OpenSemanticSearch\Interfaces\ServiceInterface;
use OpenSemanticSearch\Models\IndexedURL;
use OpenSemanticSearch\Services\Search;
use OpenSemanticSearch\Traits\json;
use Modular\Interfaces\HTTP as HTTP;
use Page;

class SolariumResult extends Result implements ResultInterface {
	use json;

	// Mapper requires a source name to select the map to use.
	const MapperSourceName = 'solarium';

	/**
	 * SolariumResult constructor.
	 *
	 * @param mixed                              $code
	 * @param \Solarium\Core\Query\Result\Result $result
	 * @param string                             $message
	 *
	 * @throws \Solarium\Exception\RuntimeException
	 * @throws \Solarium\Exception\UnexpectedValueException
	 */
	public function __construct( $code = null, $result = null, $message = null ) {
		parent::__construct(
			$code ?: $result->getResponse()->getStatusCode(),
			$result,
			$message ?: $result->getResponse()->getStatusMessage()
		);
	}

	/**
	 * @return array|\Traversable
	 * @throws \Solarium\Exception\RuntimeException
	 * @throws \Solarium\Exception\UnexpectedValueException
	 */
	public function items() {
		$items = [];
		if ( $decoded = $this->data() ) {
			$items = isset( $decoded['response']['docs'] )
				? $decoded['response']['docs']
				: [];
		}

		return $items;
	}

	/**
	 * Turn a decoded response from e.g. 'search' into SilverStripe models in a list, e.g of File and Page models.
	 *
	 * @param bool $updateMetaData if true then found models will also be updated from the results from search index
	 *                             using MetaDataExtensions.updateOSSMetaData()
	 *
	 * @param int  $include        bitfield of what models to include in results
	 *
	 * @return \ArrayList
	 * @throws \OpenSemanticSearch\Exceptions\Exception
	 * @throws null
	 */
	public function models( $updateMetaData = false, $include = ServiceInterface::IncludeAll ) {
		$models = new \ArrayList();
		try {

			$service = Search::get();

			if ( $this->hasItems() ) {
				$items = $this->items();

				$fileNames = [];

				foreach ( $items as $item ) {
					$id     = $item['id'];
					$scheme = parse_url( $id, PHP_URL_SCHEME );

					if ( $scheme == HTTP::SchemeFile ) {
						if ( ( $include & ServiceInterface::IncludeFiles ) === ServiceInterface::IncludeFiles ) {
							if ( $filePathName = $service->remoteToLocalPath( $id ) ) {
								$fileNames[] = $filePathName;
							}
						}
					}
				}
				// preload files in one hit then can do a 'find' in them.
				$files = \File::get()->filter( 'Filename', $fileNames );
				foreach ( $items as $item ) {
					$id     = $item['id'];
					$scheme = parse_url( $id, PHP_URL_SCHEME );

					if ( $scheme == HTTP::SchemeFile ) {
						// deal with files
						if ( ( $include & ServiceInterface::IncludeFiles ) === ServiceInterface::IncludeFiles ) {
							if ( $filePathName = $service->remoteToLocalPath( $id ) ) {

								/** @var MetaDataExtension|File $file */

								if ( $file = $files->find( 'Filename', $filePathName ) ) {
									if ( $updateMetaData ) {
										$file->updateOSSMetaData( static::MapperSourceName, $item );
									}
									$models->push( $file );
								}
							}
						}
					} else if ( in_array( $scheme, [ HTTP::SchemeHTTP, HTTP::SchemeHTTPS ] ) ) {
						// deal with pages/urls.
						$path = parse_url( $id, PHP_URL_PATH );

						if ( \Director::is_site_url( $id ) ) {
							if ( ( $include & ServiceInterface::IncludeLocalPages ) === ServiceInterface::IncludeLocalPages ) {

								/** @var MetaDataExtension|Page $page */

								if ( $page = \SiteTree::get_by_link( $path ) ) {
									if ( $updateMetaData ) {
										$page->updateOSSMetaData( static::MapperSourceName, $item );
									}
									$models->push( $page );
								}
							}
						} else if ( ( $include & ServiceInterface::IncludeRemoteURLs ) === ServiceInterface::IncludeRemoteURLs ) {
							/** @var MetaDataExtension $model */

							$model = IndexedURL::get()->filter( [
								IndexedURL::URLField => $path,
							] )->first();

							if ( $model ) {
								if ( $updateMetaData ) {
									$model->updateOSSMetaData( static::MapperSourceName, $item );
								}
								$models->push( $model );
							}

						}
					}
				}
			}
		} catch ( \Exception $e ) {
			throw new Exception( $e->getMessage(), $e->getCode(), $e );
		}

		return $models;
	}

	/**
	 * Returns json decoded data
	 *
	 * @param null $data
	 *
	 * @return mixed
	 * @throws \Solarium\Exception\RuntimeException
	 * @throws \Solarium\Exception\UnexpectedValueException
	 */
	public function data( $data = null ) {
		if ( func_num_args() ) {
			$this->data = $data;
		}

		return $this->result()->getData();
	}

	/**
	 * Convenience method to hint type of data.
	 *
	 * @return \Solarium\Core\Query\Result\Result
	 */
	protected function result() {
		return $this->data;
	}

	/**
	 * @return int
	 */
	public function start() {
		return $this->result()->getQuery()->getStart();
	}

	/**
	 * @return int
	 */
	public function limit() {
		return $this->result()->getQuery()->getRows();
	}

	/**
	 * Return the count of items which can be returned, or 0 if none. Depending on implementation this
	 * could be the total count, or the count available from start, or from start+limit.
	 *
	 * @return int
	 */
	public function count() {
		return $this->result()->getNumFound();
	}

	/**
	 * @return bool
	 */
	public function hasItems() {
		return $this->count() > 0;
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
		return ! $this->result()->getStatus();
	}

	/**
	 * @return string
	 */
	public function resultMessage() {
		return $this->result()->getResponse()->getStatusMessage();
	}

	public function resultCode() {
		return $this->result()->getResponse()->getStatusCode();
	}

}
