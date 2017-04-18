<?php
namespace OpenSemanticSearch\Traits;

use Injector;
use Modular\Fields\Title;
use OpenSemanticSearch\Fields\IndexAction;
use OpenSemanticSearch\Fields\IndexedItem;

/**
 * file trait added to files (not versioned files) to add/save/reindex/remove them from the index
 *
 * @package OpenSemanticSearch
 */
trait model {
	abstract function add($item);

	abstract function metadata($item);

	abstract function remove($item);

	abstract function OSSID();

	abstract function owner();

	public function onAfterWrite() {
		$this->add($this->owner());
		$this->metadata($this->owner());
	}

	public function onAfterDelete() {
		$this->remove($this->owner());
	}
}