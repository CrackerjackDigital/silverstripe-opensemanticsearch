<?php
namespace OpenSemanticSearch;

/**
 * Add onAfterDelete extension hooks to call the exhibiting classes remove()
 *
 * @package OpenSemanticSearch
 */
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