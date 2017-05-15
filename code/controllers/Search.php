<?php

namespace OpenSemanticSearch\Controllers;

use DataObject;
use Member;
use OpenSemanticSearch\Interfaces\SearchInterface;

class Search extends \ContentController {
	const StartParam = 'start';
	const LimitParam = 'limit';

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

	/** @var array Default search parameters to use if none provided e.g. on query string or post */
	private static $search_options = [
		'start' => 0,
		'limit' => 100,
	];

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
		return $model->canView() && (
			$this->config()->get( 'require_login' )
				? Member::currentUserID()
				: true
			);
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

				$service->searchOptions( $this->searchOptions( $request ) );

				if ( $result = $service->search( $terms ) ) {

					// check with model and (this) controller if OK to view
					$models = $result->models( true )
					                 ->filterByCallback(
						                 function ( DataObject $model ) {
							                 return $this->canViewModel( $model );
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

	/**
	 * Return merged incoming options which override the config.search_options settings. Filters out values which are null.
	 *
	 * @param \SS_HTTPRequest $request
	 *
	 * @return array
	 */
	public function searchOptions( \SS_HTTPRequest $request ) {
		$defaults = $this->config()->get( 'search_options' ) ?: [];

		return array_filter(
			[
				'start' => $request->requestVar( self::StartParam ) ?: $defaults['start'],
				'limit' => $request->requestVar( self::LimitParam ) ?: $defaults['limit'],
			],
			function ( $value ) {
				return ! is_null( $value );
			}
		);
	}

	public static function require_login() {
		return static::config()->get( 'require_login' );
	}
}