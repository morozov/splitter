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
		$this->response->info('dummy');
		unset($this->response);
		$contents = ob_get_clean();
		$this->assertContains(Splitter_Response_Web::CALLEE . '.trace("info",', $contents);
	}

	public function testCallNonExistingMethod() {
		$this->response->dummy();
		unset($this->response);
		$this->assertContains(Splitter_Response_Web::CALLEE . '.dummy()', ob_get_clean());
	}
}
