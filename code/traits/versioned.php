<?php
namespace OpenSemanticSearch;

trait versioned {

	abstract public function add();

	abstract public function remove();

	/**
	 * Remove file from index if changed e.g. incase has moved, been renamed etc
	 *
	 * @param \SiteTree $original
	 */
	public function onBeforePublish( &$original ) {
		$this->remove();
		parent::onBeforePublish( $original );
	}

	/**
	 * Add file to index
	 *
	 * @param \SiteTree $original
	 */
	public function onAfterPublish( &$original ) {
		$this->add();
		parent::onAfterPublish( $original );
	}

	/**
	 * Remove file from index.
	 */
	public function onBeforeUnpublish() {
		$this->remove();
		parent::onBeforeUnpublish();
	}

}
