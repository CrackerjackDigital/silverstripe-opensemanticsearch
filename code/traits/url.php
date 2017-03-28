<?php
namespace OpenSemanticSearch;

/**
 * file
 *
 * @package OpenSemanticSearch
 */
trait url {
	/**
	 * Return the name of the field on the model which holds the URL to index, override in model to get other field other than 'URL'
	 *
	 * @return string
	 */
	public function ossURLFieldName() {
		return \Modular\Fields\URL::Name;
	}

	/**
	 * Add a directory or file via a queued task
	 */
	protected function add() {
		$fieldName = $this->ossURLFieldName();

		\Injector::inst()->get( 'IndexTask' )->dispatch( [
			\Modular\Fields\URL::Name => $this->owner->$fieldName,
			IndexAction::Name         => IndexAction::Add,
		] );
	}

	/**
	 * Remove directory or file via a queued task
	 */
	protected function remove() {
		$fieldName = $this->ossURLFieldName();

		\Injector::inst()->get( 'IndexTask' )->dispatch( [
			\Modular\Fields\URL::Name => $this->owner->$fieldName,
			IndexAction::Name         => IndexAction::Remove,
		] );
	}
}