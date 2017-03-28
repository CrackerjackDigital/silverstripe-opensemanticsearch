<?php

namespace OpenSemanticSearch;

use Modular\Task;
use Modular\Fields\Page as PageField;
use Modular\Fields\File as FileField;
use Modular\Fields\URL as URLField;

/**
 * Task to add queuing of a particular file, page or url to the task queue to be picked up by QueuedTaskHandler
 *
 * @package OpenSemanticSearch
 */
class QueueIndexTask extends Task {
	const PageIDParam = 'pageid';
	const FileIDParam = 'fileid';
	const URLParam    = 'url';

	/**
	 * Given a pageid, fileid or url as parameters add an IndexTask to ReIndex that page, file or url.
	 *
	 * @param null $params
	 *
	 * @return int ID of task created
	 */
	public function execute( $params = null ) {
		$this->trackable_start(__METHOD__, "Trying to queue an IndexTask");

		if ( isset( $params[ self::PageIDParam ] ) ) {
			$task = IndexTask::dispatch( [
				PageField::field_name()   => $params[ self::PageIDParam ],
				IndexAction::field_name() => IndexAction::ReIndex,
			] );
		} elseif ( isset( $params[ self::FileIDParam ] ) ) {
			$task = IndexTask::dispatch( [
				FileField::field_name()   => $params[ self::FileIDParam ],
				IndexAction::field_name() => IndexAction::ReIndex,
			] );

		} elseif ( isset( $params[ self::URLParam ] ) ) {
			$task = IndexTask::dispatch( [
				URLField::field_name()    => $params[ self::URLParam ],
				IndexAction::field_name() => IndexAction::ReIndex,
			] );
		} else {
			$this->debug_fail( new Exception( "No valid parameters passed" ) );
		}
		$this->trackable_end("queued task '$task->Title'");

	}
}