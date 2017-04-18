<?php

namespace OpenSemanticSearch\Services;

use OpenSemanticSearch\Results\SolariumResult;
use OpenSemanticSearch\Traits\array_bitfield_map;
use OpenSemanticSearch\Traits\http;
use OpenSemanticSearch\Traits\json;
use Solarium\Client;
use Solarium\Core\Client\Endpoint;

/**
 * SolariumSearcher SearchInterface implementation which uses the Solarium library https://packagist.org/packages/solarium/solarium
 *
 * @package OpenSemanticSearch\Service
 */
class SolariumSearcher extends SolrSearcher {
	use array_bitfield_map;
	use json;
	use http;

	const ServiceSolr    = self::TypeSolr;
	const EndpointRemove = 'delete';
	const EndpointSearch = 'search';

	/** @var array default fields to search for full text */
	private static $fulltext_fields = [ 'title', 'content' ];

	/** @ var int|null how many results per search, null = unlimited */
	private static $limit = null;

	/** @var \Solarium\Core\Client\Endpoint */
	protected $endpoint;

	public function __construct( $env = '' ) {
		parent::__construct( $env );

		$uri = $this->uri( self::ServiceSolr, self::EndpointSearch );

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

	public function findByID($id, $options = [
		'resultclass' => SolariumResult::class,
	]) {
		$client = $this->createClient( $options);
		$query = $client->createSelect();

		$id = $query->getHelper()->escapeTerm( $id);

		$query->setQuery( "id:$id");

		return $client->select( $query);
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
		$client = $this->createClient($options);

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

	/**
	 * Creates a Solarium client configured for the correct endpoint for the environment we're running in.
	 * @param $options
	 *
	 * @return \Solarium\Core\Client\Client
	 * @throws \Solarium\Exception\InvalidArgumentException
	 * @throws \Solarium\Exception\OutOfBoundsException
	 */

	public function createClient($options) {
		return (new Client( $options ))
			->addEndpoint( $this->endpoint )
			->setDefaultEndpoint( $this->endpoint );
	}

	/**
	 * @param string $type          e.g. 'client', 'query' etc maps to config
	 * @param array  $moduleOptions options
	 *
	 * @return mixed to be fed to native methods
	 */
	protected function nativeOptions( $type, array $moduleOptions ) {
		return $this->arr_to_btf( $moduleOptions, $this->option( $this->option( $type ), 'options' ) ?: [] );
	}

}
