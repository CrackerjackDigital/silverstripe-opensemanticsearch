<?php
namespace OpenSemanticSearch\Extensions;

/**
 * Extension to add to files which are Versioned to add, remove and gather meta data from them.
 *
 * @package OpenSemanticSearch
 *
 */
class VersionedFileExtension extends VersionedModelExtension {
	public function OSSID() {
		return $this->owner()->Link();
	}

}