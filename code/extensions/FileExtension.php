<?php
namespace OpenSemanticSearch\Extensions;

/**
 * Extension to add to files to add, remove and gather meta data from them.
 *
 * @package OpenSemanticSearch
 *
 */
class FileExtension extends ModelExtension {
	public function OSSID() {
		return $this->owner()->Filename;
	}

}