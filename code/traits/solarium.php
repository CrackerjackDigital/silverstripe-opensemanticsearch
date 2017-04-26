<?php

namespace OpenSemanticSearch\Traits;

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
	 * @throws \Solarium\Exception\InvalidArgumentException
	 * @throws \Solarium\Exception\OutOfBoundsException
	 */
	public function find(
		$item,
		$options = [
			'resultclass' => SolariumResult::class,
		]
	) {
		$client = $this->createClient( $options );

		$query = $client->createRealtimeGet();

		$id = $query->getHelper()->escapeTerm( $item->OSSID() );

		$query->addId( $id );

		$result = $client->realtimeGet( $query );

		if ( $this->responseIsOK( $result ) ) {
			$response = new SolariumResult( $result );
		} else {
			$response = new ErrorResult();
		}

		return $response;
	}

	/**
	 * @param $result
	 *
	 * @return bool
	 */
	public function responseIsOK( $result ) {
		$result = $this->decode( $result );

		return $result && isset($result['resultcode']);
	}

	/**
	 * @param array|string $fullText
	 * @param array        $fields
	 * @param array        $filters
	 * @param array        $facets
	 * @param array        $options
	 * @param int          $include
	 *
	 * @return \OpenSemanticSearch\Results\SolariumResult
	 * @throws \Solarium\Exception\InvalidArgumentException
	 * @throws \Solarium\Exception\OutOfBoundsException
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
			'resultclass' => SolariumResult::class,
			//			'sort'     => [ self::SortRelevance ],
		],
		$include = self::IncludeAll
	) {
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

		/** @var SolariumResult $result */
		return $client->select( $query );
	}

	public function endpoint( $uri = null ) {
		$uri = $this->uri( self::ServiceSolr, self::EndpointSearch );

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
	 * @param string $uri we want to communicate with
	 * @param mixed  $options
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