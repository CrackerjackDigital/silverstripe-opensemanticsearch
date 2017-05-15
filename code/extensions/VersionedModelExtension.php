<?php

namespace OpenSemanticSearch\Extensions;

use DataObject;
use Modular\Traits\owned;
use OpenSemanticSearch\Interfaces\OSSID;
use OpenSemanticSearch\Traits\remover;

/**
 * Extensions derived from this should be added to Models which are Versioned.
 *
 * @package OpenSemanticSearch
 */
abstract class VersionedModelExtension extends \DataExtension implements OSSID {
	use remover, owned;

	/**
	 * Return the model, if exhibited on a Model should return $this, if an extension should return owner.
	 *
	 * @return DataObject
	 */
	public function model() {
		return $this->owner();
	}

}