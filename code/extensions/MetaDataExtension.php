<?php
namespace OpenSemanticSearch;

use DataExtension;

/**
 * Adds the meta data that OSS extracts or can return to models (generally files)
 *
 * @package OpenSemanticSearch
 */
class MetaDataExtension extends DataExtension {
	private static $db = [
		'OSSID'         => 'Text',
		'OSSAuthor'     => 'Text',
		'OSSRemotePath' => 'Text',
		'OSSMDate'      => 'Varchar(20)',
	];

}