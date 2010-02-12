<?php

require_once 'PHPUnit/Framework/TestCase.php';

class Splitter_Storage_RamTest extends PHPUnit_Framework_TestCase {

	public function testWriteThenGetContents() {
		$storage = new Splitter_Storage_Ram;
		$storage->write('foo');
		$storage->write('bar');
		$this->assertEquals('foobar', $storage->getContents());
	}
}
