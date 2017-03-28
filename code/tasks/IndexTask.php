<?php

namespace OpenSemanticSearch;

use Modular\Fields\File as FileField;
use Modular\Fields\Outcome;
use Modular\Fields\Page as PageField;
use Modular\Fields\URL;
use Modular\Tasks\QueuedTask;

/**
 * Task which adds, removes etc items from the OSS service
 *
 * @package OpenSemanticSearch
 */
class IndexTask extends QueuedTask {
	const QueueName = 'OpenSemanticSearch';

	private static $singular_name = 'Index Task';
	private static $plural_name = 'Index Tasks';

	private static $summary_fields = [
		'QueueName'   => 'Queue',
		'Title'       => 'Title',
		'QueuedState' => 'Status',
		'QueuedDate'  => 'Queued Date',
		'Outcome'     => 'Outcome',
		'EventDate'   => 'To Run Date',
		'StartDate'   => 'Started',
		'EndDate'     => 'Ended',
	];

	/**
	 * Check the IndexAction and perform that action on either the related File, Page or the value of the URL field. If both ReIndex is provided then
	 * a Remove then an Add is made.
	 *
	 * @param string $params if provided then e.g. IndexAction.Add or IndexAction.Remove, otherwise the Task.IndexAction field value will be used.
	 *
	 * @return mixed|void
	 */
	public function execute( $params = null ) {
		$this->trackable_start(__FUNCTION__, "Processing task '$this->Title'");

		set_time_limit( $this->timeout() );

		$res = false;

		$message = '';

		try {
			$this()->update( [
				Outcome::Name => Outcome::Determining,
			] )->write();

			$action  = $this()->{IndexAction::Name};
			$service = OSSIndexer::get();

			if ( $id = $this()->{FileField::field_name()} ) {
				$what = $this()->{FileField::Name}();

			} elseif ( $id = $this()->{PageField::field_name()} ) {
				$what = $this()->{PageField::Name}();

			} else {
				$what = $this()->{URL::Name};
			}
			if ( $what ) {
				if ( $action == IndexAction::ReIndex || $action == IndexAction::Remove ) {
					if ( $what instanceof \Folder ) {
						$res = $service->removePath( $what->Filename );
					} elseif ( $what instanceof \File ) {
						$res = $service->removePath( $what->Filename );
					} elseif ( $what instanceof \Page ) {
						$res = $service->removePage( $what->Link() );
					} else {
						$res = $service->removeURL( $what );
					}
				}
				if ( $action == IndexAction::ReIndex || $action == IndexAction::Add ) {
					if ( $what instanceof \Folder ) {
						$res = $service->addDirectory( $what->Filename );
					} elseif ( $what instanceof \File ) {
						$res = $service->addFile( $what->Filename );
					} elseif ( $what instanceof \Page ) {
						$res = $service->addPage( $what->Link() );
					} else {
						$res = $service->addURL( $what );
					}
				}
			}
			$message = 'OK';
		} catch ( \Exception $e ) {
			$res = false;
			$message = $e->getMessage();
		}

		if ( $res ) {
			$this->success($message);

		} else {
			$this->fail($message);
		}

		$this->trackable_end($message);
	}

	public function onBeforeWrite() {
		parent::onBeforeWrite();
		if ( ! $this->Title ) {
			if ( $target = $this->{PageField::Name}() ) {
				$title = $target->Title;
			} elseif ( $target = $this->{FileField::Name}() ) {
				$title = $target->Title;
			} else {
				$title = $this->{URL::Name};
			}
			$action = $this->{IndexAction::Name};

			$this->Title = _t(
				"OpenSemanticSearch.IndexAction.$action",
				"$action '{title}'",
				[ 'title' => $title ]
			);
		}
	}
}
