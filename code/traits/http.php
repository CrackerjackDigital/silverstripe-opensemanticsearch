<?php

namespace OpenSemanticSearch\Traits;

use Modular\Debugger;
use Modular\Interfaces\HTTP as HTTPInterface;
use OpenSemanticSearch\Exceptions\Exception;
use OpenSemanticSearch\Results\ErrorResult;

/**
 * http simple http request handling using php file methods and stream contexts
 *
 * @package OpenSemanticSearch
 */
trait http {
	/**
	 * Check response code is in the '2xx' range.
	 *
	 * @param int|string $code
	 *
	 * @return bool true if in range, false otherwise
	 */
	public function responseCodeIsOK( $code ) {
		return fnmatch( '2*', $code );
	}

	/**
	 * Return a decoded response a request to the service endpoint passing params on url and data in request body if provided.
	 *
	 * @param string             $service
	 * @param string             $endpoint
	 * @param array|\ArrayAccess $params will be added to uri as query string
	 * @param array              $data   to send as the request payload (will become a POST if passed)
	 * @param array              $tokens additional tokens to substitute into the uri
	 *
	 * @return mixed
	 * @throws \OpenSemanticSearch\Exceptions\Exception
	 */
	public function request( $service, $endpoint, $params = [], $data = null, $tokens = [] ) {

		if ( ! $uri = $this->uri( $service, $endpoint, $params, $tokens ) ) {
			return false;
		}
		// make a stream context to use
		$context = $this->context( $service, $endpoint, $data );

		$message = '';
		$code    = null;

		$previousErrorHandler = Debugger::set_error_exception( $message, $code );
		try {
			$responseBody = false;
			$e            = null;
			try {
				$responseBody = @file_get_contents( $uri, null, $context );

				$result = $this->decodeResponse( $service, $endpoint, $responseBody, $this->parseHTTPResponseHeaders( $http_response_header ) );

			} catch ( \Exception $e ) {
				// message and code will have been set by exception error handler

			}
			if ( false === $responseBody ) {
				// handle no response at all, check if exception error handler got a message first
				if ( ! $message ) {

					if ( isset( $http_response_header[0] ) ) {

						$message = $http_response_header[0];

					}
					if ( ! $message ) {
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
				throw new Exception( $message, $code, $e );
			}

			set_error_handler( $previousErrorHandler );

		} catch ( \Exception $e ) {
			set_error_handler( $previousErrorHandler );
			$result = new ErrorResult( $e->getMessage() );
		}

		return $result;
	}

	/**
	 *
	 *
	 * @param string $url      to rewrite
	 *
	 * @param array  $rewrite  set these values on the output
	 * @param array  $defaults set these values on the output if missing
	 *
	 * @return string
	 */
	public function rebuildURL(
		$url,
		$rewrite = [],
		$defaults = [
			HTTPInterface::PartScheme   => 'https',
			HTTPInterface::PartUser     => '',
			HTTPInterface::PartPassword => '',
			HTTPInterface::PartHost     => '',
			HTTPInterface::PartPort     => '80',
			HTTPInterface::PartPath     => '',
			HTTPInterface::PartQuery    => '',
			HTTPInterface::PartFragment => '',
		]
	) {

		static $seperators = [
			HTTPInterface::PartScheme   => '://',
			HTTPInterface::PartUser     => ':',
			HTTPInterface::PartPassword => '@',
			HTTPInterface::PartHost     => ':',
			HTTPInterface::PartPort     => '/',
			HTTPInterface::PartPath     => '?',
			HTTPInterface::PartQuery    => '#',
			HTTPInterface::PartFragment => '',
		];

		$parsed = array_merge(
			parse_url( $url ),
			$rewrite
		);

		$out  = '';
		$last = '';

		foreach ( $seperators as $key => $seperator ) {

			if ( array_key_exists( $key, $parsed ) ) {
				if (array_key_exists( $key, $rewrite)) {
					$value = $rewrite[$key];
				} else {
					$value = $parsed[$key];
				}
				// a null rewrite means don't add this value in
				if (!is_null($value)) {
					// value for this key was parsed out, add it back and postfix
					$out .= $parsed[ $key ] . $seperator;
				}
				$last = $parsed[ $key ];
			} elseif ( ! empty( $defaults[ $key ] ) && ! empty( $last ) ) {
				// there is a default use that and append prefix
				$out  .= $defaults[ $key ] . $seperator;
				$last = '';
			} elseif ( $last ) {
//				$out .= $defaults[$lastKey];
				$last = '';
			} else {
				$last = '';
			}
			$lastKey = $key;
		}
		// trim off any trailing postfixes
		foreach ( $seperators as $seperator ) {
			$out = rtrim( $out, $seperator );
		}

		return $out;
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
					$head['ResponseCode'] = intval( $out[1] );
				}
			}
		}

