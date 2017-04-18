<?php
namespace OpenSemanticSearch\Extensions;

use OpenSemanticSearch\Interfaces\OSSID;

/**
 * Extension to add to Pages to control adding, removing and meta data tasks
 * when they are published and unpublished.
 *
 * @package OpenSemanticSearch
 * @property \Page owner
 */
class PageExtension extends VersionedModelExtension implements OSSID {
	public function OSSID() {
		return $this->owner()->Link();
	}

}