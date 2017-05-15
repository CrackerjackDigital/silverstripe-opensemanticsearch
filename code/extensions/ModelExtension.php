<?php

namespace OpenSemanticSearch\Extensions;

use OpenSemanticSearch\Traits\remover;

/**
 * Add to a model which has a field which is a URL which will be indexed.
 *
 * @package OpenSemanticSearch
 */
abstract class ModelExtension extends \Modular\ModelExtension {
	use remover;

	public function model() {
		return $this->owner();
	}
}