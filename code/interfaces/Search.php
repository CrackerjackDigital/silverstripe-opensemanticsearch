<?php
namespace OpenSemanticSearch;

interface SearchInterface extends PathMappingInterface {
	const FilterYear        = 'year';
	const FilterContentType = 'type';

	/**
	 * @param string $localPath
	 *
	 * @return ResultInterface
	 */
	public function findPath( $localPath );

	/**
	 * @param Page|int
	 *
	 * @return ResultInterface
	 */
	public function findPage( $pageOrID );


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
	 * @return \OpenSemanticSearch\ResultInterface
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
			#	'type'    => '',                        // e.g. application/pdf, more specific than include if provided
			#	'view'     => self::ViewList,
			#	'operator' => self::OperatorOR,
			#	'stemming' => self::Stemming,
			#	'synonyms' => true,
		],
		$include = self::IncludeAll
	);
}