<?php

require_once dirname(__FILE__) . '/TestHelper.php';

if (!defined('PHPUnit_MAIN_METHOD')) {
	define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}

class AllTests {

	public static function main() {
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	public static function suite() {
		$suite = new PHPUnit_Framework_TestSuite('Splitter');
		$suite->addTestSuite('Splitter_Storage_CommonTest');
		$suite->addTestSuite('Splitter_Storage_EmailTest');
		$suite->addTestSuite('Splitter_Storage_FileTest');
		$suite->addTestSuite('Splitter_Storage_RamTest');
		$suite->addTestSuite('Splitter_StorageTest');
		$suite->addTestSuite('Splitter_SocketTest');

		$suite->addTestSuite('Splitter_Response_WebTest');

		$suite->addTestSuite('Splitter_Response_CliTest');
		//$suite->addTestSuite('Splitter_Service_Download_IntfTest');
		return $suite;
	}
}

if (PHPUnit_MAIN_METHOD == 'AllTests::main') {
	AllTests::main();
}
