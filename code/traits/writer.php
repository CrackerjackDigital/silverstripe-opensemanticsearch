<?php
namespace OpenSemanticSearch;

/**
 * Add onBeforeWrite and onAfterWrite extension hooks to call the exhibiting classes remove() and add() methods
 *
 * @package OpenSemanticSearch
 */
trait writer {
	abstract protected function remove();

	abstract protected function add();

	/**
	 * Remove file from index if changed e.g. incase has moved, been renamed etc
	 */
	public function onBeforeWrite() {
		$this->remove();
		parent::onBeforeWrite();
	}

	/**
	 * Add file to index
	 */
	public function onAfterWrite() {
		$this->add();
		parent::onAfterWrite();
	}

}
