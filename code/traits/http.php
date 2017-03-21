<?php
namespace OpenSemanticSearch;
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
	 * @param array  $params will be added to uri as query string
	 * @param array  $data   to send as the request payload (will become a POST if passed)
	 * @param array  $tokens additional tokens to substitute into the uri
	 *
	 * @return mixed
	 */
	protected function request( $service, $endpoint, $params = [], $data = null, $tokens = [] ) {
		if ( ! $uri = $this->uri( $service, $endpoint, $params, $tokens ) ) {
			return false;
		}
		$payload = $this->encodeRequest( $service, $endpoint, $data );

		$context = $this->context( $service, $payload );

		if ( false != ( $body = file_get_contents( $uri, null, $context ) ) ) {
			return $this->decodeResponse( $service, $endpoint, $body );
		}
	}

	/**
	 * Build a uri replacing tokens with key => value pairs from data
	 *
	 * @param string $service  generally a ServiceABC constant
	 * @param string $endpoint generally an EndpointABC constant
	 * @param array  $params   encoded as query string, values are expected already to be url encoded correctly
	 * @param array  $tokens   additional to replace in uri
	 *
	 * @return String
	 */
	protected function uri( $service, $endpoint, $params = [], $tokens = [] ) {
		$uri = $this->setting( $this->setting( $this->setting( 'endpoints' ), $service ), $endpoint );

		// replace path tokens here

		$tokens = array_merge(
			[
				'endpoint' => $endpoint,
				'core'     => $this->core()
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
			$this->setting( 'context_options' )[ $service ] ?: [],
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
		$method = $this->setting( $this->setting( 'encoding' ), $service ) . 'Decode';

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
		$method = $this->setting( $this->setting( 'encoding' ), $service ) . 'Encode';

		return $this->encode( $requestData );
	}

}