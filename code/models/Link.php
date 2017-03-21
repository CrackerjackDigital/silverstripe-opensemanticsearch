<?php
namespace OpenSemanticSearch;

/**
 * Link represents an ephemeral link to an external page or resource which can be indexed by OSS/Solr.
 *
 * @package OpenSemanticSearch
 */
class Link extends \DataObject {
	private static $db = [
		'Link' => 'Text'
	];
}