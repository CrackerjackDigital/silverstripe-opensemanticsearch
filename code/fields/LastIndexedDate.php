<?php
namespace OpenSemanticSearch\Fields;

use Modular\Field;
use Modular\Types\DateTimeType;

class LastIndexedDate extends Field implements DateTimeType {
	const Name = 'OSSLastIndexedDate';

}