<?php

namespace OpenSemanticSearch\Traits;

use OpenSemanticSearch\Exceptions\Exception;
use OpenSemanticSearch\Interfaces\OSSID;
use OpenSemanticSearch\Results\ErrorResult;
use OpenSemanticSearch\Results\SolariumResult;
use Solarium\Core\Client\Client;
use Solarium\Core\Client\Endpoint;

/**
 * solr provides encoding/decoding to and from solr.
 *
 * @package OpenSemanticSearch\Traits
 */
trait solarium {
	/** @var  Endpoint */
	protected $endpoint;

	/**
	 * @param OSSID $item e.g. the remote file path should not be escaped yet. This shouldn't be url encoded, but should have 'file://' scheme prefixed.
	 * @param array $options
	 *
	 * @return \OpenSemanticSearch\Results\Result
	 * @throws \OpenSemanticSearch\Exceptions\Exception
	 */
	public function find(
		$item,
		$options = [
			'resultclass' => SolariumResult::class,
		]
	) {
		try {
			$client = $this->createClient( $options );

			$query = $client->createRealtimeGet();

			$localPath = $this->localToRemotePath( $item->OSSID() );

			$id = $query->getHelper()->escapeTerm( $localPath );

			$query->addId( $id );

			$result = $client->realtimeGet( $query );

			if ( $this->responseIsOK( $result ) ) {
				$response = new SolariumResult( null, $result );
			} else {
				$response = new ErrorResult( $result->getResponse()->getStatusCode(), $result->getData(), $result->getResponse()->getStatusMessage() );
			}

			return $response;

		} catch ( \Exception $e ) {
			throw new Exception( $e->getMessage(), $e->getCode(), $e );
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
			'view'        => self::ViewList,
			'start'       => 0,
			'limit'       => null,                    // null means use configured default
			'stemming'    => self::Stemming,
			'operator'    => self::OperatorOR,
			'synonyms'    => 1,
			// 'resultclass' => SolariumResult::class,
			//  'sort'     => [ self::SortRelevance ],
		],
		$include = self::IncludeAll
	) {
		try {
			$client = $this->createClient( $options );

			$query = $client->createSelect( $options );

			if ( $fullText ) {
				$query->setQuery( $fullText );
			}

			$facets = array_filter( $facets );
			if ( $facets ) {
				$facetSet = $query->getFacetSet();
				foreach ( $facets as $name => $value ) {
					$facetSet->createFacetField( $name )->setField( $value );
				}
			}
			foreach ( array_filter( $filters ) as $filter => $value ) {
				if ( $value ) {
					if ( $filter == 'year' ) {
						$query->addParam( 'zoom', 'year' );
						$query->addParam( 'year', $value );
						$query->addParam( 'start_dt', $value );
						$query->addParam( 'end_dt', $value );
					}
					if ( $filter == 'type' ) {

					}
				}
			}

			/** @var \Solarium\QueryType\Select\Result\Result $result */
			$result = $client->select( $query );
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
			throw new Exception( $e->getMessage(), $e->getCode(), $e );
		}
	}

	/**
	 * Initialise or replace the endpoint this client uses.
	 *
	 * @param string $uri if not supplied then the uri for the Solr Search config option will be used.
	 *
	 * @return \Solarium\Core\Client\Endpoint
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
	 * @throws \Solarium\Exception\InvalidArgumentException
	 * @throws \Solarium\Exception\OutOfBoundsException
	 */

	public function createClient( $options ) {
		return ( new Client( $options ) )
			->addEndpoint( $this->endpoint() )
			->setDefaultEndpoint( $this->endpoint() );
	}

}