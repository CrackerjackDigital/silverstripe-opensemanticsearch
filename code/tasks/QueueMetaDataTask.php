<?php

namespace OpenSemanticSearch\Tasks;

use File;
use Modular\Queue\QueuedTaskDispatcher;
use OpenSemanticSearch\Fields\IndexedItem;
use OpenSemanticSearch\Models\IndexedURL;
use OpenSemanticSearch\Models\MetaDataTask;
use Page;

/**
 * Queues a MetaDataTask for execution later e.g. by QueuedTaskRunner.
 *
 * @package OpenSemanticSearch
 */
class QueueMetaDataTask extends QueuedTaskDispatcher {
	const TaskName = MetaDataTask::class;

	protected $description = 'Queues a MetaDataTask to retrieve MetaData from search index and update models in SilverStripe';

	/**
	 * @param array  $params
	 * @param string $resultMessage
	 *
	 * @return array
	 * @throws \InvalidArgumentException
	 * @throws \ValidationException
	 */
	public function mapParams( $params = [], &$resultMessage = '' ) {
		if ( isset( $params['q'] ) ) {
			// we want to base the tasks to get meta data for off a query to the search index
			$params['SourceQuery'] = $params['q'];
		}
		if ( isset( $params['fid'] ) ) {

			$params[ IndexedItem::field_name() ]       = $params['fid'];
			$params[ IndexedItem::class_field_name() ] = File::class;

		} elseif ( isset( $params['pid'] ) ) {

			$params[ IndexedItem::field_name() ]       = $params['fid'];
			$params[ IndexedItem::class_field_name() ] = Page::class;

		} elseif ( isset( $params['url'] ) ) {
			$data = [
				IndexedURL::URLField => $params['url'],
			];

			$model = IndexedURL::get()->filter( $data )->first();
			if ( ! $model ) {
				$model = new IndexedURL( $data );
				$model->write();
			}
			$params[ IndexedItem::field_name() ]       = $model->ID;
			$params[ IndexedItem::class_field_name() ] = IndexedURL::class;
		}

		return parent::mapParams( $params, $resultMessage );
	}
}