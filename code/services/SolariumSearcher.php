<?php

namespace OpenSemanticSearch\Services;

use OpenSemanticSearch\Traits\array_bitfield_map;
use OpenSemanticSearch\Traits\http;
use OpenSemanticSearch\Traits\json;
use OpenSemanticSearch\Traits\solarium;

/**
 * SolariumSearcher SearchInterface implementation which uses the Solarium library https://packagist.org/packages/solarium/solarium
 *
 * @package OpenSemanticSearch\Service
 */
class SolariumSearcher extends SolrSearcher {
	use array_bitfield_map;
	use json;
	use http;
	use solarium;

	const ServiceSolr    = self::TypeSolr;
	const EndpointRemove = 'delete';
	const EndpointSearch = 'search';

	/** @var array default fields to search for full text */
	private static $fulltext_fields = [ 'title', 'content' ];

	/** @ var int|null how many results per search, null = unlimited */
	private static $limit = null;


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
