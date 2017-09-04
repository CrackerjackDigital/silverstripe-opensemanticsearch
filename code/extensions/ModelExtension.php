<?php

namespace OpenSemanticSearch\Extensions;

use OpenSemanticSearch\Traits\reindexer;
use OpenSemanticSearch\Traits\remover;

/**
 * Add to a model which has a field which is a URL which will be indexed.
 *
 * @package OpenSemanticSearch
 */
abstract class ModelExtension extends \Modular\ModelExtension {
	use remover, reindexer;

	public function model() {
		return $this->owner();
	}

	/**
	 * Queue an Index to reindex the model, needs to be done after write so we have an ID for the model (File, Page etc).
	 */
	public function onAfterWrite() {
		$this->reindex();
	}

	/**
	 * Remove the file from the index.
	 */
	public function onBeforeDelete() {
		$this->remove();
	}

}