<?php
namespace OpenSemanticSearch;

use DataExtension;

/**
 * Adds the meta data that OSS extracts or can return to models (generally files)
 *
 * @package OpenSemanticSearch
 */
class MetaDataExtension extends DataExtension {
	const IDField       = 'OSSID';              // name of ID field
	const AuthorField   = 'OSSAuthor';          // field for authors
	const PathField     = 'OSSPath';            // path on service (e.g Solr)
	const InfoDateField = 'OSSInfoDate';        // date info was last updated for this file

	private static $db = [
		self::IDField       => 'Text',
		self::AuthorField   => 'Text',
		self::PathField     => 'Text',
		self::InfoDateField => 'SS_DateTime',
	];

}