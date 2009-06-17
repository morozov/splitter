<?php

class Splitter_Storage_FileTest extends PHPUnit_Framework_TestCase {

	public function testAllowedFilename() {
		try {
			$storage = new Splitter_Storage_File('.');
			$storage->setFilename('image.png');
		} catch (Splitter_Storage_Exception $e) {
			$this->fail($e->getMessage());
		}
		$this->assertEquals($storage->getFilename(), 'image.png');
	}

	public function testDotHtFilename() {
		$this->_testInvalidFilename('.htaccess');
	}

	public function testFilenameWithPath() {
		$this->_testInvalidFilename('/path/to/file');
	}

	protected function _testInvalidFilename($filename) {
		$this->setExpectedException('Splitter_Storage_Exception');
		$storage = new Splitter_Storage_File('.');
		$storage->setFilename($filename);
	}
}
