<?php

class Splitter_Response_WebTest extends PHPUnit_Framework_TestCase {

	protected $response;

	public function setUp() {
		ob_start();
		$this->response = new Splitter_Response_Web;
	}

	public function testHtml() {
		unset($this->response);
		$contents = ob_get_clean();
		$this->assertContains('<html'/* it contains attributes */, $contents);
		$this->assertContains('</html>', $contents);
	}

	public function testCallExistingMethod() {
		$date = date(Splitter_Response_Abstract::TIME_FORMAT);
		$this->response->log('message1');
		$this->response->log('message2', 'type2');
		unset($this->response);
		$contents = ob_get_clean();
		$this->assertContains(Splitter_Response_Web::CALLEE . '.log("message1", "' . $date . '")', $contents);
		$this->assertContains(Splitter_Response_Web::CALLEE . '.log("message2", "' . $date . '", "type2")', $contents);
	}

	public function testCallNonExistingMethod() {
		$this->response->dummy();
		unset($this->response);
		$this->assertContains(Splitter_Response_Web::CALLEE . '.dummy()', ob_get_clean());
	}
}
