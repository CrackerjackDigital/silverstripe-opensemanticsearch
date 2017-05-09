<?php

namespace OpenSemanticSearch\Tasks;

use Exception;
use Modular\Queue\QueuedTaskDispatcher;
use OpenSemanticSearch\Models\IndexTask;

/**
 * Queues an IndexTask for execution later e.g. by QueuedTaskRunner.
 *
 * @package OpenSemanticSearch
 */
class QueueIndexTask extends QueuedTaskDispatcher {
	const TaskName = IndexTask::class;

	protected $description = 'Queues an IndexTask to add, remove or update the search index';
}