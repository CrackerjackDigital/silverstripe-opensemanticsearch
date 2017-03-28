<?php
namespace OpenSemanticSearch;

trait page {

	/**
	 * Add a directory or file via a queued task
	 */
	protected function add() {
		IndexTask::create( [
			\Modular\Fields\Page::field_name( 'ID' ) => $this->owner->ID,
			IndexAction::Name                        => IndexAction::Add,
		] )->write();
	}

	/**
	 * Remove directory or file via a queued task
	 */
	protected function remove() {
		IndexTask::create( [
			\Modular\Fields\Page::field_name( 'ID' ) => $this->owner->ID,
			IndexAction::Name                        => IndexAction::Remove,
		] )->write();
	}
}