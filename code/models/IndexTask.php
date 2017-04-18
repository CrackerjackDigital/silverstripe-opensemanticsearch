<?php

namespace OpenSemanticSearch\Models;

use Modular\Models\QueuedTask;
use OpenSemanticSearch\Exceptions\Exception;
use OpenSemanticSearch\Fields\IndexAction;
use OpenSemanticSearch\Fields\IndexedItem;
use OpenSemanticSearch\Services\IndexService;
use OpenSemanticSearch\Services\OSSIndexer;

/**
 * Task which adds, removes etc items from the OSS service
 *
 * @package OpenSemanticSearch
 */
class IndexTask extends QueuedTask {
	const QueueName = 'OpenSemanticSearch';

	private static $singular_name = 'Index Task';
	private static $plural_name = 'Index Tasks';

	private static $summary_fields = [
		'QueueName'       => 'Queue',
		'Title'           => 'Title',
		'QueuedState'     => 'Status',
		'QueuedDate'      => 'Queued Date',
		'Outcome'         => 'Outcome',
		'EventDate'       => 'To Run Date',
		'EndDate'         => 'Completed Date',
		'IndexedItemType' => 'Type',
	];

	private static $default_sort = 'ID desc';

	/** @var  \OpenSemanticSearch\Interfaces\IndexInterface|OSSIndexer set by Injector */
	private $service;

	public function setService( IndexService $service ) {
		$this->service = $service;
	}

	/**
	 * Perform the IndexAction on the item.
	 *
	 * @param array|\ArrayAccess $params
	 * @param string             $resultMessage
	 *
	 * @return mixed|void
	 * @throws \ValidationException
	 */
	public function execute( $params = [], &$resultMessage = '' ) {
		$this->trackable_start( __FUNCTION__, "Processing task '$this->Title'" );

		set_time_limit( $this->timeout() );

		$res = false;

		$resultMessage = '';

		$this->debugger()->set_error_exception();
		try {
			$this->markRunning();

			$action = $this()->{IndexAction::Name};
			$item   = $this()->{IndexedItem::relationship_name()}();

			switch ( $action ) {
				case IndexAction::Add:
					$this->service->add( $item, $resultMessage );
					break;
				case IndexAction::Remove:
					$this->service->remove( $item, $resultMessage );
					break;
				case IndexAction::ReIndex:
					$this->service->reindex( $item, $resultMessage );
					break;
				default:
					throw new Exception( "Unknown IndexTask action '$action'" );
			}

			$resultMessage = 'OK';
		} catch ( \Exception $e ) {
			$res           = false;
			$resultMessage = $e->getMessage();
		}

		if ( $res ) {
			$this->success( $resultMessage );

		} else {
			$this->fail( $resultMessage );
		}
		$resultMessage = "task ended with: '$resultMessage'";
		$this->trackable_end( $resultMessage );
	}

	public function IndexedItemType() {
		if ( $item = $this()->{IndexedItem::relationship_name()}() ) {
			$type = $item->i18n_singular_name();
		} elseif ( $this()->{IndexedItem::field_name()} ) {
			$type = 'Unknown';
		} else {
			$type = 'None';
		}

		return $type;
	}

	public function onBeforeWrite() {
		parent::onBeforeWrite();
		if ( ! $this->Title ) {
			if ($indexedItem = $this->{IndexedItem::relationship_name()}()) {
				$title = $indexedItem->Title;
			} else {
				$title = "Index Task";
			}
			$action = $this->{IndexAction::Name};

			$this->Title = _t(
				"OpenSemanticSearch.IndexAction.$action",
				"$action '{title}'",
				[ 'title' => $title ]
			);
		}
	}
}
