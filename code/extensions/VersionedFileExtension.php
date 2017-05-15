<?php

namespace OpenSemanticSearch\Extensions;

use Modular\Interfaces\HTTP as HTTPInterface;
use Modular\Traits\file_changed;
use Modular\Traits\md5;
use OpenSemanticSearch\Interfaces\OSSID;
use OpenSemanticSearch\Traits\http;
use OpenSemanticSearch\Traits\reindexer;

/**
 * Extension to add to files which are Versioned to add, remove and gather meta data from them.
 *
 * @package OpenSemanticSearch
 *
 */
class VersionedFileExtension extends VersionedModelExtension implements OSSID {
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
	 * Queue a IndexTask to reindex the model.
	 */
	public function onBeforePublish() {
		$this->reindex();
	}

	/**
	 * Remove the file from the index.
	 */
	public function onAfterDelete() {
		$this->remove();
	}
}