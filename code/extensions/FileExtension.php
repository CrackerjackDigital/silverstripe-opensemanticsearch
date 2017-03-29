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
	use after_write;
	use remover;
	use file;
	use dispatch_indextask {
		indextask as reindex;
	}
	use dispatch_infotask {
		infotask as reinfo;
	}

}