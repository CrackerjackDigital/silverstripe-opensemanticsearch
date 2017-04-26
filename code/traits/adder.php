<?php

namespace OpenSemanticSearch\Traits;

use Modular\Fields\Title;
use OpenSemanticSearch\Fields\IndexAction;
use OpenSemanticSearch\Fields\IndexedItem;

/**
 * Remover Queues an IndexTask for later or does it immediately depending on Injector
 * service for 'IndexTask'.
 *
 * @package OpenSemanticSearch
 */
trait adder {
	/**
	 * Add an item to index via a queued task
	 *
	 * @param \DataObject $item to add to queue
	 *
	 * @return
	 */
	protected function add( $item ) {
		return \Injector::inst()->create(
			'IndexTask',
			[
				Title::Name                     => "Add '" . $this->owner()->Title . "' to index",
				IndexAction::Name               => IndexAction::Add,
				IndexedItem::field_name()       => $item->ID,
				IndexedItem::class_field_name() => $item->ClassName,
			]
		)->dispatch();
	}

}