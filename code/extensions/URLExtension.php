<?php
namespace OpenSemanticSearch;

/**
 * Add to a model which has a URL field which will be indexed, e.g. OpenSemanticSearch\Link
 *
 * @package OpenSemanticSearch
 */
class URLExtension extends \DataExtension {
	use url;
	use writer;
}