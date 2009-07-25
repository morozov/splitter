<?php

class Splitter_Response_CliTest extends PHPUnit_Framework_TestCase {

	protected $response;

	public function setUp() {
		ob_start();
		$this->response = new Splitter_Response_Cli;
	}

	public function testLog() {
		$date = date(Splitter_Response_Abstract::TIME_FORMAT);
		$this->response->log('message1');
		$this->response->log('message2', 'error');
		unset($this->response);
		$contents = ob_get_clean();
		$this->assertContains('  | ' . $date . ' | message1', $contents);
		$this->assertContains('! | ' . $date . ' | message2', $contents);
	}
}
