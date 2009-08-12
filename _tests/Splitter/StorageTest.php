<?php

require_once dirname(__FILE__) . '/../TestHelper.php';

class Splitter_StorageTest extends PHPUnit_Framework_TestCase {

	public function testCrc32Charset() {
		$storage = new Splitter_Storage('file', 1, array(
			'dir' => '.',
			'crc32charset' => 'windows-1251',
		));
	}
}
