<?php
namespace OpenSemanticSearch;

use Modular\Exceptions\Exception;
use Modular\Exceptions\NotImplemented;
use SiteTree;

/**
 * Service represents an Open Semantic Search service which consists of two parts, the OSS provided service which adds, removes and updates
 * files and urls to the index, and the backend indexing service which is Solr.
 *
 * NB Only the SolrGet service is working as of 2017/03/05
 *
 * @package OpenSemanticSearch
 */
class OSSIndexer extends RestfulService implements IndexInterface {
	const ServiceOSS = self::TypeOSS;

	const EndpointIndexDir  = 'index-dir';
	const EndpointIndexFile = 'index-file';
	const EndpointIndexURL  = 'index-web';
	const EndpointRemove    = 'delete';

	/**
	 * Makes a request and checks it's validity according to it's type. Returns a response corresponding to the type (e.g. SolrJSONResponse). Returns an
	 *
	 * @param       $service
	 * @param       $endpoint
	 * @param array $params
	 * @param null  $data
	 * @param array $tokens
	 *
	 * @return ResultInterface could be an ErrorResponse or e.g. SolrJSONResponse
	 */
	public function request( $service, $endpoint, $params = [], $data = null, $tokens = [] ) {
		// parent::request will call request in http extension
		if ( false !== ( $decoded = parent::request( $service, $endpoint, $params, $data, $tokens ) ) ) {
			$response = OSSResult::create( $decoded );
		} else {
			$response = ErrorResult::create( "Request returned no valid data", print_r( $decoded, true ) );
		}

		return $response;
	}

	/**
	 * @param string $localPath relative to assets folder or absolute from wb root root of file to add to index.
	 *
	 * @return bool
	 * @api
	 */
	public function addFile( $localPath ) {
		if ( ! $this->relativePath( $localPath ) ) {
			// doesn't exist in file system or not in a safe place
			return false;
		}

		return $this->request(
			self::ServiceOSS,
			self::EndpointIndexFile,
			[
				'uri' => $this->localToRemotePath( $localPath ),
			]
		)->isOK();
	}

	/**
	 * @param string $localPath relative to assets folder or absolute from wb root root of file to add to index.
	 *
	 * @return bool
	 * @api
	 */
	public function addDirectory( $localPath ) {
		if ( ! $this->relativePath( $localPath ) ) {
			// doesn't exist in file system or not in a safe place
			return false;
		}

		return $this->request(
			self::ServiceOSS,
			self::EndpointIndexDir,
			[
				'uri' => $this->localToRemotePath( $localPath ),
			]
		)->isOK();
	}

	/**
	 * @param int|\Page $pageOrID
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function addPage( $pageOrID ) {
		if ( $pageOrID && is_int( $pageOrID ) ) {
			$page = SiteTree::get()->byID( $pageOrID );
		} elseif ( $pageOrID instanceof SiteTree ) {
			$page = $pageOrID;
		} else {
			throw new Exception( "Don't know what to do with parameter 'pageOrID', it's not one of those" );
		}

		return $this->addURL( $page->Link() );
	}

	/**
	 * Add a url to the index, no further checks are made e.g. to check the url is one from this site but it is indexed verbatim.
	 *
	 * @param $url
	 *
	 * @return bool
	 */
	public function addURL( $url ) {
		return $this->request(
			self::ServiceOSS,
			self::EndpointIndexURL,
			[
				'uri' => $url,
			]
		)->isOK();
	}

	/**
	 * Removes a file from index.
	 *
	 * @param string $localPath relative to assets folder or absolute from web root of file to add to index.
	 *
	 * @return bool
	 * @api
	 */
	public function removeFile( $localPath ) {
		return $this->request(
			self::ServiceOSS,
			self::EndpointRemove,
			[
				'uri' => $this->localToRemotePath( $localPath ),
			]
		)->isOK();
	}

	/**
	 * Removes a file or directory from index.
	 *
	 * @param string $localPath relative to assets folder or absolute from web root of file to add to index.
	 *
	 * @return bool
	 * @api
	 */
	public function removePath( $localPath ) {
		return $this->request(
			self::ServiceOSS,
			self::EndpointRemove,
			[
				'uri' => $this->localToRemotePath( $localPath ),
			]
		)->isOK();
	}

	/**
	 * @param \Page|int $pageOrID
	 *
	 * @return bool
	 * @throws \Modular\Exceptions\Exception
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
				'uri' => $page->Link()
			]
		)->isOK();
	}

	/**
	 * @param string $url
	 *
	 * @return mixed
	 */
	public function removeURL( $url ) {
		return $this->request(
			self::ServiceOSS,
			self::EndpointRemove,
			[
				'uri' => $url
			]
		)->isOK();
	}


}