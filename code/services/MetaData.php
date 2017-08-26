<?php
namespace OpenSemanticSearch\Services;

use OpenSemanticSearch\Exceptions\Exception;
use OpenSemanticSearch\Extensions\MetaDataExtension;
use OpenSemanticSearch\Interfaces\MetaDataInterface;
use OpenSemanticSearch\Interfaces\OSSID;
use OpenSemanticSearch\Interfaces\SearchInterface;
use OpenSemanticSearch\Models\IndexedURL;
use OpenSemanticSearch\Traits\http;
use OpenSemanticSearch\Traits\json;

/**
 * MetaDataService provides api for retrieving structured information from an implementation e.g. for populating fields added by MetaDataExtension.
 *
 * @package OpenSemanticSearch
 */
class MetaDataService extends Service implements MetaDataInterface {
	use json;
	use http;

	/** @var  \OpenSemanticSearch\Interfaces\SearchInterface set via Injector on construct to a suitable search service e.g. %SearchService */
	private $searcher;

	public function setSearcher(SearchInterface $searcher) {
		$this->searcher = $searcher;
	}

	/**
	 * @param \DataObject|OSSID $model
	 *
	 * @return \DataObject|null
	 * @throws \Exception
	 * @throws \Modular\Exceptions\Debug
	 */
	public function populateMetaData($model) {
		if (!$this->validModel($model)) {
			$this->debug_fail(new Exception("Invalid model passed, it may not exist anymore or not have the correct extensions"));
		}
		$found = false;

		/** @var \OpenSemanticSearch\Results\OSSResult $result */
		if ($result = $this->searcher->find( $model )) {
			/** @var \ArrayList $models */
			if ($models = $result->models(true)) {
				$found = $models->first();
			}
		}
		return $found;
	}

	/**
	 * @param \File|\Page|IndexedURL|\DataObject $model
	 *
	 * @return bool
	 */
	protected function validModel($model) {
		return $model->exists()
			&& $model->hasExtension(MetaDataExtension::class)
			&& $model->hasMethod(OSSID::IDMethod);
	}
}