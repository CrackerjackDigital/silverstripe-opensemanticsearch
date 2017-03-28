<?php
namespace OpenSemanticSearch;

use Modular\Fields\File as FileField;
use Modular\Tasks\QueuedTask;

/**
 * Scan through a file or files and update fields in the CMS with information from Solr, such as facets etc
 *
 * @package OpenSemanticSearch
 */
class FileInfoTask extends QueuedTask {
	const QueueName = 'OpenSemanticSearch';

	const GetAll     = 'All';
	const GetMissing = 'Missing';

	private static $singular_name = 'File Information Task';
	private static $plural_name = 'File Information Tasks';

	/**
	 * @param null $params
	 *
	 * @return mixed|void
	 */
	public function execute( $params = null ) {
		set_time_limit( $this->timeout() );
		$args = func_get_arg( 1 ) ?: [];

		$filter = [];
		if ( $opt = $args['filter'] ) {
			if ( $opt == self::GetMissing ) {
				$filter[ MetaDataExtension::InfoDateField ] = '';
			}
		}
		$limit = $args['limit']
			? (int) $args['limit']
			: null;

		if ( $file = $this->{FileField::Name}() ) {
			$files = [ $file ];
		} else {
			$files = \File::get()->filter( $filter )->limit( $limit );
		}

		$service = SolrSearcher::get();
		/** @var \File $file */
		foreach ( $files as $file ) {
			if ( $result = $service->findPath( $file->Link() ) ) {
//				$file->update();
			}
		}
	}
}