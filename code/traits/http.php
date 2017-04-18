<?php

namespace OpenSemanticSearch\Traits;

use OpenSemanticSearch\Exceptions\Exception;

/**
 * http simple http request handling using php file methods and stream contexts
 *
 * @package OpenSemanticSearch
 */
trait http {
	/**
	 * Return a decoded response a request to the service endpoint passing params on url and data in request body if provided.
	 *
	 * @param string $service
	 * @param string $endpoint
	 * @param array|\ArrayAccess $params will be added to uri as query string
	 * @param array  $data   to send as the request payload (will become a POST if passed)
	 * @param array  $tokens additional tokens to substitute into the uri
	 *
	 * @return mixed
	 * @throws \OpenSemanticSearch\Exceptions\Exception
	 */
	public function request( $service, $endpoint, $params = [], $data = null, $tokens = [] ) {
		if ( ! $uri = $this->uri( $service, $endpoint, $params, $tokens ) ) {
			return false;
		}
		$payload = $this->encodeRequest( $service, $endpoint, $data );

		$context = $this->context( $service, $payload );

		$message = '';

		$previousErrorHandler = set_error_handler( function ( $c, $m ) use ( &$message ) {
			$message = $m;
			return false;
		} );
		try {
			// we want to try and handle the error gracefully so suppress messages on the call to file_get_contents
			if ( false === ( $body = @file_get_contents( $uri, null, $context ) ) ) {
				if ( ! $message ) {

					if ( isset( $http_response_header[0] ) ) {

						$message = $http_response_header[0];

					}
					if (!$message) {
						$oldContext = stream_context_get_options( stream_context_get_default() );
						stream_context_set_default( stream_context_get_options( $context ) );

						// see if we can get some sensible error info from headers.
						if ( $headers = get_headers( $uri, 1 ) ) {
							$message = $headers[0];
						} else {
							$message = "Failed to connect to '$uri', no more info available";
						}
						stream_context_set_default( $oldContext );

					}

				}
				throw new Exception( $message );
			}
			set_error_handler( $previousErrorHandler );

		} catch ( \Exception $e ) {
			set_error_handler( $previousErrorHandler );

			throw new Exception( $e->getMessage(), $e->getCode() );
		}

		return $this->decodeResponse( $service, $endpoint, $body );
	}

	/**
	 * Given an array of headers return a map of [ header-name => value ]
	 *
	 * @param array $headers
	 *
	 * @return array
	 */
	protected function parseHTTPResponseHeaders( $headers ) {
		$head = array();
		foreach ( $headers as $k => $v ) {
			$t = explode( ':', $v, 2 );
			if ( isset( $t[1] ) ) {
				$head[ trim( $t[0] ) ] = trim( $t[1] );
			} else {
				$head[] = $v;
				if ( preg_match( "#HTTP/[0-9\.]+\s+([0-9]+)#", $v, $out ) ) {
					$head['reponse_code'] = intval( $out[1] );
				}
			}
		}

		return $head;
	}

	/**
	 * Build a uri replacing tokens with key => value pairs from data
	 *
	 * @param string $service  generally a ServiceABC constant
	 * @param string $endpoint generally an EndpointABC constant
	 * @param array|\ArrayAccess $params   encoded as query string, values are expected already to be url encoded correctly
	 * @param array  $tokens   additional to replace in uri
	 *
	 * @return String
	 */
	protected function uri( $service, $endpoint, $params = [], $tokens = [] ) {
		$uri = $this->option( $this->option( $this->option( 'endpoints' ), $service ), $endpoint );

		// replace path tokens here

		$tokens = array_merge(
			[
				'endpoint' => $endpoint,
				'core'     => $this->core(),
			],
			$tokens
		);
		// replace tokens in uri
		foreach ( $tokens as $token => $value ) {
			$uri = str_replace( $this->token( $token ), urlencode( $value ), $uri );
		}
		$uri = $this->appendQueryParams( $uri, $params );

		return "$uri";
	}

	/**
	 * Append params as a query string to the uri.
	 *
	 * @param $toURI
	 * @param $params
	 *
	 * @return string
	 */
	protected function appendQueryParams( $toURI, $params ) {
		if ( $qs = $this->buildQueryString( $params ) ) {
			if ( false === strpos( $toURI, '?' ) ) {
				$toURI .= "?$qs";
			} else {
				$toURI .= "&$qs";
			}
		}

		return $toURI;
	}

	/**
	 * Build query string from params, values are expected to already be correctly urlencoded.
	 *
	 * @param $params
	 *
	 * @return string
	 */
	protected function buildQueryString( $params ) {
		// build query string from params, values are expected already to be url encoded
		$qs = '';
		foreach ( $params ?: [] as $name => $value ) {
			$qs .= "&$name=$value";
		}

		return substr( $qs, 1 );
	}

	/**
	 * Return a context suitable for use by php file functions over http. If payload is provided then this will
	 * be sent as the request body along with the accept-type for the service/endpoint.
	 *
	 * @param string $service        e.g. self.ServiceSOLR
	 * @param string $payload        will be sent as the request body
	 * @param string $endpoint       e.g. self.EndpointQuery
	 * @param array  $contextOptions (passed to context create)
	 *
	 * @return resource
	 */
	protected function context( $service, $payload = null, $endpoint = '', $contextOptions = [] ) {
		$options = array_merge_recursive(
			$this->option( 'context_options' )[ $service ] ?: [],
			$contextOptions ?: []
		);
		if ( $payload ) {
			$options['http']['content'] = $payload;
		}
		$context = stream_context_create( $options );

		return $context;
	}

	/**
	 * Given a raw response return something sensible given the encoding defined for the environment.
	 *
	 * @param string $service  responding
	 * @param string $endpoint responding
	 * @param string $responseBody
	 *
	 * @return mixed e.g. an array from json_decode
	 */
	protected function decodeResponse( $service, $endpoint = '', $responseBody ) {
		$method = $this->option( $this->option( 'encoding' ), $service ) . 'Decode';

		return $this->decode( $responseBody );
	}

	/**
	 * Encode request data to something the service at the other end can understand, e.g. json. Set the correct headers for the service etc
	 *
	 * @param string $service  being called
	 * @param string $endpoint being called
	 * @param mixed  $requestData
	 *
	 * @return string
	 */
	protected function encodeRequest( $service, $endpoint = '', $requestData ) {
		$method = $this->option( $this->option( 'encoding' ), $service ) . 'Encode';

		return $this->encode( $requestData );
	}

}