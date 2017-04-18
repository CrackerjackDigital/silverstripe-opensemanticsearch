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
	 * @param array $data to add to task when created, e.g. [ 'FileID' => 1212 ] or [ 'PageID' => 223 ]
	 */
	protected function add( $item ) {
		return \Injector::inst()->get( 'IndexTask' )->execute(
			[
				Title::Name               => "Add '" . $this->owner()->Title . "'",
				IndexAction::Name         => IndexAction::Add,
				IndexedItem::field_name() => $item->ID,
				IndexedItem::class_field_name() => $item->ClassName
			]
		);
	}

}