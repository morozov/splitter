<?php

class Splitter_Storage_FileTest extends PHPUnit_Framework_TestCase {

	private
		$dir = '.',
		$filename = 'test.txt',
		$contents = 'Lorem ipsum';

	public function setUp() {
		$this->dir = dirname(__FILE__) . '/tmp';
	}

	public function testAllowedFilename() {
		try {
			$storage = new Splitter_Storage_File('.');
			$storage->setFilename($this->filename);
		} catch (Splitter_Storage_Exception $e) {
			$this->fail($e->getMessage());
		}
		$this->assertEquals($storage->getFilename(), $this->filename);
	}

	public function testDotHtFilename() {
		$this->_testInvalidFilename('.htaccess');
	}

	public function testFilenameWithPath() {
		$this->_testInvalidFilename('/path/to/file');
	}

	public function testWriteToANewFile() {
		$path = $this->dir . '/' . $this->filename;

		if (file_exists($path) && !unlink($path)) {
			$this->fail('Unable to unlink an existing file');
		}

		$storage = $this->_createStorage($this->dir, $this->filename);
		$storage->write($this->contents);
		// unset is important to close the storage
		unset($storage);

		$this->assertStringEqualsFile($path, $this->contents);
	}

	public function testSetFilenameAfterWritten() {
		$this->setExpectedException('Splitter_Storage_Exception');
		$storage = $this->_createStorage($this->dir, $this->filename);
		$storage->write($this->contents);
		$storage->setFileName('dummy');
	}

	public function testMkdirFileExists() {
		$this->setExpectedException('Splitter_Storage_Exception');
		$dir = $this->dir . '/dummy';
		@rmdir($dir);
		@unlink($dir);
		touch($dir);
		$storage = $this->_createStorage($dir, $this->filename);
		$storage->write($this->contents);
	}

	public function testSaveToDirectory() {
		$this->setExpectedException('Splitter_Storage_Exception');
		$dir = $this->dir . '/dummy';
		@rmdir($dir);
		@unlink($dir);
		mkdir($dir);
		$storage = $this->_createStorage($this->dir, 'dummy');
		$storage->write($this->contents);
	}

	protected function _createStorage($dir, $filename) {
		$storage = new Splitter_Storage_File($dir);
		$storage->setFilename($filename);
		return $storage;
	}

	protected function _testInvalidFilename($filename) {
		$this->setExpectedException('Splitter_Storage_Exception');
		$storage = new Splitter_Storage_File('.');
		$storage->setFilename($filename);
	}
}
