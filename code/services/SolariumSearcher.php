<?php

namespace OpenSemanticSearch;

use Exception;
use SiteTree;
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

	/**
	 * @param array|string $fullText
	 * @param array        $fields
	 * @param array        $facets
	 * @param array        $filters
	 * @param array        $options
	 * @param int          $include
	 *
	 * @return SolariumResult
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
		$client = new Client( $options );

		$client->addEndpoint( $this->endpoint )
		       ->setDefaultEndpoint( $this->endpoint );

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
	 * @param string $type          e.g. 'client', 'query' etc maps to config
	 * @param array  $moduleOptions options
	 *
	 * @return mixed to be fed to native methods
	 */
	protected function nativeOptions( $type, array $moduleOptions ) {
		return $this->arr_to_btf( $moduleOptions, $this->option( $this->option( $type ), 'options' ) ?: [] );
	}

}
