<?php
namespace OpenSemanticSearch\Extensions;

use OpenSemanticSearch\Interfaces\OSSID;

/**
 * Extension to add to files which are Versioned to add, remove and gather meta data from them.
 *
 * @package OpenSemanticSearch
 *
 */
class VersionedFileExtension extends VersionedModelExtension implements OSSID {
	public function OSSID() {
		return $this->owner()->Link();
	}

}