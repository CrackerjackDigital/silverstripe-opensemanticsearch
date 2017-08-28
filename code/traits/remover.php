<?php

namespace OpenSemanticSearch\Traits;

use DataObject;
use Modular\Fields\Title;
use OpenSemanticSearch\Extensions\MetaDataExtension;
use OpenSemanticSearch\Extensions\ModelExtension;
use OpenSemanticSearch\Fields\IndexAction;
use OpenSemanticSearch\Fields\IndexedItem;
use OpenSemanticSearch\Fields\LastIndexedDate;

/**
 * Remover Queues a remove task for later or does it immediately depending on Injector
 * service for 'IndexTask'.
 *
 * @package OpenSemanticSearch
 */
trait remover {
	/**
	 * Return the model, if exhibited on a Model should return $this, if an extension should return owner.
	 *
	 * @return DataObject
	 */
	abstract public function model();

	/**
	 * Only remove if we've been indexed previously.
	 * @return bool
	 */
	protected function shouldRemove() {
		return (bool)$this->model()->{LastIndexedDate::Name};
	}

	/**
	 * Remove an item from the index via a queued task
	 *
	 * @return
	 */
	protected function remove() {
		if ($this->shouldRemove()) {

			$model = $this->model();

			return \Injector::inst()->create(
				'IndexTask',
				[
					Title::Name                     => "Remove '" . $model->Title . "' from index",
					IndexAction::Name               => IndexAction::Remove,
					IndexedItem::field_name()       => $model->ID,
					IndexedItem::class_field_name() => $model->ClassName,
				]
			)->dispatch();
		}
	}
}