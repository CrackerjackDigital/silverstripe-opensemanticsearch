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
	public function OSSID( $prefixSchema = false ) {
		if ( $prefixSchema ) {
			return $this->owner()->AbsoluteLink();
		} else {
			return $this->owner()->Link();
		}
	}
	/**
	 * Check if any fields have changed so we can reindex page.
	 * @return bool
	 */
	public function shouldReIndex() {
		return $this->owner()->isChanged();
	}
}