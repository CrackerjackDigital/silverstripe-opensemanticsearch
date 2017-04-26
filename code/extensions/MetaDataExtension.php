<?php

namespace OpenSemanticSearch\Extensions;

use DataExtension;
use Modular\Traits\bitfield;
use OpenSemanticSearch\Exceptions\Exception;
use Modular\Traits\mappable_map_map;
use Modular\Traits\mappable_mapper;
use Modular\Traits\mappable_model;

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
	use bitfield, mappable_model, mappable_mapper, mappable_map_map;

	const AuthorField        = 'OSSAuthor';          // field for authors
	const PathField          = 'OSSPath';            // path on service (e.g Solr)
	const RetrievedDateField = 'OSSRetrievedDate';   // date meta data was last updated for this file

	private static $db = [
		self::AuthorField        => 'Text',
		self::PathField          => 'Text',
		self::RetrievedDateField => 'SS_DateTime',
	];

	private static $quaff_map = [
		'solarium' => [

		],
	];

	public function model() {
		return $this->owner;
	}

	/**
	 * Update the model mapping incoming fields to the model fields.
	 *
	 * @param array  $data
	 * @param string $source key used to look up a suitable map in config.quaff_map
	 *
	 * @throws \Modular\Exceptions\Mappable
	 * @throws \ValidationException
	 * @throws null
	 * @internal param array $map e.g. from json_decoded item from a search on solr.
	 */
	public function updateOSSMetaData( array $data, $source = 'solarium' ) {
		$this->model()->mappableUpdate( $data, $source );
	}

}