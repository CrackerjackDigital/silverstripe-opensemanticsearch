<?php
namespace OpenSemanticSearch\Services;

use OpenSemanticSearch\Exceptions\Exception;
use OpenSemanticSearch\Extensions\MetaDataExtension;
use OpenSemanticSearch\Interfaces\MetaDataInterface;
use OpenSemanticSearch\Interfaces\OSSID;
use OpenSemanticSearch\Interfaces\SearchInterface;
use OpenSemanticSearch\Traits\json;
use OpenSemanticSearch\Services\Service;

/**
 * MetaDataService provides api for retrieving structured information from an implementation e.g. for populating fields added by MetaDataExtension.
 *
 * @package OpenSemanticSearch
 */
class MetaDataService extends Service implements MetaDataInterface {
	use json;

	const ServiceName = 'MetaDataService';

	/** @var  \OpenSemanticSearch\Interfaces\SearchInterface set via Injector on construct to a suitable search service e.g. %SearchService */
	private $searcher;

	public function setSearcher(SearchInterface $searcher) {
		$this->searcher = $searcher;
	}

	/**
	 * @param \DataObject|\OpenSemanticSearch\Interfaces\OSSID $model
	 *
	 * @return array|void
	 * @throws \Modular\Exceptions\Exception
	 */
	public function populateMetaData($model) {
		if (!$this->validModel($model)) {
			$this->debug_fail(new Exception("Invalid model passed"));
		}
		$indexed = $this->searcher->findByID( $model->OSSID() );
	}

	/**
	 * @param \DataObject $model
	 *
	 * @return bool
	 */
	protected function validModel($model) {
		return $model->exists()
			&& $model->hasExtension(MetaDataExtension::class)
			&& $model->hasMethod(OSSID::IDMethod);
	}
}