<?php
namespace OpenSemanticSearch;

use DataExtension;

/**
 * FileExtension added to File (not versioned files, they have their own extension).
 *
 * @package OpenSemanticSearch
 *
 * @property \DataObject owner
 */
class FileExtension extends DataExtension {
	use writer;
	use deleter;
	use file;

}