<?php
namespace OpenSemanticSearch\Models;

use Modular\Fields\URL;
use OpenSemanticSearch\Interfaces\OSSID;

/**
 * Link represents an ephemeral link to an external page or resource which can be indexed by OSS/Solr.
 *
 * @package OpenSemanticSearch
 */
class IndexedURL extends \DataObject implements OSSID {
	const URLField = URL::Name;
	/**
	 * Return the ID of the model as it would be in OSS, e.g. in the case of a file it would be the Filename field
	 *
	 * @return mixed
	 */
	public function OSSID() {
		return $this->{static::URLField};
	}
}