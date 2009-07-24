<?php

class Splitter_SocketTest extends PHPUnit_Framework_TestCase {

	public function testSuccessConnection() {
		$socket = new Splitter_Socket('127.0.0.1', 80);
		$this->assertTrue($socket instanceof Splitter_Socket);
	}

	public function testFailedConnection() {
		$this->setExpectedException('Splitter_Socket_Exception');
		$socket = new Splitter_Socket('the-very-non-existing-hostname', 21);
	}
}
