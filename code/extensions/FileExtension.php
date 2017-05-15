<?php

namespace OpenSemanticSearch\Extensions;

use Modular\Interfaces\HTTP as HTTPInterface;
use Modular\Traits\file_changed;
use Modular\Traits\md5;
use OpenSemanticSearch\Interfaces\OSSID;
use OpenSemanticSearch\Traits\http;
use OpenSemanticSearch\Traits\reindexer;

/**
 * Extension to add to files to add, remove and gather meta data from them. Expects there also to be FileModifiedStamp and FileContentHash fields
 * add to file to work well on detecting file changes.
 *
 * @package OpenSemanticSearch
 *
 */
class FileExtension extends ModelExtension implements OSSID {
	use file_changed {
		fileChanged as shouldReIndex;
	}
	use reindexer, http, md5;

	public function OSSID( $prefixSchema = false ) {
		$link = $this->owner()->Link();
		if ( $prefixSchema ) {
			// make sure we have a 'file://' prefix
			$link = $this->rebuildURL( $link, [ HTTPInterface::PartScheme => HTTPInterface::SchemeFile ] );
		}

		return $link;
	}

	/**
	 * Queue an IndexTask to reindex the model
	 */
	public function onBeforeWrite() {
		$this->reindex();
	}

	/**
	 * Remove the file from the index.
	 */
	public function onAfterDelete() {
		$this->remove();
	}

}