<?php

namespace OpenSemanticSearch\Extensions;

use FieldList;
use Folder;
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
	const IncludeInSearchField = 'ShowInSearch';

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
	 * @param FieldList $fields
	 *
	 * @return array
	 *
	 */
	public function updateCMSFields( FieldList $fields ) {
		if ($this->owner()->ClassName == Folder::class && $this->owner()->ParentID) {
			$add = [
				new \CheckboxField( self::IncludeInSearchField ),
			];

			foreach ( $add as $field ) {
				$fields->push( $field );
			}
		}
	}

}