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
	 * Queue an Index to reindex the model
	 */
	public function onBeforeWrite() {
		$this->reindex();
	}

	/**
	 * Remove the file from the index.
	 */
	public function onAfterDelete() {
		$this->remove();
	}

}