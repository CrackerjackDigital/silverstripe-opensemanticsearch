<?php

namespace OpenSemanticSearch\Extensions;

use ArrayData;
use ArrayList;
use DataObject;
use DocumentAuthor;
use FieldList;
use File;
use Modular\Fields\DateTimeField;
use Modular\Interfaces\Mappable;
use Modular\Interfaces\Mappable as Mappableinterface;
use Modular\Traits\bitfield;
use Modular\Traits\mappable_map_map;
use Modular\Traits\mappable_mapper;
use Modular\Traits\mappable_model;
use OpenSemanticSearch\Models\IndexedURL;
use Page;
use ReadonlyField;
use SS_List;

/**
 * Adds the meta data that OSS extracts or can return to models (generally files)
 *
 * @package OpenSemanticSearch
 * @property string $OSSID
 * @property string $OSSPath
 * @property string $OSSRetrievedDate
 * @method SS_List OSSAuthors()
 */
class MetaDataExtension extends ModelExtension {
	use bitfield,
		mappable_model,
		mappable_mapper,
		mappable_map_map;

	const OSSIDField = 'OSSID';
	const TitleField = 'OSSTitle';

	// field or relationship for authors, this should be added in an applicaiton extension as a many_may
	// relating to the correct application-specific model if there is one.
	const AuthorField = 'OSSAuthors';

	// date meta data was last updated for this file
	const RetrievedDateField = 'OSSRetrievedDate';
	const ContentTypeField   = 'OSSContentType';
	const ContentField       = 'OSSContent';
	const LastModifiedField  = 'OSSLastModified';

	private static $db = [
		self::OSSIDField         => 'Text',
		self::ContentTypeField   => 'Varchar(32)',
		self::ContentField       => 'Text',
		self::TitleField         => 'Text',
		self::RetrievedDateField => 'SS_DateTime',
		self::LastModifiedField  => 'SS_DateTime',
	];
	/**
	 * Map from solr result via solarium client to this extensions fields
	 *
	 * @var array
	 */
	private static $mappable_map = [
		'solarium' => [
			'title.0'          => \OpenSemanticSearch\Extensions\MetaDataExtension::TitleField,
			'author[]'         => \OpenSemanticSearch\Extensions\MetaDataExtension::AuthorField,
			'content_type.0'   => \OpenSemanticSearch\Extensions\MetaDataExtension::ContentTypeField,
			'id'               => \OpenSemanticSearch\Extensions\MetaDataExtension::OSSIDField,
			'content'          => \OpenSemanticSearch\Extensions\MetaDataExtension::ContentField,
			'file_modified_dt' => \OpenSemanticSearch\Extensions\MetaDataExtension::LastModifiedField,
		],
	];

	/**
	 * If we've added the OSSAuthors relationship to the extended model then add authors from
	 * solr results to it, creating if they don't exist.
	 *
	 * @param array $authors
	 *
	 * @return bool
	 * @throws \InvalidArgumentException
	 * @throws \ValidationException
	 */
	public function mapOSSAuthors( array $authors ) {
		$mapped = false;
		if ($this->model()->hasMethod(self::AuthorField)) {
			$authors = array_filter(
				$authors,
				'trim'
			);

			$existing = DocumentAuthor::get()->filter( [
				'Title' => $authors,
			] );

			foreach ( $authors as $title ) {
				if ( ! $author = $existing->find( 'Title', $title ) ) {
					$author = new DocumentAuthor( [
						'Title' => $title,
					] );
					$author->write();
				}
				$this->model()->{self::AuthorField}()->add( $author );
				$mapped = true;
			}
		}
		return $mapped;
	}

	/**
	 * Gives the content a bit of a tidy before me set it on the OSSContent field.
	 *
	 * @param array|string $content
	 *
	 * @return bool
	 */
	public function mapOSSContent($content) {
		$content = is_array($content) ? implode('\n', $content) : $content;

		$content = trim(preg_replace( "/[\n]+/", "\n", $content ));

		$this->model()->{self::ContentField} = $content;
		return true;
	}

	/**
	 * Called by traits, if exhibited on an extension this should return the owner, if exhibited
	 * on a model this should return the model itself. Also usefull for type hinting inside this extension.
	 *
	 * @return $this|File|Page|IndexedURL|MappableInterface
	 */
	public function model() {
		return $this->owner;
	}

	/**
	 * @param \FieldList $fields
	 *
	 * @return array
	 *
	 */
	public function updateCMSFields( FieldList $fields ) {
		if ( $fields->hasTabSet() ) {
			$fields->addFieldsToTab(
				'Root.SearchIndexFields',
				[
					new ReadonlyField( self::OSSIDField ),
					new ReadonlyField( self::TitleField ),
					new ReadonlyField( self::ContentTypeField ),
					new ReadonlyField( self::ContentField ),
					new ReadonlyField( self::LastModifiedField ) .
					new ReadonlyField( self::RetrievedDateField ),
				]
			);
			if ( $this->model()->hasMethod( self::AuthorField ) ) {
				$fields->addFieldToTab(
					'Root.SearchIndexFields',
					new ReadonlyField( self::AuthorField )
				);
			}
		}
	}


	/**
	 * Returns OSS MetaData as an ArrayObject or a single value if passed.
	 *
	 * @param string $what optional parameter just return a single value
	 *
	 * @return ArrayData|SS_List|string
	 */
	public function getOSSMetaData( $what = '' ) {
		$model    = $this->model();
		$metaData = [
			'ID'            => $model->{self::OSSIDField},
			'Title'         => $model->{self::TitleField},
			'RetrievedDate' => $model->{self::RetrievedDateField},
			'ContentType'   => $model->{self::ContentTypeField},
			'Content'       => $model->{self::ContentField},
			'LastModified'  => $model->{self::LastModifiedField},
		];
		if ( $model->hasMethod( self::AuthorField ) ) {
			// add authors if the model has these related
			$metaData['Authors'] = $model->{self::AuthorField}();

		}

		return $what ? $metaData[ $what ] : new ArrayData( $metaData );
	}

	/**
	 * Update the model mapping incoming fields to the model fields.
	 *
	 * THIS DOESN'T WRITE THE MODEL, JUST SETS THE FIELDS.
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

		return $this;
	}

}