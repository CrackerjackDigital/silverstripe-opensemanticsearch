<?php
namespace OpenSemanticSearch;

interface PathMappingInterface {
	/**
	 * Return a local (website-oriented) path for a remote (service-oriented) path.
	 *
	 * @param string $localPath
	 *
	 * @return string
	 */
	public function localToRemotePath($localPath);

	/**
	 * Return a remote (service-oriented) path for a local (website-oriented) path.
	 *
	 * @param string $remotePath
	 *
	 * @return string
	 */
	public function remoteToLocalPath($remotePath);

	/**
	 * Check if a local path is safe to index and/or include in web site front-end, search results etc.
	 *
	 * @param string $localPath
	 *
	 * @return bool
	 */
	public function isSafe($localPath);

	/**
	 * Turn what might be an absolute path, a path relative to assets or a path relative to web root (starting with '/')
	 * into a path relative to web root. The path must exist as identified by 'realpath'.
	 *
	 * @param string $path
	 *
	 * @return string|bool path relative to web root or empty string if doesn't exist or false if it is not Safe.
	 */
	public function relativePath($path);

}