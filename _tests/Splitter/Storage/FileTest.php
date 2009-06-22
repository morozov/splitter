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

		$storage = $this->_createStorage();;
		$storage->write($this->contents);
		// unset is important to close the storage
		unset($storage);

		$this->assertStringEqualsFile($path, $this->contents);
	}

	public function testSetFilenameAfterWritten() {
		$this->setExpectedException('Splitter_Storage_Exception');
		$storage = $this->_createStorage();
		$storage->write($this->contents);
		$storage->setFileName('dummy');
	}

	protected function _createStorage() {
		$storage = new Splitter_Storage_File($this->dir);
		$storage->setFilename($this->filename);
		return $storage;
	}

	protected function _testInvalidFilename($filename) {
		$this->setExpectedException('Splitter_Storage_Exception');
		$storage = new Splitter_Storage_File('.');
		$storage->setFilename($filename);
	}
}
