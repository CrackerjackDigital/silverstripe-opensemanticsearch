<?php

namespace OpenSemanticSearch\Models;

use Modular\Models\QueuedTask;
use OpenSemanticSearch\Exceptions\Exception;
use OpenSemanticSearch\Extensions\MetaDataExtension;
use OpenSemanticSearch\Interfaces\MetaDataInterface;
use OpenSemanticSearch\Services\MetaDataService;

/**
 * Scan through a file or files and update fields in the CMS with information from Solr, such as facets etc. Filters can be specified in
 * request as 'all' (default not needed), 'missing' (only files without MetaDate will be updated) or an int which is a specific file ID.
 *
 * @package OpenSemanticSearch
 */
class MetaDataTask extends QueuedTask {
	const QueueName = 'OpenSemanticSearch';

	const LimitParam = 'limit';
	const FilterParam = 'filter';

	// if filter param is this then all files will be indexed
	const FilterAll = 'all';
	// if no filter passed then only files with no retrieved date will be updated
	const FilterDefault = '';

	private static $singular_name = 'Information retrieval Task';
	private static $plural_name = 'Information retrieval Tasks';

	// process this many things at a time
	private static $batch_size = 5;

	/** @var  \OpenSemanticSearch\Interfaces\MetaDataInterface set by Injector */
	private $service;

	public function setService( MetaDataInterface $service ) {
		$this->service = $service;
	}

	/**
	 * @param array|\ArrayAccess $params
	 * @param string  $resultMessage
	 *
	 * @return mixed|void
	 * @throws \InvalidArgumentException
	 * @throws \Modular\Exceptions\Exception
	 */
	public function execute( $params = [], &$resultMessage = '' ) {
		set_time_limit( $this->timeout() );

		$files = \File::get();

		$resultMessage = '';

		$filter = isset($params[self::FilterParam])
			? $params[self::FilterParam]
			: self::FilterDefault;

		if ($filter == self::FilterDefault) {
			$files = $files->filter([
				MetaDataExtension::RetrievedDateField => '',
			]);
		} elseif ( is_int( $filter ) ) {
			// int = ID of file to get info for
			$files = $files->filter( [
				'ID' => $filter
			] );
		} else {
			$resultMessage = "Bad filter parameter '" . $filter . "'";
			$this->debug_fail( new Exception( $resultMessage ) );
		}

		$limit = array_key_exists(self::LimitParam, $params)
			? $params[self::LimitParam]
			: $this->config()->get('batch_size');

		if (is_int($limit)) {
			$files = $files->limit( $limit);
		}

		$service = MetaDataService::get();
		/** @var \File $file */
		foreach ( $files as $file ) {
			if ( $result = $service->findByID( $file) ) {

			}
		}
	}
}