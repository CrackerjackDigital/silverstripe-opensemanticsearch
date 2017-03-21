<?php
namespace OpenSemanticSearch;
/**
 * FileExtension added to File (not versioned files, they have their own extension).
 *
 * @package OpenSemanticSearch
 */
class FileExtension extends \DataExtension {

	protected function Link() {
		return $this->owner->Link();
	}

	/**
	 * Remove file from index if changed e.g. incase has moved, been renamed etc
	 */
	public function onBeforeWrite() {
		if ( $this->owner->isChanged() ) {
			\Injector::inst()->get( 'OpenSemanticSearchService' )->removePage( $this->Link() );
		}

		return parent::onBeforeWrite();
	}

	/**
	 * Add file to index
	 */
	public function onAfterWrite() {
		\Injector::inst()->get( 'OpenSemanticSearchService' )->addPage( $this->Link() );

		return parent::onAfterWrite();
	}

	/**
	 * Remove file from index.
	 */
	public function onAfterDelete() {
		\Injector::inst()->get( 'OpenSemanticSearchService' )->removePage( $this->Link() );

		return parent::onAfterDelete();
	}
}