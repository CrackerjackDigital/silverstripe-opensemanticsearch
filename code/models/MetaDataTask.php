<?php

namespace OpenSemanticSearch\Models;

use Modular\Models\QueuedServiceTask;
use Modular\Models\QueuedTask;
use OpenSemanticSearch\Exceptions\Exception;
use OpenSemanticSearch\Extensions\MetaDataExtension;
use OpenSemanticSearch\Fields\IndexedItem;
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

	private static $singular_name = 'Search MetaData retrieval Task';
	private static $plural_name = 'Search MetaData retrieval Tasks';

	/** @var  \OpenSemanticSearch\Interfaces\MetaDataInterface set by Injector */
	private $service;

	public function setService( MetaDataInterface $service ) {
		$this->service = $service;
	}

	/**
	 * Look for files which haven't had their meta data updated by checking the MetaDataRetrievedDate field
	 * and get the MetaData from the search index, updating the file object fields added by the MetaDataExtension.
	 *
	 * @param array|\ArrayAccess $params
	 * @param string             $resultMessage
	 *
	 * @return bool true if executed succesfully, false otherwise (and should have set $resultMessage with reason)
	 * @throws \InvalidArgumentException
	 * @throws \Modular\Exceptions\Exception
	 */
	public function execute( $params = [], &$resultMessage = '' ) {
		if ($item = $this->{IndexedItem::relationship_name()}()) {
			return count($this->service->populateMetaData( $item));
		}
		return false;
	}


}