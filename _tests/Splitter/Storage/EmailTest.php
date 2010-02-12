<?php

require_once 'PHPUnit/Framework/TestCase.php';

class Splitter_Storage_EmailTest extends PHPUnit_Framework_TestCase {

	public function testCreationUsingValidEmail() {
		try {
			$storage = new Splitter_Storage_Email(array('to' => 'a-valid-email-address@somedomain.com'));
		} catch (Splitter_Storage_Exception $e) {
			$this->fail($e->getMessage());
		}
		$this->assertTrue(!empty($storage));
	}

	public function testCreationUsingInvalidEmail() {
		$this->setExpectedException('Splitter_Storage_Exception');
		$storage = new Splitter_Storage_Email(array('to' => 'an-invalid-email-address'));
	}
}
