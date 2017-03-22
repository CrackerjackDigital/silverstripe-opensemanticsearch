<?php
namespace OpenSemanticSearch;
use Folder;

/**
 * Extensions to add to Versioned Files
 *
 *
 * @package OpenSemanticSearch
 */
class VersionedFileExtension extends \DataExtension {
	/**
	 * Get the CMS link to the file, will be e.g. 'assets/...'
	 *
	 * @return mixed
	 */
	protected function Link() {
		return $this->owner->Filename;
	}

	/**
	 * Checks the extended file model is a Folder (which derives from File).
	 *
	 * @return bool
	 */
	protected function isFolder() {
		return $this->owner->ClassName == Folder::class;
	}

	/**
	 * Remove Page from index if changed e.g. incase has moved, been renamed etc
	 */
	public function onBeforePublish( &$original ) {
		if ( $this->owner->isChanged() ) {
			if ( $this->owner->isChanged() ) {
				if ( $this->isFolder() ) {
					\Injector::inst()->get( IndexInterface::ServiceName )->removeDirectory( $this->Link() );
				} else {
					\Injector::inst()->get( IndexInterface::ServiceName )->removeFile( $this->Link() );
				}
			}
		}
	}

	/**
	 * Add Page to index
	 *
	 * @param \SiteTree $original
	 */
	public function onAfterPublish( &$original ) {
		if ( $this->isFolder() ) {
			\Injector::inst()->get( IndexInterface::ServiceName )->addDirector( $this->Link() );
		} else {
			\Injector::inst()->get( IndexInterface::ServiceName )->addFile( $this->Link() );
		}
	}

	/**
	 * Remove Page from index when it is unpublished.
	 */
	public function onBeforeUnpublish() {
		if ( $this->isFolder() ) {
			\Injector::inst()->get( IndexInterface::ServiceName )->removeDirectory( $this->Link() );
		} else {
			\Injector::inst()->get( IndexInterface::ServiceName )->removeFile( $this->Link() );
		}
	}
}