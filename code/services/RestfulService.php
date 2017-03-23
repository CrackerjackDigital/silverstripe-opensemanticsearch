<?php
namespace OpenSemanticSearch;
/**
 * RestfulService implements a typical request/response type service over
 * HTTP using json.
 *
 * @package OpenSemanticSearch
 */
class RestfulService extends Service {
	use http;
	use json;

	private static $context_options = [
	];
}