<?php

namespace OpenSemanticSearch\Extensions;

use FieldList;
use File;
use Folder;
use Modular\Interfaces\HTTP as HTTPInterface;
use Modular\Traits\file_changed;
use Modular\Traits\md5;
use OpenSemanticSearch\Interfaces\OSSID;
use OpenSemanticSearch\Traits\http;
use OpenSemanticSearch\Traits\reindexer;
use Permission;

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

	const ShowInSearchField = 'ShowInSearch';

	/** @var array add file patterns as matched by fnmatch
	 * '*.jpg' will exclude files ending in 'jpg',
	 * 'assets/secret/*' will exclude the 'assets/secret' directory
	 */
	private static $exclude_patterns = [];

	public function OSSID( $prefixSchema = false ) {
		$link = $this->owner()->Link();
		if ( $prefixSchema ) {
			// make sure we have a 'file://' prefix
			$link = $this->rebuildURL( $link, [ HTTPInterface::PartScheme => HTTPInterface::SchemeFile ] );
		}

		return $link;
	}

	/**
	 * @param null $member not used.
	 *
	 * @return bool
	 */
	public function canView( $member = null ) {
		return Permission::check( 'ADMIN' ) || $this->showInSearchResults();
	}

	/**
	 * Check ShowInSearch is true all the way up to root, and both full filename and basename don't match patterns in config.exclude_patterns.
	 *
	 * @param File   $byParent  set to parent which excludes this model if one does
	 * @param string $byPattern set to pattern from config.exclude_pattersn that excludes this file
	 *
	 * @return bool
	 */
	protected function showInSearchResults( &$byParent = null, &$byPattern = '' ) {
		$model = $this->owner;

		$byParent  = null;
		$byPattern = '';

		if ( $patterns = $this->config()->get( 'exclude_patterns' ) ?: [] ) {
			$filePathName = strtolower( $model->Filename );
			$fileName = basename($filePathName);

			foreach ( $patterns as $pattern ) {
				if ( fnmatch( strtolower( $pattern ), $fileName ) || fnmatch($pattern, $filePathName)) {
					$byPattern = $pattern;
					break;
				}
			}
		}
		if ( ! $byPattern ) {
			while ( $model && $model->exists() && $model->hasField( self::ShowInSearchField ) ) {
				if ( ! $model->{self::ShowInSearchField} ) {
					$byParent = $model;
					break;
				}
				$model = $model->Parent();
			}
		}
		return ! ( $byPattern || $byParent );
	}

	/**
	 * @param FieldList $fields
	 *
	 * @return array
	 *
	 */
	public function updateCMSFields( FieldList $fields ) {
		if ( $this->owner()->ClassName == Folder::class && $this->owner()->ParentID ) {
			if ( $this->showInSearchResults( $byParent, $byExtension ) ) {
				$add = [
					new \CheckboxField( self::ShowInSearchField ),
				];
			} else {
				$message = [];

				if ( $byParent ) {
					$message[] = "Hidden by '" . $byParent->Title . "'";
				}
				if ( $byExtension ) {
					$message[] = "Excluded file pattern '" . $byExtension . "'";
				}
				$add = [
					new \ReadOnlyField( self::ShowInSearchField, implode( ' and ', $message ) ),
				];

			}

			foreach ( $add as $field ) {
				$fields->push( $field );
			}
		}
	}

}