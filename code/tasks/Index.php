<?php

namespace OpenSemanticSearch\Tasks;

use Modular\Models\QueuedTask;
use OpenSemanticSearch\Exceptions\Exception;
use OpenSemanticSearch\Fields\IndexAction;
use OpenSemanticSearch\Fields\IndexedItem;
use OpenSemanticSearch\Interfaces\IndexInterface;
use OpenSemanticSearch\Services\Index as Service;

/**
 * Task which adds, removes etc items from the OSS service
 *
 * @package OpenSemanticSearch
 */
class Index extends QueuedTask {
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

	/**
	 * Check for duplicates via the IndexedItemID not the Title.
	 *
	 * @var array
	 */
	private static $unique_fields = [
		\Modular\Fields\Title::Name                  => false,
		\OpenSemanticSearch\Fields\IndexedItem::Name => true,
		\OpenSemanticSearch\Fields\SourceQuery::Name => true,
		\OpenSemanticSearch\Fields\IndexAction::Name => true,
	];

	/** @var  IndexInterface|Service set by Injector */
	private $service;

	public function setService( IndexInterface $service ) {
		$this->service = $service;
	}

	/**
	 * Perform the IndexAction on the item.
	 *
	 * @param array|\ArrayAccess $params
	 * @param string             $resultMessage
	 *
	 * @return bool
	 * @throws \Exception
	 * @throws \ValidationException
	 */
	public function execute( $params = [], &$resultMessage = '' ) {
		$this->trackable_start( __FUNCTION__, "Processing task '$this->Title'" );
		$this->markRunning();

		$this->timeout();

		$res = false;

		$resultMessage = '';

		$this->debugger()->set_error_exception( $resultMessage );
		try {

			$action = $this()->{IndexAction::Name};
			$item   = $this()->{IndexedItem::relationship_name()}();

			switch ( $action ) {
				case IndexAction::Add:
					$res = $this->service->add( $item, $resultMessage );
					break;
				case IndexAction::Remove:
					$res = $this->service->remove( $item, $resultMessage );
					break;
				case IndexAction::ReIndex:
					$res = $this->service->reindex( $item, $resultMessage );
					break;
				default:
					throw new Exception( "Unknown Index action '$action'" );
			}
		} catch ( \Exception $e ) {
			$res           = false;
			$resultMessage = $e->getMessage();
		}

		if ( $res ) {
			// will mark as complete
			$this->markSuccessful( $resultMessage );

		} else {
			// will mark as complete, message could still be 'OK' if failure not a bad thing
			$this->markFailed( $resultMessage );
		}
		$resultMessage = "task ended with: '$resultMessage'";
		$this->trackable_end( $resultMessage );

		return $res;
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
			if ( $indexedItem = $this->{IndexedItem::relationship_name()}() ) {
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
