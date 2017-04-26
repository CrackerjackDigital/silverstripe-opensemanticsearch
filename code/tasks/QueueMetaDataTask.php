<?php

namespace OpenSemanticSearch\Tasks;

use Modular\Fields\File as FileField;
use Modular\Fields\Page as PageField;
use Modular\Fields\QueueName;
use Modular\Fields\URL as URLField;
use Modular\Queue\QueuedTaskDispatcher;
use OpenSemanticSearch\Exceptions\Exception;
use OpenSemanticSearch\Fields\IndexAction;
use OpenSemanticSearch\Models\MetaDataTask;

/**
 * Queues a MetaDataTask for execution later e.g. by QueuedTaskRunner.
 *
 * @package OpenSemanticSearch
 */
class QueueMetaDataTask extends QueuedTaskDispatcher {
	const TaskName    = MetaDataTask::class;

	protected $description = 'Queues a MetaDataTask to retrieve MetaData from search index and update models in SilverStripe';

	/**
	 * Given a pageid, fileid or url as parameters add a MetaDataTask to get meta data for that model. If no params are passed then a 'general'
	 * MetaDataTask is queued which will use defaults to update files found.
	 *
	 * @param array|\ArrayAccess $params
	 * @param string $resultMessage
	 *
	 * @return int ID of task created
	 */
	public function execute( $params = [], &$resultMessage = '' ) {
		$resultMessage = "Queuing MetaData task";
		$this->trackable_start( __METHOD__, $resultMessage );

		$this->debugger()->set_error_exception($resultMessage);
		try {
			$task          = $this->dispatch( $params, $resultMessage );
			$resultMessage = "Dispatched MetaData task '$task->Title'";

		} catch ( Exception $e ) {
		}
		$this->trackable_end( $resultMessage );

	}

}