<?php
namespace OpenSemanticSearch;

class OSSController extends \ContentController {
	private static $allowed_actions = [
		'search' => '->canSearch'
	];

	private static $url_handlers = [
		'' => 'search'
	];

	private static $results_templates = [
		'Layout' => 'Page_results',
	    'Page' => 'Page'
	];

	public function canSearch() {
		return true;
	}

	public function search(\SS_HTTPRequest $request) {
		if ($request->isPOST()) {
			$terms = $request->postVar('q');
		} else {
			$terms = $request->getVar('q');
		}

		$results = new \ArrayList();

		if ($terms) {
			/** @var SearchInterface $service */
			$service = \Injector::inst()->get(SearchInterface::ServiceName);
			$results = $service->search($terms);
		}
		return $this->renderWith(
			$this->config()->get('results_templates'),
			new \ArrayData([
				'Results' => new \PaginatedList($results)
			])
		);
	}
}