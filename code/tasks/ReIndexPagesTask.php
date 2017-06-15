<?php
namespace OpenSemanticSearch\Tasks;

use Modular\Task;
use OpenSemanticSearch\Services\IndexService;
use SiteTree;

class ReIndexPagesTask extends Task {

	/**
	 * Service interface method.
	 *
	 * @param array|\ArrayAccess $params
	 * @param string             $resultMessage
	 *
	 * @return mixed
	 * @throws \InvalidArgumentException
	 */
	public function execute( $params = [], &$resultMessage = '' ) {
		if ( ! \Director::is_cli() ) {
			ob_start( 'nl2br' );
		} else {
			ob_start();
		}
		$service = IndexService::get();

		/** @var SiteTree|\OpenSemanticSearch\Extensions\VersionedModelExtension $page */
		$pages = SiteTree::get();
		foreach ( $pages as $page) {
			echo "reindexing '$page->Title'";
			$service->reindex( $page, $resultMessage );
			echo "$resultMessage\n";
			ob_flush();
		}
	}
}