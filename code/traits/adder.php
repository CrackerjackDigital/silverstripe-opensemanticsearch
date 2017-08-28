<?php

namespace OpenSemanticSearch\Traits;

use DataObject;
use Modular\Fields\Title;
use OpenSemanticSearch\Fields\IndexAction;
use OpenSemanticSearch\Fields\IndexedItem;

/**
 * Remover Queues an Index for later or does it immediately depending on Injector
 * service for 'Index'.
 *
 * @package OpenSemanticSearch
 */
trait adder {
	/**
	 * Return the model, if exhibited on a Model should return $this, if an extension should return owner.
	 *
	 * @return DataObject
	 */
	abstract public function model();

	protected function shouldAdd() {
		return true;
	}

	/**
	 * Add an item to index via a queued task
	 *
	 * @return \Modular\Interfaces\QueuedTask
	 */
	protected function add() {
		if ($this->shouldAdd()) {

			$model = $this->model();

			return \Injector::inst()->create(
				'IndexTask',
				[
					Title::Name                     => "Add '" . $model->Title . "' to index",
					IndexAction::Name               => IndexAction::Add,
					IndexedItem::field_name()       => $model->ID,
					IndexedItem::class_field_name() => $model->ClassName,
				]
			)->dispatch();
		}
	}

}