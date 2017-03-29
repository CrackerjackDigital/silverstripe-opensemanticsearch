<?php
namespace OpenSemanticSearch;

/**
 * Add onBeforeWrite and onAfterWrite extension hooks to call the exhibiting classes remove() and add() methods
 *
 * @package OpenSemanticSearch
 */
trait before_write {
	/**
	 * Dispatch a ReIndex task to run
	 * @return mixed
	 */
	abstract protected function reindex();

	/**
	 * Dispatch an FileInfoTask to run
	 * @return mixed
	 */
	abstract protected function reinfo();

	/**
	 * Add file to index
	 */
	public function onBeforeWrite() {
		$this->reindex();
		$this->reinfo();
		parent::onBeforeWrite();
	}

}
