<?php

namespace OpenSemanticSearch\Interfaces;

interface MetaDataInterface {
	/**
	 * Find the model and update it's MetaData fields as added by MetaDataExtension
	 *
	 * @param \DataObject $model     should support the OSSID interface natively or by extension
	 *
	 * @return array map of fields retrieved
	 */
	public function populateMetaData( $model );
}