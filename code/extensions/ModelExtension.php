<?php
namespace OpenSemanticSearch\Extensions;

use OpenSemanticSearch\Traits\adder;
use OpenSemanticSearch\Traits\metadata;
use OpenSemanticSearch\Traits\model;
use OpenSemanticSearch\Traits\remover;

/**
 * Add to a model which has a field which is a URL which will be indexed.
 *
 * @package OpenSemanticSearch
 */
abstract class ModelExtension extends \Modular\ModelExtension {
	use model,      // provides onAfterWrite and onAfterDelete hooks
		adder,      // provides queuing or execution of index add task
		metadata,   // provides queuing or execution of metadata task
		remover;    // provides queuing or execution of index removal task

	public function owner() {
		return $this->owner;
	}
}