<?php
namespace OpenSemanticSearch;

use Controller;

/**
 * Service represents an Open Semantic Search service which consists of two parts, the OSS provided service which adds, removes and updates
 * files and urls to the index, and the backend indexing service which is Solr.
 *
 * NB Only the SolrGet service is working as of 2017/03/05
 *
 * @package OpenSemanticSearch
 */
abstract class Service extends \Object implements ServiceInterface {
	// ctor provided options, may be combined with e.g. options passed as a parameter or config options.
	protected $options;
	// current configured environment, set in ctor, may override config.environment if set
	protected $env;

	// tokens in uris start with this
	private static $token_start = '{';

	// tokens in uris end with this
	private static $token_end = '}';

	// what configuration to use
	private static $environment = SS_ENVIRONMENT_TYPE;

	// override to supply name of Injector service or class to use for this service.
	private static $service_name = '';

	/**
	 * Map of encodings to return depending on the environment, the encoding will eventually be called through as e.g. 'jsonEncode' or 'xmlDecode'
	 * on a concrete derived class.
	 *
	 * @var array [ env => [ service => encoding ]]
	 */
	private static $encoding = [
		'*' => [
			'*' => 'json',
		],
	];

	/**
	 * Map from a local (web-root based) path to the equivalent path the indexing engine should use (relative to filesystem root).
	 * Local paths (keys) should be relative to web root and with leading and following slashes, remote paths relative to filesystem root and also
	 * bracketed by slashes. You can restrict paths to index by only listing paths to index here as key.
	 *
	 * @var array
	 */
	private static $path_map = [
	];

	// map an environment to a core 'name', shared between derived services
	private static $core = [
	];

	// configure url paths to services depending on environment
	private static $endpoints = [
	];

	/**
	 * Map of different implementation specific (e.g. Solarium client) options for
	 * a given SS environment. These can be referenced in code when client options are required.
	 *
	 * @var array
	 */
	private static $library_options = [
		'*' => [
			#   'translate' => [                // could be converted by e.g. arr_to_btf if required
			#       self::Option1 => true,      // to map local generic settings to library specific
			#       self::Option2 => 'text'
			#   ],
			#   'bitfield' => LIBRARY_OPT1 | LIBRARY_OPT2
		],
	];

	/**
	 * Service constructor.
	 *
	 * @param null   $options
	 * @param string $env override the configured environment, e.g. for testing or selecting a different core/service
	 */
	public function __construct( $options = null, $env = '' ) {
		$this->options = $options;
		$this->env     = $this->env( $env );
		parent::__construct();
	}

	/**
	 * Return a configured instance of the service via config.service_name, self.ServiceName or the called class name.
	 *
	 * @param mixed  $options
	 * @param string $env
	 *
	 * @return \OpenSemanticSearch\IndexInterface|\OpenSemanticSearch\SearchInterface
	 */
	public static function get( $options = null, $env = '' ) {
		$serviceName = static::config()->get( 'service_name' )
			?: static::ServiceName
				?: get_called_class();

		return \Injector::inst()->create( $serviceName, $options, $env );
	}

	/**
	 * Alternate way to call a method via Service interface, e.g. if calling form a QueuedServiceTask.
	 *
	 * @param null $params
	 *
	 * @return mixed
	 */
	public function execute( $params = null) {
		return call_user_func_array( [ $this, $params ], func_get_args());
	}

	/**
	 * Convenience method if ::class is not available or not understood
	 *
	 * @return string
	 */
	public static function class_name() {
		return __CLASS__;
	}

