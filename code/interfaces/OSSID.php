<?php
namespace OpenSemanticSearch\Interfaces;

/**
 * Interface OSSID declares a method OSSID which should be added to a model or extension to return the models ID as it would be in OSS either from
 * a field on the model or a calculation/fabrication.
 *
 * @package OpenSemanticSearch
 */
interface OSSID {
	// convenience, keep in sync with the method name that returns the ID
	const IDMethod = 'OSSID';
	/**
	 * Return the ID of the model as it would be in OSS, e.g. in the case of a file it would be the Filename field
	 * @return mixed
	 */
	public function OSSID();
}
