<?php

namespace OpenSemanticSearch\Controllers;

use DataObject;
use Member;
use OpenSemanticSearch\Interfaces\SearchInterface;

class Search extends \ContentController {
	private static $allowed_actions = [
		'search' => '->canSearch',
	];

	private static $url_handlers = [
		'' => 'search',
	];

	private static $results_templates = [
		'Layout' => 'Page_results',
		'Page'   => 'Page',
	];

	private static $require_login = false;

	public function canSearch() {
		return true;
	}

	/**
	 * Ask controller to check model can be viewed, applied in search method for
	 * each model found in results.
	 *
	 * @param DataObject $model not used by default
	 *
	 * @return bool
	 */
	public function canViewModel( DataObject $model ) {
		return $this->config()->get( 'require_login' )
			? Member::currentUserID()
			: true;
	}

	public function search( \SS_HTTPRequest $request ) {
		if ( ! Member::currentUserID() && static::require_login() ) {
			return $this->redirect(
				'/Security/login?BackURL=/' . $request->getURL( true )
			);
		}
		if ( $request->isPOST() ) {
			$terms = $request->postVar( 'q' );
		} else {
			$terms = $request->getVar( 'q' );
		}

		$results = new \ArrayList();

		$message = '';

		if ( $terms ) {
			try {
				/** @var SearchInterface $service */
				$service = \Injector::inst()->get( SearchInterface::ServiceName );

				if ( $result = $service->search( $terms ) ) {

					// check with model and (this) controller if OK to view
					$models = $result->models( true )
					                 ->filterByCallback(
						                 function ( DataObject $model ) {
							                 return $model->canView() && $this->canViewModel( $model );
						                 } );

					$results = new \PaginatedList( $models, $request );
				}
			} catch ( \Exception $e ) {
				$message = 'Sorry, there was a problem with your request, please try again later';
			}
		}

		return $this->renderWith(
			$this->config()->get( 'results_templates' ),
			new \ArrayData( [
				'Results' => $results,
				'Message' => $message,
				'Query'   => $terms,
			] )
		);
	}

	public static function require_login() {
		return static::config()->get( 'require_login' );
	}
}