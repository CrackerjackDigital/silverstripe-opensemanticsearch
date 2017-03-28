<?php
namespace OpenSemanticSearch;

use DataExtension;

/**
 * Extensions to add to Versioned Files
 *
 *
 * @package OpenSemanticSearch
 */
class VersionedFileExtension extends DataExtension {
	use versioned;
	use file;

}