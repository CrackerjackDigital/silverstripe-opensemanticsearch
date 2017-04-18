<?php
namespace OpenSemanticSearch\Fields;

use Modular\Fields\Enum;
use Modular\Types\OptionType;

class IndexAction extends Enum implements OptionType {
	const Name = 'IndexAction';

	const Add     = 'Add';
	const Remove  = 'Remove';
	const ReIndex = 'ReIndex';      // a remove then an add will be called

	private static $options = [
		self::ReIndex,
		self::Add,
		self::Remove,
	];
}