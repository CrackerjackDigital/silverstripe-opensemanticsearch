<?php

namespace OpenSemanticSearch\Exceptions;

class StreamException extends Exception {
	/**
	 * StreamException constructor.
	 *
	 * @param resource        $context
	 * @param int             $code
	 * @param \Exception|null $previous
	 */
	public function __construct( $context, $code = 0, \Exception $previous = null ) {
		if (is_resource( $context)) {
			$data = stream_get_meta_data( $context );
			$uri  = $data['uri'];

			if ( isset( $data['timed_out'] ) ) {
				$message = "Timed out after x calling '$uri'";
			} else {
				$message = "Error calling '$uri'";
			}
		} else {
			$message = "Invalid context passed";
		}
		parent::__construct( $message, $code, $previous );
	}
}