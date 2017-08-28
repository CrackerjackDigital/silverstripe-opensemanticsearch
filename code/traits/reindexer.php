<?php

namespace OpenSemanticSearch\Traits;

use DataObject;
use Modular\Fields\FileContentHash;
use Modular\Fields\FileModifiedStamp;
use Modular\Fields\Title;
use OpenSemanticSearch\Fields\IndexAction;
use OpenSemanticSearch\Fields\IndexedItem;

/**
 * Remover Queues an Index for later or does it immediately depending on Injector
 * service for 'Index'.
 *
 * @package OpenSemanticSearch
 */
trait reindexer {
	/**
	 * Return the model, if exhibited on a Model should return $this, if an extension should return owner.
	 *
	 * @return DataObject
	 */
	abstract public function model();

	abstract protected function shouldReIndex();

	/**
	 * Add an item to reindex via a queued task if it requires it, by default only files not folders.
	 *
	 * @param bool $filesOnly
	 *
	 * @return
	 */
	protected function reindex() {
		if ( $this->shouldReIndex() ) {
			$model = $this->model();

			return \Injector::inst()->create(
				'Index',
				[
					Title::Name                     => "ReIndex '" . $model->Title . "'",
					IndexAction::Name               => IndexAction::ReIndex,
					IndexedItem::field_name()       => $model->ID,
					IndexedItem::class_field_name() => $model->ClassName,
				]
			)->dispatch();
		}
	}

}