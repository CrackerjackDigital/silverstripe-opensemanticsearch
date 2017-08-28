<?php

namespace OpenSemanticSearch\Tasks;

use Modular\Queue\QueuedTaskDispatcher;

/**
 * Queues an Index for execution later e.g. by QueuedTaskRunner.
 *
 * @package OpenSemanticSearch
 */
class QueueIndexTask extends QueuedTaskDispatcher {
	const TaskName = Index::class;

	protected $description = 'Queues an Index to add, remove or update the search index';
}