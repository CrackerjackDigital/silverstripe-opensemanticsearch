<?php
namespace OpenSemanticSearch;
/**
 * Extensions to add to Versioned Files
 *
 *
 * @package OpenSemanticSearch
 */
class VersionedFileExtension extends \DataExtension {


	protected function Link() {
		return $this->owner->Filename;

	}
	/**
	 * Remove Page from index if changed e.g. incase has moved, been renamed etc
	 */
	public function onBeforePublish( &$original ) {
		if ( $this->owner->isChanged() ) {
			\Injector::inst()->get( 'OpenSemanticSearchService' )->removePage( $this->Link() );
		}
	}

	/**
	 * Add Page to index
	 *
	 * @param \SiteTree $original
	 */
	public function onAfterPublish( &$original ) {
		\Injector::inst()->get( 'OpenSemanticSearchService' )->addPage( $this->Link() );
	}

	/**
	 * Remove Page from index when it is unpublished.
	 */
	public function onBeforeUnpublish() {
		\Injector::inst()->get( 'OpenSemanticSearchService' )->removePage( $this->Link() );
	}
}