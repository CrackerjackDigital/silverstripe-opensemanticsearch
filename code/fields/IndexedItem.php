<?php
namespace OpenSemanticSearch\Fields;

use Modular\Fields\RefOneAnyField;
use ValidationResult;

class IndexedItem extends RefOneAnyField {
	const Name = 'IndexedItem';

	public function classFieldName() {
		return $this()->{static::relationship_name()}()->classValue();
	}

}