<?php
namespace OpenSemanticSearch\Extensions;

use OpenSemanticSearch\Interfaces\OSSID;
use OpenSemanticSearch\Traits\adder;
use OpenSemanticSearch\Traits\metadata;
use OpenSemanticSearch\Traits\model;
use OpenSemanticSearch\Traits\remover;

/**
 * Add to a model which has a field which is a URL which will be indexed.
 *
 * @package OpenSemanticSearch
 */
abstract class ModelExtension extends \DataExtension implements OSSID {
	use model, adder, metadata, remover;

	public function owner() {
		return $this->owner;
	}
}