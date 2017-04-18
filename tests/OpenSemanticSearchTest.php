<?php
use OpenSemanticSearch\Interfaces\IndexInterface;
use OpenSemanticSearch\Extensions\MetaDataExtension;
use OpenSemanticSearch\Interfaces\SearchInterface;

class OpenSemanticSearchTest extends FunctionalTest {

	// this will be set to the path where test assets are copied
	private $testAssetsDir;

	// set to one entry per test file copied where an entry is a path to the file
	private $testFilePaths = [];

	const CurrentEnvironment = SS_ENVIRONMENT_TYPE;

	const TestPath = 'oss-test';

	// wait this long between adding a file and testing it exists, longer periods will slow down tests
	const IndexWait = 10;

	/**
	 * Sets up the environment and services ready for testing.
	 */
	public function setUp() {
		$this->copyTestFiles();
		parent::setUp();
	}

	/**
	 * @return bool
	 */
	protected function copyTestFiles() {
		$testDir = Controller::join_links(ASSETS_PATH, self::TestPath);
		if (!is_dir($testDir)) {
			if (is_file($testDir)) {
				unlink($testDir);
			}
			Filesystem::makeFolder($testDir);
		}
		if (!$this->testAssetsDir = realpath($testDir)) {
			die("Failed to create $testDir");
		}

		$sourcePath = Controller::join_links(__DIR__, 'files');

		$itr = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(
				$sourcePath,
				RecursiveDirectoryIterator::CURRENT_AS_PATHNAME | RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::FOLLOW_SYMLINKS
			)
		);

		$sync = false;
		/**
		 * @var string      $sourcePathName
		 * @var SplFileInfo $fileInfo
		 */
		foreach ($itr as $sourcePathName) {
			$filePathName = substr($sourcePathName, strlen($sourcePath));

			$destPathName = Controller::join_links($this->testAssetsDir, $filePathName);

			$this->testFilePaths[] = $destPathName;

			if (!is_file($destPathName)) {
				// only copy if doesn't exist
				copy( $sourcePathName, $destPathName );
				$sync = true;
			}
		}
		if ($sync) {
			Filesystem::sync();
		}

