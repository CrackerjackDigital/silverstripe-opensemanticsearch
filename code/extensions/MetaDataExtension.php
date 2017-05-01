<?php

namespace OpenSemanticSearch\Extensions;

use FieldList;
use Modular\Fields\DateTimeField;
use Modular\Forms\TabField;
use Modular\Interfaces\Mappable;
use Modular\Traits\bitfield;
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
class MetaDataExtension extends ModelExtension {
	use bitfield,
		mappable_model,
		mappable_mapper,
		mappable_map_map;

	const AuthorField        = 'OSSAuthor';          // field for authors
	const PathField          = 'OSSPath';            // path on service (e.g Solr)
	const RetrievedDateField = 'OSSRetrievedDate';   // date meta data was last updated for this file

	private static $db = [
		self::AuthorField        => 'Text',
		self::PathField          => 'Text',
		self::RetrievedDateField => 'SS_DateTime',
	];
	/**
	 * Map from solr result via solarium client to this extensions fields
	 *
	 * @var array
	 */
	private static $mappable_map = [
		'solarium' => [
			'author[]' => 'updateOSSAuthors()',
		],
	];

	/**
	 * @param \FieldList $fields
	 *
	 * @return array
	 *
	 */
	public function updateCMSFields(FieldList $fields) {
		if ($fields->hasTabSet()) {
			$fields->addFieldsToTab(
				'Root.SearchIndexFields',
				[
					new \ReadonlyField( self::AuthorField ),
					new \ReadonlyField( self::PathField ),
					new \ReadonlyField( self::RetrievedDateField ),
				]
			);
		}
	}

	/**
	 * Called by traits, if exhibited on an extension this should return the owner, if exhibited
	 * on a model this should return the model itself.
	 *
	 * @return \DataObject|\Modular\Interfaces\Mappable
	 */
	public function model() {
		return $this->owner;
	}

	public function updateOSSAuthors($data) {
		if (!$this()->Authors()->count()) {

		}
	}

	/**
	 * Update the model mapping incoming fields to the model fields.
	 *
	 * @param string $source key used to look up a suitable map in config.quaff_map
	 * @param array  $data
	 *
	 * @param int    $options
	 *
	 * @return $this
	 * @throws \ValidationException
	 */
	public function updateOSSMetaData( $source = 'solarium', array $data = [], $options = Mappable::DefaultMappableOptions ) {
		$model = $this->model();

		$model->mappableUpdate( $source, $data, $options );
		$model->update( [
			self::RetrievedDateField => DateTimeField::now(),
		] );
		$model->write();

		return $this;
	}

}