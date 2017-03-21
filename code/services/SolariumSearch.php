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
class SolariumSearch extends Service implements SearchInterface {
	use array_bitfield_map;
	use json;

	const ServiceSolr    = self::TypeSolr;
	const EndpointRemove = 'delete';
	const EndpointSearch = 'search';

	// configure url paths to services depending on environment
	private static $endpoints = [
		'dev' => [
			self::ServiceSolr => [
				self::EndpointSearch => 'http://localhost:9011/solr',
			],
		],
		'*'   => [
			self::ServiceSolr => [
				self::EndpointSearch => 'http://searcher:8011/solr',
			],
		],
	];

	/** @var array default fields to search for full text */
	private static $fulltext_fields = [ 'title', 'content' ];

	/** @ var int|null how many results per search, null = unlimited */
	private static $limit = null;

	/** @var \Solarium\Core\Client\Endpoint */
	protected $endpoint;

	public function __construct( $env = '' ) {
		parent::__construct( $env );

		$endpointInit   = array_merge(
			parse_url( $this->uri( self::ServiceSolr, self::EndpointSearch ), PHP_URL_SCHEME | PHP_URL_USER | PHP_URL_PASS | PHP_URL_HOST | PHP_URL_PORT | PHP_URL_PATH ),
			[
				'timeout' => $this->setting( 'timeout', $this->setting( 'service' ) ),
				'core'    => $this->core(),
			]
		);
		$this->endpoint = new Endpoint( $endpointInit );
	}

	/**
	 * Find a specific indexed document by id (path)
	 *
	 * TODO sort out what to do if indexed https/http
	 *
	 * @param string $localPath
	 *
	 * @return mixed
	 */
	public function findPath( $localPath ) {
		return $this->search( [ 'id' => self::TypeFile . $this->localToRemotePath( $localPath ) ] );
	}

	public function findPage( $pageOrID ) {
		if ( $pageOrID && is_int( $pageOrID ) ) {
			$page = SiteTree::get()->byID( $pageOrID );
		} elseif ( $pageOrID instanceof SiteTree ) {
			$page = $pageOrID;
		} else {
			throw new Exception( "Don't know what to do with parameter 'pageOrID', it's not one of those" );
		}

		return $this->search( [ 'id' => $page->Link() ] );
	}

	public function findURL( $url ) {
		return $this->search( [ 'id' => $url ] );
	}

	/**
	 * @param array|string $fullText
	 * @param array        $fields
	 * @param array        $facets
	 * @param array        $filters
	 * @param array        $options
	 * @param int          $include
	 *
	 * @return \ArrayList
	 */
	public function search(
		$fullText,
		$fields = [
			'title',
			'content',
		],
		$filters = [
			'year' => self::FilterYear,            // e.g. 2017
			'type' => self::FilterContentType,     // e.g. application/pdf
		],
		$facets = [],
		$options = [
			'view'     => self::ViewList,
			'start'    => 0,
			'limit'    => null,                    // null means use configured default
			'stemming' => self::Stemming,
			'operator' => self::OperatorOR,
			'synonyms' => 1,
			'sort'     => self::SortRelevance,
		],
		$include = self::IncludeFiles | self::IncludeLocalPages
	) {
		$client = new Client( [
			'resultclass' => SolariumResult::class,
		] );
		$client->addEndpoint( $this->endpoint );

		$query = $client->createSelect( $this->nativeOptions( $options ) );

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
			if ( $filter == 'year' ) {
				$query->addParam( 'zoom', 'year' );
				$query->addParam( 'year', $value );
				$query->addParam( 'start_dt', $value );
				$query->addParam( 'end_dt', $value );
			}
			if ( $filter == 'type' ) {

			}
		}
		/** @var SolariumResult $result */
		$result = $client->select( $query );

		return $this->models( $result );
	}

	/**
	 * @param string $type          e.g. 'client', 'query' etc maps to config
	 * @param array  $moduleOptions options
	 *
	 * @return mixed to be fed to native methods
	 */
	protected function nativeOptions( $type, array $moduleOptions ) {
		return $this->arr_to_btf( $moduleOptions, $this->setting( $this->setting( $type ), 'options' ) ?: [] );
	}

	/**
	 * Turn a decoded response from e.g. 'search' into SilverStripe models in a list, e.g of File and Page models.
	 *
	 * @param \OpenSemanticSearch\ResultInterface $response e.g. a SolrJSONResponse
	 * @param int                                 $include
	 *
	 * @return \ArrayList
	 */
	protected function models( ResultInterface $response, $include = self::IncludeAll ) {
		$models = new \ArrayList();

		if ( $response->hasItems() ) {
			$items = $response->items();

			$files = [];

			foreach ( $items as $item ) {
				$id     = $item['id'];
				$scheme = parse_url( $id, PHP_URL_SCHEME );

				if ( $scheme == 'file' ) {
					if ( ( $include & self::IncludeFiles ) === self::IncludeFiles ) {
						if ( $filePathName = $this->remoteToLocalPath( $id ) ) {
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
					if ( ( $include & self::IncludeFiles ) === self::IncludeFiles ) {
						if ( $filePathName = $this->remoteToLocalPath( $id ) ) {
							if ( $file = $files->find( 'Filename', $filePathName ) ) {
								$models->push( $file );
							}
						}
					}
				} else if ( $scheme == 'http' || $scheme == 'https' ) {
					$path = parse_url( $id, PHP_URL_PATH );

					if ( \Director::is_site_url( $id ) ) {
						if ( ( $include & self::IncludeLocalPages ) === self::IncludeLocalPages ) {
							if ( $page = \Page::get_by_link( $path ) ) {
								$models->push( $page );
							}
						}
					} else if ( ( $include & self::IncludeRemoteURLs ) === self::IncludeRemoteURLs ) {
						$models->push( new Link( [ 'Link' => $path ] ) );
					}
				}
			}
		}

		return $models;
	}

}
