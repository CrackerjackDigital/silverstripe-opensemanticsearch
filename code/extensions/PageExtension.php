<?php
namespace OpenSemanticSearch\Extensions;

use OpenSemanticSearch\Interfaces\OSSID;
use OpenSemanticSearch\Traits\http;
use Modular\Interfaces\HTTP as HTTPInterface;

/**
 * Extension to add to Pages to control adding, removing and meta data tasks
 * when they are published and unpublished.
 *
 * @package OpenSemanticSearch
 * @property \Page owner
 */
class PageExtension extends VersionedModelExtension implements OSSID {
	use http;

	public function OSSID($prefixSchema = false) {
		$link = $this->owner()->Link();
		if ($prefixSchema) {
			$link = $this->rebuildURL($link, [ HTTPInterface::PartScheme => HTTPInterface::SchemeHTTP ]);
		}
		return $link;
	}

}