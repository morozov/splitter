<?php

require_once dirname(__FILE__) . '/../TestHelper.php';

class Splitter_SocketTest extends PHPUnit_Framework_TestCase {

	public function testSuccessConnection() {
		$socket = new Splitter_Socket('127.0.0.1', 80);
		$this->assertTrue($socket instanceof Splitter_Socket);
	}

	public function testFailedConnection() {
		try {
			$socket = new Splitter_Socket('the-very-unreachable-host', 1111);
		} catch (Splitter_Socket_Exception $e) {
			$this->assertTrue(mb_check_encoding($e->getMessage(), 'utf-8'));
			return;
		}
		$this->fail('Expected exception not caught');
	}
}
