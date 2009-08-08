<?php

require_once dirname(__FILE__) . '/../../TestHelper.php';

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

	public function testNonAsciiContentsOnWindows() {
		$utf8_contents = 'Содержимое по-русски';
		$this->response->log($utf8_contents);
		unset($this->response);
		$contents = ob_get_clean();
		if ('WIN' == substr(PHP_OS, 0, 3)) {
			$oemcp_contents = mb_convert_encoding($utf8_contents, Splitter_Os_Windows::getOEMCPCharset(), 'utf-8');
			$this->assertContains($oemcp_contents, $contents);
		} else {
			$this->assertContains($utf8_contents, $contents);
		}
	}
}
