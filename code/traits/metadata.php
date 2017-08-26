<?php

namespace OpenSemanticSearch\Traits;

use Modular\Fields\Title;
use OpenSemanticSearch\Fields\IndexAction;
use OpenSemanticSearch\Fields\IndexedItem;

/**
 * Adds a MetaData to the queue or executes a MetaData depending on Injector
 * MetaData config.
 *
 * @package OpenSemanticSearch
 */
trait metadata {
	/**
	 * @return \DataObject
	 */
	abstract public function owner();

	/**
	 * Retrieve meta data for an item via a queued task
	 */
	protected function metadata( $item ) {
		return \Injector::inst()->create(
			'MetaData',
			[
				Title::Name                     => "Get MetaData for '" . $this->owner()->Title . "'",
				IndexedItem::field_name()       => $item->ID,
				IndexedItem::class_field_name() => $item->ClassName,
			]
		)->dispatch();
	}

}