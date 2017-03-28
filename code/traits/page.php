<?php
namespace OpenSemanticSearch;

trait page {

	/**
	 * Add a directory or file via a queued task
	 */
	protected function add() {
		\Injector::inst()->get( 'IndexTask' )->dispatch( [
			\Modular\Fields\Page::field_name( 'ID' ) => $this->owner->ID,
			IndexAction::Name                        => IndexAction::Add,
		] );
	}

	/**
	 * Remove directory or file via a queued task
	 */
	protected function remove() {
		\Injector::inst()->get( 'IndexTask' )->dispatch( [
			\Modular\Fields\Page::field_name( 'ID' ) => $this->owner->ID,
			IndexAction::Name                        => IndexAction::Remove,
		] );
	}
}