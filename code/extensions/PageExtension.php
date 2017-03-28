<?php
namespace OpenSemanticSearch;

use SiteTreeExtension;

/**
 * Extension to add to Pages
 *
 * @package OpenSemanticSearch
 * @property \Page owner
 */
class PageExtension extends SiteTreeExtension {
	use versioned;
	use page;

}