		return $head;
	}

	/**
	 * Build a uri replacing tokens with key => value pairs from data
	 *
	 * @param string             $service  generally a ServiceABC constant
	 * @param string             $endpoint generally an EndpointABC constant
	 * @param array|\ArrayAccess $params   encoded as query string, values are expected already to be url encoded correctly
	 * @param array              $tokens   additional to replace in uri
	 *
	 * @param bool               $encode
	 *
	 * @return String
	 */
	protected function uri( $service, $endpoint, $params = [], $tokens = [], $encode = HTTPInterface::QueryStringEncode ) {
		if (!$uri = $this->option( $this->option( $this->option( 'endpoints' ), $service ), $endpoint )) {
			throw new Exception("No uri configured for environment '$this->env'");
		}

		// add default tokens
		$tokens = array_merge(
			[
				'endpoint' => $endpoint,
				'core'     => $this->core(),
			],
			$tokens
		);
		$uri    = $this->replaceTokens( $uri, $tokens );
		$uri    = $this->appendQueryParams( $uri, $params, $encode );

		return $uri;
	}

	/**
	 * Replace tokens in uri with urlencoded values
	 *
	 * @param string $uri
	 * @param array  $tokens
	 *
	 * @return mixed
	 */
	protected function replaceTokens( $uri, $tokens = [] ) {
		// replace tokens in uri with urlencoded values
		foreach ( $tokens as $token => $value ) {
			$uri = str_replace( $this->token( $token ), urlencode( $value ), $uri );
		}

		return $uri;
	}

	/**
	 * Append params as a query string to the uri.
	 *
	 * @param string $toURI
	 * @param array  $params
	 *
	 * @param string $encode
	 *
	 * @return string
	 */
	protected function appendQueryParams( $toURI, $params, $encode = HTTPInterface::QueryStringEncode ) {
		if ( $qs = $this->buildQueryString( $params, $encode ) ) {
			if ( false === strpos( $toURI, '?' ) ) {
				$toURI .= "?$qs";
			} else {
				$toURI .= "&$qs";
			}
		}

		return $toURI;
	}

	/**
	 * Build query string from params, values will be urlencoded.
	 *
	 * @param             $params
	 *
	 * @param string|bool $encode either method to call to encode parameters or boolean for rawurlencode (if true).
	 *
	 * @return string
	 */
	protected function buildQueryString( $params, $encode = HTTPInterface::QueryStringEncode ) {
		// build query string from params, values are expected already to be url encoded
		$qs = '';
		foreach ( $params ?: [] as $name => $value ) {
			if ( $encode ) {
				if ( method_exists( $this, $encode ) ) {
					$value = $this->$encode( $value );
				} elseif ( is_callable( $encode ) ) {
					$value = $encode( $value );
				} else {
					$encode = HTTPInterface::QueryStringEncode;
				}
			}
			$qs .= "&$name=" . $value;
		}

		return substr( $qs, 1 );
	}

	protected function encodeSpaces( $value ) {
		return str_replace(' ', '+', $value);
	}

	/**
	 * Return a context suitable for use by php file functions over http. If payload is provided then this will
	 * be sent as the request body along with the accept-type for the service/endpoint.
	 *
	 * @param string $service        e.g. self.ServiceSOLR
	 * @param string $endpoint       e.g. self.EndpointQuery
	 * @param null   $data           to encode into context, e.g. post body
	 * @param array  $contextOptions (passed to context create)
	 *
	 * @return resource
	 */
	protected function context( $service, $endpoint = '', $data = null, $contextOptions = []) {
		$options = array_merge_recursive(
			$this->option( 'context_options' )[ $service ] ?: [],
			$contextOptions ?: []
		);
		if ( $data ) {
			$options['http']['content'] = $this->encodeRequest( $endpoint, $endpoint, $data );
		}
		$context = stream_context_create( $options );

		return $context;
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
		return $this->encode( $requestData );
	}

}