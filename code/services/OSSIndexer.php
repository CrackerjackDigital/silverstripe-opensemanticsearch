<?php

namespace OpenSemanticSearch\Services;

use Modular\Exceptions\Exception;
use Modular\Extensions\Model\TrackedValue;
use Modular\Interfaces\HTTP as HTTPInterface;
use OpenSemanticSearch\Interfaces\OSSID;
use OpenSemanticSearch\Results\ErrorResult;
use OpenSemanticSearch\Results\OSSResult;
use OpenSemanticSearch\Traits\http;
use OpenSemanticSearch\Traits\json;
use SiteTree;

/**
 * Service represents an Open Semantic Search service which consists of two parts, the OSS provided service which adds, removes and updates
 * files and urls to the index, and the backend indexing service which is Solr.
 *
 * NB Only the SolrGet service is working as of 2017/03/05
 *
 * @package OpenSemanticSearch
 */
class OSSIndexer extends IndexService {
	use json, http;

	// for http::request
	private static $context_options = [
	];

	const ServiceOSS = self::TypeOSS;

	const EndpointIndexDir  = 'index-dir';
	const EndpointIndexFile = 'index-file';
	const EndpointIndexURL  = 'index-web';
	const EndpointRemove    = 'delete';

	/**
	 * @param \DataObject $item
	 * @param string      $resultMessage
	 *
	 * @return bool
	 * @throws \InvalidArgumentException
	 * @throws \Modular\Exceptions\Exception
	 * @throws \OpenSemanticSearch\Exceptions\Exception
	 */
	public function add( $item, &$resultMessage = '' ) {
		/** @var \OpenSemanticSearch\Results\Result $result */
		if ( $item instanceof \Folder ) {
			$result = $this->addDirectory( $item->Link() );
		} elseif ( $item instanceof \File ) {
			$result = $this->addFile( $item->Link() );
		} elseif ( $item instanceof \Page ) {
			$result = $this->addPage( $item );
		} elseif ( $item->hasMethod( 'OSSID' ) ) {
			// item should have a Link method which returns the URL
			$result = $this->addURL( $item->OSSID() );

		} elseif ( $item->hasMethod( 'Link' ) ) {
			// the model should have a Link method which returns the url to index
			$result = $this->addURL( $item->Link() );
		} else {
			$result = new ErrorResult( "Don't know what to do with item '" . get_class( $item ) . "'" );
		}
		$resultMessage = $result->resultMessage();

		return $result->isOK();
	}

	/**
	 * @param \DataObject|TrackedValue $item
	 * @param string                   $resultMessage
	 *
	 * @return bool
	 * @throws \InvalidArgumentException
	 * @throws \Modular\Exceptions\Exception
	 * @throws \OpenSemanticSearch\Exceptions\Exception
	 */
	public function remove( $item, &$resultMessage = '' ) {
		/** @var \OpenSemanticSearch\Results\Result $result */
		if ( $item instanceof \File ) {
			// handles File and Folder

			// if filename has changed we need to remove the old filename
			// saved by TrackedValue extension
			if ( $filename = $item->trackedValue( 'Filename' ) ) {
				$this->removeFilePath( $filename );
			}
			$fileName = $item->Link();

			$result = $this->removeFilePath( $fileName );

		} elseif ( $item instanceof \Page ) {
			$result = $this->removePage( $item );

		} elseif ( $item->hasMethod( 'OSSID' ) || ( $item instanceof OSSID ) ) {
			// item should have a Link method which returns the URL
			$result = $this->removeURL( $item->OSSID() );

		} elseif ( $item->hasMethod( 'Link' ) ) {
			// item should have a Link method which returns the URL
			$result = $this->removeURL( $item->Link() );

		} else {
			$result = new ErrorResult( "Don't know what to do with item '" . get_class( $item ) . "'" );
		}
		$resultMessage = $result->resultMessage();

		return $result->isOK();
	}

	/**
	 * Remove then add the item.
	 *
	 * @param \DataObject $item
	 * @param string      $resultMessage
	 *
	 * @return mixed
	 * @throws \InvalidArgumentException
	 * @throws \Modular\Exceptions\Exception
	 * @throws \OpenSemanticSearch\Exceptions\Exception
	 */
	public function reindex( $item, &$resultMessage = '' ) {
		/** @var \OpenSemanticSearch\Results\Result $result */
		if ( $result = $this->remove( $item, $resultMessage ) ) {
			$result = $this->add( $item, $resultMessage );
		}
		$resultMessage = $result->resultMessage();

		return $result->isOK();
	}

	/**
	 * @param string $localPath relative to assets folder or absolute from wb root root of file to add to index.
	 *
	 * @return bool
	 * @throws \OpenSemanticSearch\Exceptions\Exception
	 * @api
	 */
	public function addFile( $localPath ) {
		return $this->addFilePath( $localPath, self::EndpointIndexFile );
	}

	/**
	 * @param string $localPath relative to assets folder or absolute from wb root root of file to add to index.
	 *
	 * @return bool
	 * @throws \OpenSemanticSearch\Exceptions\Exception
	 * @api
	 */
	public function addDirectory( $localPath ) {
		return $this->addFilePath( $localPath, self::EndpointIndexDir );
	}

