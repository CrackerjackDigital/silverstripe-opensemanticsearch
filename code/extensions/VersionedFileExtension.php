<?php
namespace OpenSemanticSearch;

/**
 * Extensions to add to Versioned Files
 *
 *
 * @package OpenSemanticSearch
 */
class VersionedFileExtension extends \DataExtension {
	use versioned;
	use file;

	public function owner() {
		return $this->owner;
	}
}