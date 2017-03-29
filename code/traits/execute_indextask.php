<?php

namespace OpenSemanticSearch;

use Modular\Fields\Title;

/**
 * execute_infotask immediately executes an IndexTask
 *
 * @package OpenSemanticSearch
 */
trait exceute_indextask {
	/**
	 * @return \DataObject
	 */
	abstract public function owner();

	/**
	 * Add a directory or file via a queued task
	 */
	protected function indextask() {
		\Injector::inst()->get( 'IndexTask' )->execute( [
			Title::Name                              => "Add '" . $this->owner()->Title . "'",
			IndexAction::Name                        => IndexAction::Add,
			\Modular\Fields\File::field_name( 'ID' ) => $this->owner()->ID,
		] );
	}

}