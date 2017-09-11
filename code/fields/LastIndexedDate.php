<?php
namespace OpenSemanticSearch\Fields;

use FieldList;
use Modular\Field;
use Modular\Types\DateTimeType;

class LastIndexedDate extends Field implements DateTimeType {
	const Name = 'OSSLastIndexedDate';

	public function updateCMSFields( FieldList $fields ) {
		parent::updateCMSFields( $fields );
		if ( $this->owner->ParentID && \Permission::check( 'ADMIN' ) ) {
			if ($fields->hasTabSet()) {
				$fields->addFieldToTab(
					'Root.Admin',
					new \TextField( self::Name )
				);

			}
		} else {
			$fields->removeByName( self::Name );
		}
	}
}