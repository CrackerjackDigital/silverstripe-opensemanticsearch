<?php

namespace OpenSemanticSearch\Services;

use OpenSemanticSearch\Exceptions\Exception;
use OpenSemanticSearch\Interfaces\OSSID;
use OpenSemanticSearch\Interfaces\SearchInterface;
use OpenSemanticSearch\Results\ErrorResult;
use OpenSemanticSearch\Results\SolariumResult;
use OpenSemanticSearch\Traits\array_bitfield_map;
use OpenSemanticSearch\Traits\http;
use OpenSemanticSearch\Traits\json;
use OpenSemanticSearch\Traits\map_key_transform;
use Solarium\Core\Client\Client;
use Solarium\Core\Client\Endpoint;

/**
 * SolariumSearcher SearchInterface implementation which uses the Solarium library https://packagist.org/packages/solarium/solarium
 *
 * @package OpenSemanticSearch\Service
 */
class SolariumSearcher extends SolrSearcher {
	use array_bitfield_map;
	use map_key_transform;
	use json;
	use http;

	const ServiceSolr    = self::TypeSolr;
	const EndpointRemove = 'delete';
	const EndpointSearch = 'search';

	/** @var array default fields to search for full text */
	private static $fulltext_fields = [ 'title', 'content' ];

	/** @ var int|null how many results per search, null = unlimited */
	private static $limit = null;

	/** @var  Endpoint */
	protected $endpoint;

	/**
	 * Return a list of models based on a provided item,
	 *
	 * @param OSSID $item           e.g. the remote file path should not be escaped yet. This shouldn't be url encoded, but should have 'file://' scheme
	 *                              prefixed.
	 * @param bool  $updateMetaData from the search result if true, doesn't write the model.
	 * @param array $options
	 *
	 * @return \DataObject
	 * @throws \OpenSemanticSearch\Exceptions\Exception
	 */
	public function find(
		$item,
		$updateMetaData = true,
		$options = []
	) {
		try {
			$options = $this->searchOptions( $options );

			$client = $this->createClient( $options );

			$query = $client->createRealtimeGet();

			$remotePath = $this->localToRemotePath( $item->OSSID() );

			$id = $query->getHelper()->escapeTerm( $remotePath );

			$query->addId( $id );

			$result = $client->realtimeGet( $query );

			if ( $this->responseIsOK( $result ) ) {
				$response = new SolariumResult(
					$result->getResponse()->getStatusCode(),
					$result,
					$result->getResponse()->getStatusMessage()
				);
			} else {
				$response = new ErrorResult(
					$result->getResponse()->getStatusCode(),
					$result->getData(),
					$result->getResponse()->getStatusMessage()
				);
			}

			return $response->models( $updateMetaData )->first();

		} catch ( \Exception $e ) {
			throw new Exception( $e->getMessage(), $e->getCode(), $e );
		}

	}

	/**
	 * @param array|string $fullText
	 * @param array        $fields
	 * @param array        $filters
	 * @param array        $facets
	 * @param array        $options
	 * @param int          $include
	 *
	 * @return \OpenSemanticSearch\Results\ErrorResult|\OpenSemanticSearch\Results\SolariumResult
	 * @throws \OpenSemanticSearch\Exceptions\Exception
	 */
	public function search(
		$fullText,
		$fields = [
			'title',
			'content',
		],
		$filters = [
			self::FilterYear        => '',            // e.g. 2017
			self::FilterContentType => '',     // e.g. application/pdf
		],
		$facets = [],
		$options = [
			'view'     => self::ViewList,
			'stemming' => self::Stemming,
			'operator' => self::OperatorOR,
			'synonyms' => 1,
			'start'    => 0,
			'limit'    => self::DefaultLimit,                    // null means use configured default
			// 'resultclass' => SolariumResult::class,
			// 'sort'     => [ self::SortRelevance ],
		],
		$include = self::IncludeAll
	) {
		try {
			$this->debugger()->trace( "Searching for '$fullText'" );

			$options = array_merge(
				$options,
				$this->searchOptions
			);

			$client = $this->createClient( $options );

			$query = $client->createSelect( $options );

			if ( $fullText ) {
				$query->setQuery( $fullText );
//				$query->setFields( $fields );
			}

			/** @var \Solarium\QueryType\Select\Result\Result $result */
			$result = $client->select( $query );
			$this->debugger()->trace( "result from select: " . print_r( $result, true ) );

			if ( $this->responseIsOK( $result ) ) {
				$response = new SolariumResult( null, $result );
			} else {
				$response = new ErrorResult(
					$result->getResponse()->getStatusCode(),
					$result->getData(),
					$result->getResponse()->getStatusMessage()
				);
			}

			return $response;

		} catch ( \Exception $e ) {
			$this->debugger()->error( $e->getMessage() );

			throw new Exception( $e->getMessage(), $e->getCode(), $e );
		}
	}

	/**
	 * Returns options massaged for solarium, e.g. 'limit' becomes 'rows'.
	 *
	 * @param null $options
	 *
	 * @return SearchInterface|array
	 */
	public function searchOptions( $options = null ) {
		if (func_num_args()) {
			parent::searchOptions( $options );
			$this->searchOptions = $this->map_key_transform( $this->searchOptions(), [ 'limit' => 'rows' ], false);
			return $this;
		} else {
			return $this->map_key_transform( parent::searchOptions(), [ 'limit' => 'rows' ]);
		}
	}

	/**
	 * @param \Solarium\Core\Query\Result\Result $result
	 *
	 * @return bool
	 */
	public function responseIsOK( $result ) {
		return fnmatch( '2*', $result->getResponse()->getStatusCode() );
	}

	/**
	 * Initialise or replace the endpoint this client uses.
	 *
	 * @param string $uri if not supplied then the uri for the Solr Search config option will be used.
	 *
	 * @return \Solarium\Core\Client\Endpoint
	 * @throws \OpenSemanticSearch\Exceptions\Exception
	 */
	public function endpoint( $uri = null ) {
		$uri = $uri ?: $this->uri( self::ServiceSolr, self::EndpointSearch );

		if ( func_num_args() || ! $this->endpoint ) {
			$endpointInit   = array_merge(
				parse_url( $uri ),
				[
					'timeout' => $this->option( $this->option( 'service' ), 'timeout' ),
					'core'    => $this->core(),
					'key'     => md5( $uri ),
				]
			);
			$this->endpoint = new Endpoint( $endpointInit );
		}

		return $this->endpoint;
	}

	/**
	 * Creates a Solarium client configured for the correct endpoint for the environment we're running in.
	 *
	 * @param mixed $options
	 *
	 * @return \Solarium\Core\Client\Client
	 * @throws \OpenSemanticSearch\Exceptions\Exception
	 * @throws \Solarium\Exception\InvalidArgumentException
	 * @throws \Solarium\Exception\OutOfBoundsException
	 */

	public function createClient( $options ) {
		return ( new Client( $options ) )
			->addEndpoint( $this->endpoint() )
			->setDefaultEndpoint( $this->endpoint() );
	}


}
