<?php
namespace OpenSemanticSearch;

/**
 * Extension to add to Pages
 *
 * @package OpenSemanticSearch
 * @property \Page owner
 */
class PageExtension extends \SiteTreeExtension {
	use versioned;
	use page;
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