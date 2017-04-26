<?php

namespace OpenSemanticSearch\Extensions;

use OpenSemanticSearch\Interfaces\OSSID;
use OpenSemanticSearch\Traits\adder;
use OpenSemanticSearch\Traits\metadata;
use OpenSemanticSearch\Traits\remover;
use OpenSemanticSearch\Traits\versioned_model;

/**
 * Extensions derived from this should be added to Models which are Versioned.
 *
 * @package OpenSemanticSearch
 */
abstract class VersionedModelExtension extends \DataExtension implements OSSID {
	use versioned_model,      // provides onAfterPublish and onAfterUnpublish hooks
		adder,                // provides queuing or execution of index add task
		metadata,             // provides queuing or execution of metadata task
		remover;              // provides queuing or execution of index removal task

	public function owner() {
		return $this->owner;
	}
}