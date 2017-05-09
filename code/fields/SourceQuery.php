<?php
namespace OpenSemanticSearch\Fields;

use Modular\Fields\Text;

/**
 * SourceQuery used to store a query string which can be used later to select
 * from a search service, e.g. Solr value would be 'content:"fred"' or just "fred".
 *
 * @package OpenSemanticSearch\Fields
 */
class SourceQuery extends Text {
	const Name = 'SourceQuery';
}