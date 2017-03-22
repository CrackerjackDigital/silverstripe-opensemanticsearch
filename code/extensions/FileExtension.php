<?php
namespace OpenSemanticSearch;

use File;
use Folder;

/**
 * FileExtension added to File (not versioned files, they have their own extension).
 *
 * @package OpenSemanticSearch
 *
 * @property \DataObject owner
 */
class FileExtension extends \DataExtension {
	/**
	 * Get the CMS link to the file, will be e.g. 'assets/...'
	 * @return mixed
	 */
	protected function Link() {
		return $this->owner->Filename;
	}

	/**
	 * Checks the extended file model is a Folder (which derives from File).
	 * @return bool
	 */
	protected function isFolder() {
		return $this->owner->ClassName == Folder::class;
	}

	/**
	 * Remove file from index if changed e.g. incase has moved, been renamed etc
	 */
	public function onBeforeWrite() {
		if ( $this->owner->isChanged()) {
			if ( $this->isFolder() ) {
				\Injector::inst()->get( IndexInterface::ServiceName )->removeDirectory( $this->Link() );
			} else {
				\Injector::inst()->get( IndexInterface::ServiceName )->removeFile( $this->Link() );
			}
		}

		return parent::onBeforeWrite();
	}

	/**
	 * Add file to index
	 */
	public function onAfterWrite() {
		if ( $this->isFolder() ) {
			\Injector::inst()->get( IndexInterface::ServiceName )->addDirector( $this->Link() );
		} else {
			\Injector::inst()->get( IndexInterface::ServiceName )->addFile( $this->Link() );
		}

		return parent::onAfterWrite();
	}

	/**
	 * Remove file from index.
	 */
	public function onAfterDelete() {
		if ( $this->isFolder() ) {
			\Injector::inst()->get( IndexInterface::ServiceName )->removeDirectory( $this->Link() );
		} else {
			\Injector::inst()->get( IndexInterface::ServiceName )->removeFile( $this->Link() );
		}

		return parent::onAfterDelete();
	}
}