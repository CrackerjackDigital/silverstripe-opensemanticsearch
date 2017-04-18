<?php
namespace OpenSemanticSearch\Services;

use ArrayList;
use OpenSemanticSearch\Exceptions\SearchException;
use OpenSemanticSearch\Interfaces\SearchInterface;
use OpenSemanticSearch\Services\Service;
use SiteTree;

abstract class SolrSearcher extends Service implements SearchInterface {
	const ServiceSolr    = self::TypeSolr;
	const EndpointSearch = 'search';

	/**
	 * Find a specific indexed document by id (path)
	 *
	 * TODO sort out what to do if indexed https/http
	 *
	 * @param string $localPath
	 *
	 * @return mixed
	 */
	public function findFile( $localPath ) {
		return $this->findByID( self::TypeFile . $this->localToRemotePath( $localPath ) );
	}

	public function findPage( $pageOrID ) {
		if ( $pageOrID && is_int( $pageOrID ) ) {
			$page = SiteTree::get()->byID( $pageOrID );
		} elseif ( $pageOrID instanceof SiteTree ) {
			$page = $pageOrID;
		} else {
			throw new SearchException( "Don't know what to do with parameter 'pageOrID', it's not one of those" );
		}

		return $this->search( [ 'id' => $page->Link() ] );
	}

	public function findURL( $url ) {
		return $this->search( [ 'id' => $url ] );
	}


}