	/**
	 * @param string $localPath
	 * @param string $endpoint e.g. self.EndpointIndexFile or self.EndpointIndexDir
	 *
	 * @return bool
	 * @throws \OpenSemanticSearch\Exceptions\Exception
	 */
	public function addFilePath( $localPath, $endpoint ) {
		if ( ! $remotePath = $this->localToRemotePath( $localPath ) ) {
			// doesn't exist in file system or not in a safe place or mappable to a remote path
			return false;
		}

		$uri = $this->rebuildURL( $remotePath, [ HTTPInterface::PartScheme => 'file' ] );

		return $this->request(
			self::ServiceOSS,
			$endpoint,
			[
				'uri' => $uri,
			]
		);

	}

	/**
	 * Removes a file or directory from index.
	 *
	 * @param string $localPath relative to assets folder or absolute from web root of file to add to index.
	 *
	 * @return bool
	 * @throws \OpenSemanticSearch\Exceptions\Exception
	 * @api
	 */
	public function removeFilePath( $localPath ) {
		if ( ! $remotePath = $this->localToRemotePath( $localPath ) ) {
			return false;
		}
		$uri = $this->rebuildURL( $remotePath, [ HTTPInterface::PartScheme => 'file' ] );

		return $this->request(
			self::ServiceOSS,
			self::EndpointRemove,
			[
				'uri' => $uri,
			]
		);
	}

	/**
	 * @param int|\Page $pageOrID
	 *
	 * @return bool
	 * @throws \InvalidArgumentException
	 * @throws \Modular\Exceptions\Exception
	 * @throws \OpenSemanticSearch\Exceptions\Exception
	 */
	public function addPage( $pageOrID ) {
		if ( $pageOrID && is_int( $pageOrID ) ) {
			$page = SiteTree::get()->byID( $pageOrID );
		} elseif ( $pageOrID instanceof SiteTree ) {
			$page = $pageOrID;
		} else {
			throw new Exception( "Don't know what to do with parameter 'pageOrID', it's not one of those" );
		}

		return $this->addURL( $page->AbsoluteLink() );
	}

	/**
	 * @param \Page|int $pageOrID
	 *
	 * @return \OpenSemanticSearch\Results\Result
	 * @throws \InvalidArgumentException
	 * @throws \Modular\Exceptions\Exception
	 * @throws \OpenSemanticSearch\Exceptions\Exception
	 */
	public function removePage( $pageOrID ) {
		if ( $pageOrID && is_int( $pageOrID ) ) {
			$page = SiteTree::get()->byID( $pageOrID );
		} elseif ( $pageOrID instanceof SiteTree ) {
			$page = $pageOrID;
		} else {
			throw new Exception( "Don't know what to do with parameter 'pageOrID', it's not one of those" );
		}

		return $this->request(
			self::ServiceOSS,
			self::EndpointRemove,
			[
				'uri' => $page->AbsoluteLink(),
			],
			[],
			[
				'uri' => false      // don't encode uri
			]
		);
	}

	/**
	 * Add a url to the index, no further checks are made e.g. to check the url is one from this site but it is indexed verbatim.
	 *
	 * @param string $absoluteURL including protocol, host etc
	 *
	 * @return bool
	 * @throws \OpenSemanticSearch\Exceptions\Exception
	 */
	public function addURL( $absoluteURL ) {
		return $this->request(
			self::ServiceOSS,
			self::EndpointIndexURL,
			[
				'uri' => $absoluteURL,
			],
			[],
			[],
			[
				'uri' => false      // don't encode uri
			]
		);
	}

	/**
	 * Remove an index entry by url
	 *
	 * @param string $absoluteURL including protocol, host etc
	 *
	 * @return mixed
	 * @throws \OpenSemanticSearch\Exceptions\Exception
	 */
	public function removeURL( $absoluteURL ) {
		return $this->request(
			self::ServiceOSS,
			self::EndpointRemove,
			[
				'uri' => $absoluteURL,
			],
			[],
			[
				'uri' => false      // don't encode uri
			]
		);
	}

	/**
	 * Given a raw response return something sensible given the encoding (e.g. json), headers and the body content.
	 *
	 * @param string $service  responding
	 * @param string $endpoint responding
	 * @param string $responseBody
	 * @param array  $responseHeaders
	 *
	 * @param string $responseCodeKey
	 *
	 * @return \OpenSemanticSearch\Results\Result
	 */
	protected function decodeResponse( $service, $endpoint = '', $responseBody, $responseHeaders = [], $responseCodeKey = 'ResponseCode' ) {
		$responseHeaders = $responseHeaders ?: $this->parseHTTPResponseHeaders( $http_response_header );
		$responseCode    = $responseCodeKey && isset( $responseHeaders[ $responseCodeKey ] )
			? $responseHeaders[ $responseCodeKey ]
			: '';

		if ( $responseCode && ! $this->responseCodeIsOK( $responseCode ) ) {
			$result = new ErrorResult($responseBody ?: "Got response $responseCode from service '$service', endpoint '$endpoint'");
		} else {
			// we got no response code in headers or it was OK, also decode from body
			$data = $this->decode( $responseBody );
			if ( $data === false ) {
				$result = new ErrorResult("No data");

			} else {
				$result = new OSSResult( $responseCode, $data );

			}
		}

		return $result;
	}

}