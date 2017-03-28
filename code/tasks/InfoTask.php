<?php

namespace OpenSemanticSearch;

use Modular\Fields\File as FileField;
use Modular\Tasks\QueuedTask;

/**
 * Scan through a file or files and update fields in the CMS with information from Solr, such as facets etc. Filters can be specified in
 * request as 'all' (default not needed), 'missing' (only files without MetaDate will be updated) or an int which is a specific file ID.
 *
 * @package OpenSemanticSearch
 */
class InfoTask extends QueuedTask {
	const QueueName = 'OpenSemanticSearch';

	const LimitParam = 'limit';
	const FilterParam = 'filter';

	// if filter param is this then all files will be indexed
	const FilterAll = 'all';
	// if passed only files with no OSSInfoDate will be looked up
	const FilterMissing = 'missing';

	private static $singular_name = 'Information retrieval Task';
	private static $plural_name = 'Information retrieval Tasks';

	/**
	 * @param null $params
	 *
	 * @return mixed|void
	 * @throws \InvalidArgumentException
	 * @throws \Modular\Exceptions\Exception
	 */
	public function execute( $params = null ) {
		set_time_limit( $this->timeout() );

		$files = \File::get();

		if ( isset( $params[ self::FilterParam ] ) ) {
			$filter = $params[self::FilterParam];

			if ($filter == self::FilterMissing) {
				// only files with no OSSInfoDate set
				$files = $files->filter( [
					MetaDataExtension::InfoDateField => '',
				] );
			} elseif (is_int( $filter)) {
				// int = ID of file to get info for
				$files = $files->filter([
					'ID' => $filter
				]);
			} else {
				$this->debug_fail( new Exception( "Bad filter parameter '" . $filter . "'") );
			}
		} else {
			$this->debug_fail( new Exception("No filter parameter, this needs to be supplied"));
		}
		if (isset($params[self::LimitParam])) {
			$limit = $params[self::LimitParam];

			if (is_int($limit)) {
				$files = $files->limit( $limit);
			}
		}

		$service = SolrSearcher::get();
		/** @var \File $file */
		foreach ( $files as $file ) {
			if ( $result = $service->findPath( $file->Link() ) ) {

			}
		}
	}
}