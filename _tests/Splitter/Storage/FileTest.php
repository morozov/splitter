<?php

class Splitter_Storage_FileTest extends PHPUnit_Framework_TestCase {

	private
		$dir = '/tmp/splitter-test',
		$filename = 'test.txt',
		$contents = 'Lorem ipsum';

	public function setUp() {
		$this->cleanup();
		mkdir($this->dir);
	}

	public function tearDown() {
		$this->cleanup();
	}

	protected function cleanup() {
		if (file_exists($this->dir)) {
			if (is_file($this->dir)) {
				unlink($this->dir);
			} elseif (is_dir($this->dir)) {
				$this->rmdir($this->dir);
			}
		}
	}

	public function testAllowedFilename() {
		try {
			$storage = new Splitter_Storage_File(array('dir' => '.'));
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

		$storage = $this->_createStorage($this->dir, $this->filename);
		$storage->write($this->contents);
		$storage->write($this->contents);
		$storage->commit();

		$this->assertStringEqualsFile($path, str_repeat($this->contents, 2));
	}

	public function testSetFilenameAfterWritten() {
		$this->setExpectedException('Splitter_Storage_Exception');
		$storage = $this->_createStorage($this->dir, $this->filename);
		$storage->write($this->contents);
		$storage->setFileName('dummy');
	}

	public function testWriteBeforeFilenameIsSet() {
		$this->setExpectedException('Splitter_Storage_Exception');
		$path = $this->dir . '/testWriteBeforeFilenameIsSet';
		$storage = $this->_createStorage($path, null);
		$storage->write($this->contents);
	}

	public function testMkdirFileExists() {
		$this->setExpectedException('Splitter_Storage_Exception');
		$dir = $this->dir . '/dummy';
		touch($dir);
		$storage = $this->_createStorage($dir, $this->filename);
		$storage->write($this->contents);
	}

	public function testMkdirRecursive() {
		$dir = $this->dir . '/dummy1/dummy2';
		$path = $dir . '/' . $this->filename;
		$storage = $this->_createStorage($dir, $this->filename);
		$storage->write($this->contents);
		$storage->commit();
		$this->assertStringEqualsFile($path, $this->contents);
	}

	public function testSaveToDirectory() {
		$this->setExpectedException('Splitter_Storage_Exception');
		$dir = $this->dir . '/dummy';
		mkdir($dir);
		$storage = $this->_createStorage($this->dir, 'dummy');
		$storage->write($this->contents);
	}

	public function testNonAsciiFilenameOnWindows() {
		if (Application::isWindows()) {
			$utf8_filename = 'Имя файла по-русски.txt';
			$windows1251_filename = mb_convert_encoding($utf8_filename, 'windows-1251', 'utf-8');
			$storage = $this->_createStorage($this->dir, $utf8_filename);
			$storage->write($this->contents);
			$this->assertFileExists($this->dir . '/' . $windows1251_filename);
		}
	}

	protected function _createStorage($dir, $filename) {
		$storage = new Splitter_Storage_File(array('dir' =>  $dir));
		$storage->setFilename($filename);
		return $storage;
	}

	protected function _testInvalidFilename($filename) {
		$this->setExpectedException('Splitter_Storage_Exception');
		$storage = new Splitter_Storage_File(array('dir' => '.'));
		$storage->setFilename($filename);
	}

	protected function rmdir($path) {
		$dir = new RecursiveDirectoryIterator($path);
		foreach (new RecursiveIteratorIterator($dir) as $file) {
			unlink($file);
		}
		foreach ($dir as $subdir) {
			if (!@rmdir($subdir)) {
				$this->rmdir($subdir);
			}
		}
		rmdir($path);
	}
}
