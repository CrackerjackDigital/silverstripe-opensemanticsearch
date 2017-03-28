<?php
namespace OpenSemanticSearch;


trait deleter {
	abstract public function remove();

	/**
	 * Remove file from index.
	 */
	public function onAfterDelete() {
		$this->remove();
		parent::onAfterDelete();
	}

}