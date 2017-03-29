<?php
namespace OpenSemanticSearch;

/**
 * Add to a model which has a URL field which will be indexed, e.g. OpenSemanticSearch\Link
 *
 * @package OpenSemanticSearch
 */
class URLExtension extends \DataExtension {
	use url;
	use after_write;
	use dispatch_indextask {
		indextask as reindex;
	}
	use dispatch_infotask {
		infotask as reinfo;
	}

	public function owner() {
		return $this->owner;
	}

}