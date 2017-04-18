<?php

namespace OpenSemanticSearch\Extensions;

use DataExtension;

/**
 * Adds the meta data that OSS extracts or can return to models (generally files)
 *
 * @package OpenSemanticSearch
 * @property string $OSSID
 * @property string $OSSAuthor
 * @property string $OSSPath
 * @property string $OSSRetrievedDate
 */
class MetaDataExtension extends DataExtension {
	const AuthorField        = 'OSSAuthor';          // field for authors
	const PathField          = 'OSSPath';            // path on service (e.g Solr)
	const RetrievedDateField = 'OSSRetrievedDate';   // date meta data was last updated for this file

	private static $db = [
		self::AuthorField        => 'Text',
		self::PathField          => 'Text',
		self::RetrievedDateField => 'SS_DateTime',
	];

}