<?php
namespace OpenSemanticSearch\Extensions;

use Modular\Interfaces\HTTP as HTTPInterface;
use OpenSemanticSearch\Interfaces\OSSID;
use OpenSemanticSearch\Traits\http;

/**
 * Extension to add to files to add, remove and gather meta data from them.
 *
 * @package OpenSemanticSearch
 *
 */
class FileExtension extends ModelExtension implements OSSID {
	use http;

	public function OSSID($prefixSchema = false) {
		$link = $this->owner()->Link();
		if ( $prefixSchema ) {
			// make sure we have a 'file://' prefix
			$link = $this->rebuildURL( $link, [ HTTPInterface::PartScheme => HTTPInterface::SchemeFile ]);
		}

		return $link;
	}

	public function onBeforeWrite() {
		parent::onBeforeWrite();
	}

}