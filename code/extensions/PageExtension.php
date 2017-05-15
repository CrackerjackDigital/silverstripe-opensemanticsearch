<?php
namespace OpenSemanticSearch\Extensions;

use OpenSemanticSearch\Interfaces\OSSID;
use OpenSemanticSearch\Traits\reindexer;
use OpenSemanticSearch\Traits\remover;

/**
 * Extension to add to Pages to control adding, removing and meta data tasks
 * when they are published and unpublished.
 *
 * @package OpenSemanticSearch
 * @property \Page owner
 */
class PageExtension extends VersionedModelExtension implements OSSID {
	use reindexer, remover;

	public function OSSID( $prefixSchema = false ) {
		if ( $prefixSchema ) {
			return $this->owner()->AbsoluteLink();
		} else {
			return $this->owner()->Link();
		}
	}

	/**
	 * Queue an IndexTask for the model to reindex it.
	 */
	public function onBeforePublish() {
		$this->reindex();
	}

	/**
	 * Remove the file from the index.
	 */
	public function onAfterDelete() {
		$this->remove();
	}


	public function shouldReIndex() {
		return $this->owner()->isChanged();
	}
}