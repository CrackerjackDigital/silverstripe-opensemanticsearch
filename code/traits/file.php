<?php
namespace OpenSemanticSearch;

use Injector;
use Modular\Fields\Title;

/**
 * file
 *
 * @package OpenSemanticSearch
 */
trait file {
	public function owner() {
		return $this->owner;
	}

	/**
	 * Add a directory or file via a queued task
	 */
	protected function add() {
		Injector::inst()->get( 'IndexTask' )->dispatch( [
			Title::Name                              => "Add '" . $this->owner()->Title . "'",
			\Modular\Fields\File::field_name( 'ID' ) => $this->owner->ID,
			IndexAction::Name                        => IndexAction::Add,
		] );
	}

	/**
	 * Remove directory or file via a queued task
	 */
	protected function remove() {
		Injector::inst()->get( 'IndexTask' )->dispatch( [
			Title::Name                              => "Remove '" . $this->owner()->Title . "'",
			\Modular\Fields\File::field_name( 'ID' ) => $this->owner()->ID,
			IndexAction::Name                        => IndexAction::Remove,
		] );
	}
}