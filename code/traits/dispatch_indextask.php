<?php

namespace OpenSemanticSearch;

use Modular\Fields\Title;

/**
 * dispatch_indextask queues an IndexTask for later
 *
 * @package OpenSemanticSearch
 */
trait dispatch_indextask {
	/**
	 * @return \DataObject
	 */
	abstract public function owner();

	/**
	 * Add a directory or file via a queued task
	 */
	protected function indextask() {
		\Injector::inst()->get( 'IndexTask' )->dispatch( [
			Title::Name                              => "Add '" . $this->owner()->Title . "'",
			IndexAction::Name                        => IndexAction::Add,
			\Modular\Fields\File::field_name( 'ID' ) => $this->owner()->ID,
		] );
	}

}