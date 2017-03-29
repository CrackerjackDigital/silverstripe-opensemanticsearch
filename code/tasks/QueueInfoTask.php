<?php

namespace OpenSemanticSearch;

use Modular\Fields\File as FileField;
use Modular\Fields\Page as PageField;
use Modular\Fields\QueueName;
use Modular\Fields\URL as URLField;
use Modular\Queue\QueuedTaskDispatcher;

/**
 * Task to add queuing of a particular file, page or url to the task queue to be picked up by QueuedTaskHandler
 *
 * @package OpenSemanticSearch
 */
class QueueInfoTask extends QueuedTaskDispatcher {
	const PageIDParam = 'pageid';
	const FileIDParam = 'fileid';
	const URLParam    = 'uri';

	/**
	 * Given a pageid, fileid or url as parameters add an IndexTask to ReIndex that page, file or url.
	 *
	 * @param null   $params
	 *
	 * @param string $resultMessage
	 *
	 * @return int ID of task created
	 * @throws \Modular\Exceptions\Exception
	 */
	public function execute( $params = null, &$resultMessage = '' ) {
		$this->trackable_start( __METHOD__, "Trying to queue an IndexTask" );

		if ( isset( $params[ self::PageIDParam ] ) ) {
			$task = $this->dispatch( [
				PageField::field_name()   => $params[ self::PageIDParam ],
				IndexAction::field_name() => IndexAction::ReIndex,
			] );
		} elseif ( isset( $params[ self::FileIDParam ] ) ) {
			$task = $this->dispatch( [
				FileField::field_name()   => $params[ self::FileIDParam ],
				IndexAction::field_name() => IndexAction::ReIndex,
			] );

		} elseif ( isset( $params[ self::URLParam ] ) ) {
			$task = $this->dispatch( [
				URLField::field_name()    => $params[ self::URLParam ],
				IndexAction::field_name() => IndexAction::ReIndex,
			] );
		} else {
			$resultMessage = "No valid parameters passed";

			return $this->debug_fail( new Exception( $resultMessage ) );
		}
		$resultMessage = "dispatched task '$task->Title'";
		$this->trackable_end( $resultMessage );

	}

}