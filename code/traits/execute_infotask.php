<?php
namespace OpenSemanticSearch;

use Modular\Fields\Title;

/**
 * execute_infotask immediately executes a FileInfoTask
 *
 * @package OpenSemanticSearch
 */
trait execute_infotask {
	/**
	 * @return \DataObject
	 */
	abstract public function owner();

	/**
	 * Add a directory or file via a queued task
	 */
	protected function infotask() {
		\Injector::inst()->get( 'FileInfoTask' )->execute( [
			Title::Name                              => "Add '" . $this->owner()->Title . "'",
			IndexAction::Name                        => IndexAction::Add,
			\Modular\Fields\File::field_name( 'ID' ) => $this->owner()->ID,
		] );
	}

}