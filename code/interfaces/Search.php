<?php

namespace OpenSemanticSearch\Interfaces;

use OpenSemanticSearch\Models\IndexedURL;

interface SearchInterface extends PathMappingInterface, ServiceInterface {
	const ServiceName       = 'SearchService';
	const FilterYear        = 'year';
	const FilterContentType = 'type';

	public function setResultResponseClass( $className );

	public function setErrorResponseClass( $className );

	/**
	 * Return a single model by it's OSS ID
	 *
	 * @param OSSID|int|string $modelOrIDOrPath
	 * @param bool             $updateMetaData from the search result if true, doesn't write the model.
	 *
	 * @return \DataObject
	 */
	public function find( $modelOrIDOrPath, $updateMetaData = true );

	/**
	 * Returns a single match given a path, or null if not match found
	 *
	 * @param string $localPath
	 *
	 * @return \File
	 */
	public function findFile( $localPath );

	/**
	 * Return a single Page if found
	 *
	 * @param Page|int
	 *
	 * @return \Page
	 */
	public function findPage( $pageOrID );

	/**
	 * Return a single match by URL
	 *
	 * @param $url
	 *
	 * @return IndexedURL|OSSID
	 */
	public function findURL( $url );

	/**
	 * @param string|array $fullText if string used to find content,
	 *                               if numerically keyed array then entries will be used joined by options.operator
	 *                               if alpha keyed map then the keys will be used as fields (fields parameter will be ignored)
	 * @param array        $fields   if no fields provided as keys in fulltext then these fields will be used to search for fulltext
	 * @param array        $filters  such as facets, document types
	 * @param array        $facets   facet these fields
	 * @param array        $options  generic representation of service specific options
	 * @param int          $include  what gets included in results
	 *
	 * @return \OpenSemanticSearch\Interfaces\ResultInterface
	 */
	public function search(
		$fullText,      # 'fred' or [ 'fred', 'dagg' ] or [ 'title' => 'fred', 'content' => 'dagg' ]
		$fields = [
			#	'title',
			#	'content'
		],
		$filters = [
			#	'authors' => [],            // e.g. [ 'John Smith', 'Fred Dagg' ] or 'John Smith'
			#	'year'    => '',            // e.g. 2017
		],
		$facets = [
			#   'year'
		],
		$options = [
			#	'start'    => 0,
			#	'sort'     => self::SortRelevance,
			#	'type'     => '',                        // e.g. application/pdf, more specific than include if provided
			#   'limit'    => self::DefaultLimit,
			#	'view'     => self::ViewList,
			#	'operator' => self::OperatorOR,
			#	'stemming' => self::Stemming,
			#	'synonyms' => true,
		],
		$include = self::IncludeAll
	);

	/**
	 * Sets/gets options as would be set by call to search, but seperately.
	 * @param mixed $options if supplied sets, otherwise gets
	 *
	 * @return $this|mixed
	 * @fluent-setter
	 */
	public function searchOptions($options = null);
}