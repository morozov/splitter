<?php

class Splitter_StorageTest extends PHPUnit_Framework_TestCase {

	public function testConstruction() {
		$storage = new Splitter_Storage('file', 1048576, array('dir' => '.'));
	}
}
