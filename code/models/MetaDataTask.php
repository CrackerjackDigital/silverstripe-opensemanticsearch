<?php

namespace OpenSemanticSearch\Models;

use ArrayList;
use InvalidArgumentException;
use Modular\Models\QueuedTask;
use OpenSemanticSearch\Exceptions\Exception;
use OpenSemanticSearch\Extensions\MetaDataExtension;
use OpenSemanticSearch\Fields\IndexedItem;
use OpenSemanticSearch\Fields\SourceQuery;
use OpenSemanticSearch\Interfaces\MetaDataInterface;
use SS_List;

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
	 * @return int count of items found and updated
	 * @throws InvalidArgumentException
	 * @throws Exception
	 */
	public function execute( $params = [], &$resultMessage = '' ) {
		$items = new ArrayList( [] );
		if ( $query = $this->{SourceQuery::Name} ) {

			$items = $this->service->search( $query )->models( true );

		} elseif ( $item = $this->{IndexedItem::relationship_name()}() ) {

			$items = [ $this->service->find( $item, true ) ];

		}
		if ($retrievedBeforeDate = $this->{MetaDataExtension::RetrievedDateField}) {
			$items = $items->filter([
				MetaDataExtension::RetrievedDateField . ':LessThan' => $retrievedBeforeDate
			]);
		}
		// now write items as the search/find may update their meta data but
		// doesn't save them.
		foreach ( $items as $item ) {
			$item->write();
		}

		return count( $items );
	}

}