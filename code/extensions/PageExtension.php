<?php
namespace OpenSemanticSearch;
/**
 * Extension to add to Pages
 *
 * @package OpenSemanticSearch
 * @property \Page owner
 */
class PageExtension extends \SiteTreeExtension {

	protected function Link() {
		return $this->owner->Link();
	}

	/**
	 * Remove Page from index if changed e.g. incase has moved, been renamed etc
	 */
	public function onBeforePublish( &$original ) {
		if ( $this->owner->isChanged() ) {
			\Injector::inst()->get( IndexInterface::ServiceName )->removePage( $this->Link() );
		}
		return parent::onBeforePublish($original);
	}

	/**
	 * Add Page to index
	 *
	 * @param \SiteTree $original
	 */
	public function onAfterPublish( &$original ) {
		\Injector::inst()->get( IndexInterface::ServiceName )->addPage( $this->Link() );

		return parent::onAfterPublish( $original );
	}

	/**
	 * Remove Page from index when it is unpublished.
	 */
	public function onBeforeUnpublish() {
		\Injector::inst()->get( IndexInterface::ServiceName )->removePage( $this->Link() );

		return parent::onBeforeUnpublish( );
	}
}