<?php

namespace OpenSemanticSearch\Traits;

use Modular\Fields\Title;
use OpenSemanticSearch\Fields\IndexAction;
use OpenSemanticSearch\Fields\IndexedItem;

/**
 * Remover Queues a remove task for later or does it immediately depending on Injector
 * service for 'IndexTask'.
 *
 * @package OpenSemanticSearch
 */
trait remover {
	/**
	 * Remove an item from the index via a queued task
	 *
	 * @param \DataObject $item for the task to index
	 *
	 * @return
	 */
	protected function remove( $item) {
		return \Injector::inst()->create(
			'IndexTask',
			[
				Title::Name                     => "Remove '" . $this->owner()->Title . "' from index",
				IndexAction::Name               => IndexAction::Remove,
				IndexedItem::field_name()       => $item->ID,
				IndexedItem::class_field_name() => $item->ClassName
			]
		)->dispatch();
	}
}