	/**
	 * Returns the core name for the current SS environment
	 *
	 * @return string e.g. 'core1'
	 */
	public function core() {
		return $this->option( 'core' );
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
	 * Should be provided in a derived class, e.g via the json trait
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	abstract public function decode( $data );

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

	/**
	 * Should be provided in a derived class, e.g via the json trait
	 */
	abstract public function encode( $data );

	/**
	 * Returns a built token for a name ready to replace in a string.
	 *
	 * @param string $token e.g. 'endpoint'
	 *
	 * @return string e.g. '{endpoint}'
	 */
	protected function token( $token ) {
		return $this->config()->get( 'token_start' ) . $token . $this->config()->get( 'token_end' );
	}

	/**
	 * Returns the configured environment, by default this with be config.environment, otherwise whatever is passed in e.g. 'dev', 'test', 'live'
	 *
	 * @param $env
	 *
	 * @return string
	 */
	protected static function env( $env = '' ) {
		return $env ? $env : static::config()->get( 'environment' );
	}

	/**
	 * Turn what might be an absolute path, a path relative to assets or a path relative to web root (starting with '/')
	 * into a path relative to web root. The path must exist as identified by 'realpath'.
	 *
	 * @param string $path
	 *
	 * @return string|bool path relative to web root or empty string if doesn't exist or false if it is not Safe.
	 */
	public function relativePath( $path ) {
		if ( substr( $path, 0, 1 ) == DIRECTORY_SEPARATOR ) {
			// if absolute then must start with web root or first part of path must be under web root
			if ( ! substr( $path, 0, strlen( BASE_PATH ) ) == BASE_PATH ) {
				$pathStart = substr( $path, strlen( BASE_PATH ) );

				if ( ! realpath( Controller::join_links( BASE_PATH, $pathStart ) ) ) {
					$path = '';
				}
			} else {
				$path = substr( $path, strlen( BASE_PATH ) );
			}
		} else {
			$path = DIRECTORY_SEPARATOR . $path;
		}

		return $this->isSafe( $path ) ? $path : '';

	}

	/**
	 * Check that a path is safe to index files from by comparing the path to paths from path_map for the environment.
	 *
	 * - if absolute the path must match up to the first part of one of the paths if absolute (e.g. '/var/www/htdocs/assets'),
	 * - if relative then the first part of the path should match a path map path (e.g. 'assets/')
	 *
	 * @param $path
	 *
	 * @return bool
	 */
	public function isSafe( $path ) {
		if ( $path ) {
			$safePaths = array_keys( $this->option( 'path_map' ) );

			foreach ( $safePaths as $safePath ) {
				if ( substr( $safePath, 0, 1 ) == DIRECTORY_SEPARATOR ) {
					// absolute path match entire path

					if ( $safePath == substr( $path, 0, strlen( $safePath ) ) ) {
						return true;
					}

				} else {
					// relative path to web root, match first part only
					$firstPart = current( explode( DIRECTORY_SEPARATOR, trim( $path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR ) );

					if ( $firstPart == $safePath ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Map a local path to a search service path:
	 *  given a path map for the environment of [ '/assets' => '/mnt/files/assets' ] then:
	 *
	 *  -   'documents/2017/' -> '/mnt/files/assets/documents/2017/'
	 *  -   '/document-library/2017/good/paper.pdf' -> '/mnt/files/document-library/2017/good/paper.pdf'
	 *
	 * @param string $localPath relative to assets path, e.g. 'documents/2017/good/paper.pdf' or relative to web root e.g '/document-library/fred.pdf'
	 *
	 * @return string e.g. '/mnt/files/assets/documents/2017/good/paper.pdf' or '' if no matching local path
	 */
	public function localToRemotePath( $localPath ) {
		$localPath = parse_url( $localPath, PHP_URL_PATH );

		if ( $path = $this->relativePath( $localPath ) ) {
			if ( $map = $this->option( 'path_map' ) ) {
				// e.g. local = '/assets/', remote = '/mnt/files/assets/'
				foreach ( $map as $local => $remote ) {

					// match e.g. '/assets/' local map to '/assets/' part of '/assets/documents/...'
					if ( $local == substr( $path, 0, strlen( $local ) ) ) {

						// strip off the local path and append the rest to the remote path
						return Controller::join_links( $remote, substr( $path, strlen( $local ) ) );
					}
				}
			}
		}

		return '';
	}

	/**
	 * Return a local path for a remote path which may be a unc path. Checks if it's safe locally. Path is trimmed from leading '/' so can be filtered
	 * directly as a File model Filename.
	 *
	 * @param string $remotePath as indexed by the indexing service.
	 *
	 * @return string of local path or '' if not found/invalid
	 */
	public function remoteToLocalPath( $remotePath ) {
		$path = parse_url( $remotePath, PHP_URL_PATH );
		if ( $map = $this->option( 'path_map' ) ) {
			// e.g. local = '/assets/', remote = '/mount/files/assets/'
			foreach ( $map as $local => $remote ) {

				// match e.g. '/mount/files/assets/' remote path to '/assets/'
				if ( $remote == substr( $path, 0, strlen( $remote ) ) ) {

					// strip off the remote path and append the rest to the local path
					$localPath = Controller::join_links( $local, substr( $path, strlen( $remote ) ) );
					if ( $this->isSafe( $localPath ) ) {
						// only return if it's safe to reference
						return ltrim( $localPath, '/' );
					}
				}
			}
		}

		return '';
	}

	/**
	 * Lookup a map by key using fnmatch to compare key to target value so simple wildcards can be used
	 *
	 * TODO allow config to be e.g. 'service.endpoint.data' (dot-encoded)
	 *
	 * @param string|array $config either name of configuration variable, or data to search
	 * @param string       $key    to match, e.g. 'service', if not supplied defaults to env()
	 *
	 * @return mixed
	 */
	public function option( $config, $key = '' ) {
		$data = ( is_array( $config ) ? $config : ( $this->config()->get( $config ) ?: [] ) ) ?: [];
		$key  = $key ?: $this->env;

		foreach ( $data as $match => $value ) {
			if ( fnmatch( $match, $key ) ) {
				return $value;
			}
		}
	}

}