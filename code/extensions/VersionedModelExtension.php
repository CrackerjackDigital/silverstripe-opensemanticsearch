<?php

namespace OpenSemanticSearch\Extensions;

use DataObject;
use Modular\Traits\owned;
use OpenSemanticSearch\Interfaces\OSSID;
use OpenSemanticSearch\Traits\adder;
use OpenSemanticSearch\Traits\reindexer;
use OpenSemanticSearch\Traits\remover;

/**
 * Extensions derived from this should be added to Models which are Versioned.
 *
 * @package OpenSemanticSearch
 */
abstract class VersionedModelExtension extends \DataExtension implements OSSID {
	use adder, remover, reindexer, owned;

	/**
	 * Return the model, if exhibited on a Model should return $this, if an extension should return owner.
	 *
	 * @return DataObject
	 */
	public function model() {
		return $this->owner();
	}

	/**
	 * Queue a IndexTask to reindex the model.
	 */
	public function onBeforePublish() {
		$this->remove();
	}

	public function onAfterPublish() {
		$this->add();
	}

	public function onBeforeUnpublish() {
		$this->remove();
	}

	/**
	 * Remove the file from the index.
	 */
	public function onBeforeDelete() {
		$this->remove();
	}

}