		return true;
	}

	/**
	 * Delete files and directories created by tests
	 */
	public function tearDownOnce() {
		Filesystem::removeFolder($this->testAssetsDir);
		Filesystem::sync();
		parent::tearDown();
	}

	/**
	 * Return a configured instance of the service which supports searching
	 *
	 * @return SearchInterface
	 */
	protected function searchService() {
		static $service;
		if ( ! $service ) {
			$service = \Injector::inst()->create( SearchInterface::ServiceName);
		}

		return $service;
	}

	/**
	 * Return a configured instance of the service which adds/removes things from the Search index.
	 * @return IndexInterface
	 */
	protected function indexingService() {
		static $service;
		if ( ! $service ) {
			$service = \Injector::inst()->create( IndexInterface::ServiceName);
		}

		return $service;
	}

	/**
	 * Return a configured instance of the service which supports retrieving structured information from the Search service
	 * @return mixed
	 */
	protected function metaDataService() {
		static $service;
		if ( ! $service ) {
			$service = \Injector::inst()->create( MetaDataInterface::ServiceName );
		}

		return $service;

	}

	public function testValidFileIsValid() {
		$this->assertNotEmpty($this->indexingService()->relativePath( $this->testFilePaths[0]), "That valid file is valid");
	}

	public function testUnsafeFilesAreInvalid() {
		$this->assertEmpty(
			$this->indexingService()->relativePath(BASE_PATH),
			"That web root is invalid path"
		);
		$this->assertEmpty(
			$this->indexingService()->relativePath(DIRECTORY_SEPARATOR),
			"That server root is invalid path"
		);
		$this->assertEmpty(
			$this->indexingService()->relativePath( __DIR__ . DIRECTORY_SEPARATOR . 'files/' . basename( $this->testFilePaths[0])),
			"That existing file not in safe path is invalid path"
		);
		$this->assertEmpty(
			$this->indexingService()->relativePath(TEMP_FOLDER),
			"That valid path to directory outside of web root is invalid path"
		);

		$invalidFile = Controller::join_links(TEMP_FOLDER, basename( $this->testFilePaths[0]));
		copy( $this->testFilePaths[0], $invalidFile);
		$this->assertEmpty(
			$this->indexingService()->relativePath($invalidFile),
			"That path to existing file outside of web root is invalid path"
		);
		unlink($invalidFile);
	}

	public function testAddFile() {
 		$this->assertTrue($this->indexingService()->addFile( $this->testFilePaths[0]), "That adding a file works");
		sleep(self::IndexWait);
		$this->assertFound($this->searchService()->findFile( $this->testFilePaths[0]), "That file exists in index");
	}

	public function testRemovePath() {
		$this->assertTrue($this->indexingService()->removePath( $this->testFilePaths[0]), "That removing a file works");
		sleep(self::IndexWait);
		$this->assertNotFound($this->searchService()->findFile( $this->testFilePaths[0]), "That file doesn't exist in index after removal");
	}

	public function testAddingDirectory() {
		$this->assertTrue($this->indexingService()->addDirectory($this->testAssetsDir), "That adding directory $this->testAssetsDir works");
		sleep(self::IndexWait);

		for ($i = 0; $i < 2; $i++) {
			$path = $this->testFilePaths[ $i];

			$this->assertFound( $this->searchService()->findFile( $path ), "That file $path exists in index after adding directory" );
			$this->assertTrue( $this->indexingService()->removePath( $path ), "That removing file $path works after adding its directory" );
			$this->assertNotFound( $this->searchService()->findFile( $path ), "That file $path doesn't exist in index after removal of file (not directory)" );
		}
	}

	public function testRemoveDirectory() {
		$this->assertTrue($this->indexingService()->addDirectory( $this->testAssetsDir), "That adding a directory $this->testAssetsDir works");
		$this->assertTrue( $this->indexingService()->removePath( $this->testAssetsDir ), "That removing directory $this->testAssetsDir works after adding its directory" );

		for ( $i = 0; $i < 2; $i ++ ) {
			$path = $this->testFilePaths[ $i ];

			$this->assertNotFound( $this->searchService()->findFile( $path ), "That file $path doesn't exist in index after removal of directory" );
		}

	}

	public function testSearchContent() {
		$this->assertTrue($this->indexingService()->addDirectory($this->testAssetsDir), "That adding a directory works");
		sleep(self::IndexWait);

		$results = $this->searchService()->search('apache');
		$this->assertDOSContains(array('Filename' => $this->testFilePaths[0]), $results);

		$results = $this->searchService()->search([ 'content' => 'passport']);
		$this->assertDOSContains(array('Filename' => $this->testFilePaths[1]), $results);
	}

	/**
	 * Test that requesting information be populated on a File object from Solr (OSSAuthor etc) works
	 */
	public function testUpdateFileFromIndex() {
		$path = $this->testFilePaths[0] ;
		$this->assertTrue( $this->indexingService()->addFile( $path ), "That adding file $path works" );
		sleep( self::IndexWait );

		$searchResults = $this->searchService()->findFile( $path );
		$this->assertFound( $searchResults, "That file $path exists in index" );

		$file = File::get()->filter('Filename', $path)->first();

		$this->assertNotNull( $file, "That file $path exists in index");
		$this->assertEmpty($file->{\OpenSemanticSearch\Extensions\MetaDataExtension::AuthorField}, "That no author exists for file");

		$this->assertTrue($this->metaDataService()->syncFileInfo($file), "That syncFileInfo says it worked");
		$this->assert($file->{MetaDataExtension::AuthorField} == 'John Smith');
	}

	protected function assertFound($fileList, $message = "Document was found in index") {
		$this->assertTrue($fileList->count() === 1, $message);
	}

	protected function assertNotFound($fileList, $message = "Document was not found in index") {
		$this->assertTrue($fileList->count() === 0, $message);
	}
}