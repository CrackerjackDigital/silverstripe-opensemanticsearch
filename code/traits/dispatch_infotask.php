<?php
namespace OpenSemanticSearch;

use Modular\Fields\Title;

/**
 * dispatch_infotask queues a FileInfoTask for later
 *
 * @package OpenSemanticSearch
 */
trait dispatch_infotask {
	/**
	 * @return \DataObject
	 */
	abstract public function owner();

	/**
	 * Add a directory or file via a queued task
	 */
	protected function infotask() {
		\Injector::inst()->get( 'FileInfoTask' )->dispatch( [
			Title::Name                              => "Get Info for '" . $this->owner()->Title . "'",
			IndexAction::Name                        => IndexAction::Add,
			\Modular\Fields\File::field_name( 'ID' ) => $this->owner()->ID,
		] );
	}

}