<?php
namespace OpenSemanticSearch\Models;

use Modular\Fields\URL;
use OpenSemanticSearch\Interfaces\OSSID;
use OpenSemanticSearch\Traits\http;
use Modular\Interfaces\HTTP as HTTPInterface;

/**
 * Link represents an ephemeral link to an external page or resource which can be indexed by OSS/Solr.
 *
 * @package OpenSemanticSearch
 */
class IndexedURL extends \DataObject implements OSSID {
	use http;

	const URLField = URL::Name;

	/**
	 * Return the ID of the model as it would be in OSS, e.g. in the case of a file it would be the Filename field
	 *
	 * @param bool $prefixSchema
	 *
	 * @return mixed
	 */
	public function OSSID($prefixSchema = false) {
		$link = $this->{static::URLField};

		if ( $prefixSchema ) {
			$link = $this->rebuildURL( $link, [ HTTPInterface::PartScheme => HTTPInterface::SchemeHTTP ]);
		}

		return $link;
	}
}