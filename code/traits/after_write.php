<?php
namespace OpenSemanticSearch;

/**
 * Add onBeforeWrite and onAfterWrite extension hooks to call the exhibiting classes remove() and add() methods
 *
 * @package OpenSemanticSearch
 */
trait onestep_writer {
	abstract protected function reindex();

	/**
	 * Add file to index
	 */
	public function onAfterWrite() {
		$this->reindex();
		parent::onAfterWrite();
	}

}
