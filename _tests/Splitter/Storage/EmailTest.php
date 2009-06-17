<?php

class Splitter_Storage_EmailTest extends PHPUnit_Framework_TestCase {

	public function testCreationUsingValidEmail() {
		try {
			$storage = new Splitter_Storage_Email('a-valid-email-address@somedomain.com');
		} catch (Splitter_Storage_Exception $e) {
			$this->fail($e->getMessage());
		}
		$this->assertTrue(!empty($storage));
		$this->assertType('Splitter_Storage_Email', $storage);
	}

	public function testCreationUsingInvalidEmail() {
		try {
			$storage = new Splitter_Storage_Email('an-invalid-email-address');
			$this->fail('Accepted an invalid e-mail address');
		} catch (Splitter_Storage_Exception $e) {
			// Good, it threw an exception
		}
	}
}
