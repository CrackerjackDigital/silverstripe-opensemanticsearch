<?php
namespace OpenSemanticSearch\Traits;

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
	 */
	protected function remove( $item ) {
		return \Injector::inst()->get( 'IndexTask' )->execute(
			[
				IndexAction::Name => IndexAction::Remove,
			    IndexedItem::field_name() => $item->ID,
			    IndexedItem::class_field_name() => $item->ClassName
			]
		);
	}

}