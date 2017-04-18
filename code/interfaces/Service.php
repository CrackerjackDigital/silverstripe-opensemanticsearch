<?php
namespace OpenSemanticSearch\Interfaces;

interface ServiceInterface extends \Modular\Interfaces\Service {
	const IncludeFiles      = 1;
	const IncludeLocalPages = 2;
	const IncludeRemoteURLs = 4;
	const IncludeAll        = 511;

	const TypeSolr = 'solr';
	const TypeOSS  = 'oss';
	const TypeFile = 'file://';
	const TypeURL  = 'http://';

	const SortRelevance  = '';
	const SortNewest     = 'newest';
	const SortOldest     = 'oldest';

	const ViewList    = 'list';             // normal results
	const ViewWords   = 'words';            // word count/cloud
	const ViewPreview = 'preview';
	const ViewImages  = 'images';
	const ViewVideos  = 'videos';
	const ViewGraph   = 'graph';

	const Stemming = 'stemming';

	const OperatorOR  = 'OR';
	const OperatorAND = 'AND';

	const EncodingJSON = 'json';
	const EncodingXML  = 'xml';

}