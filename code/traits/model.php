<?php
namespace OpenSemanticSearch\Traits;

use Injector;
use Modular\Fields\Title;
use OpenSemanticSearch\Fields\IndexAction;
use OpenSemanticSearch\Fields\IndexedItem;

/**
 * Add onAfterWrite and onAfterDelete handlers to add and remove extended model from Index. Also adds a
 * MetaDataTask onAfterWrite.
 *
 * @package OpenSemanticSearch
 */
trait model {
	abstract function add($item);

	abstract function metadata($item);

	abstract function remove($item);

	abstract function owner();

	public function onBeforeWrite() {
		$this->remove($this->owner());
	}

	/**
	 * Queue an IndexTask and a MetaData task for the model.
	 */
	public function onAfterWrite() {
		$this->add($this->owner());
		$this->metadata($this->owner());
	}

	/**
	 * Remove the file from the index.
	 */
	public function onAfterDelete() {
		$this->remove($this->owner());
	}
}