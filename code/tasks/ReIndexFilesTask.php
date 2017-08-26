<?php

namespace OpenSemanticSearch\Tasks;

use File;
use Modular\Task;
use OpenSemanticSearch\Services\Index;

class ReIndexFilesTask extends Task {

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
		if (!\Director::is_cli()) {
			ob_start('nl2br');
		} else {
			ob_start();
		}
		$service = Index::get();

		/** @var File|\OpenSemanticSearch\Extensions\VersionedModelExtension $page */
		$files = File::get();
		foreach ( $files as $file) {
			echo "reindexing '$file->Title'";
			$service->reindex( $file, $resultMessage);
			echo "$resultMessage\n";
			ob_flush();
		}
	}
}