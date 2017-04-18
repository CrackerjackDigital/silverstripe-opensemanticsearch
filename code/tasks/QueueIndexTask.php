<?php

namespace OpenSemanticSearch\Tasks;

use Exception;
use Modular\Queue\QueuedTaskDispatcher;
use OpenSemanticSearch\Models\IndexTask;

/**
 * Task to add queuing of a particular file, page or url to the task queue to be picked up by qQueuedTaskHandler
 *
 * @package OpenSemanticSearch
 */
class QueueIndexTask extends QueuedTaskDispatcher {
	const TaskName = IndexTask::class;

	protected $description = 'Queues an IndexTask to add, remove or update the search index';

	/**
	 * Given a pageid, fileid or url as parameters add an IndexTask to ReIndex that page, file or url.
	 *
	 * @param array|\ArrayAccess $params
	 * @param string $resultMessage
	 *
	 * @return int ID of task created
	 */
	public function execute( $params = [], &$resultMessage = '' ) {
		$resultMessage = "Queuing IndexTask";
		$this->trackable_start( __METHOD__, $resultMessage );

		$this->debugger()->set_error_exception();
		try {
			$task          = $this->dispatch( $params, $resultMessage );
			$resultMessage = "dispatched task '$task->Title'";

		} catch ( Exception $e ) {
			$resultMessage = $e->getMessage();
		}
		$this->trackable_end( $resultMessage );

	}

}