<?php

require_once dirname(__FILE__) . '/bootstrap.php';

if (!defined('PHPUnit_MAIN_METHOD')) {
	define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}

class AllTests {

	public static function main() {
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	public static function suite() {
		$suite = new PHPUnit_Framework_TestSuite('Splitter');
		$suite->addTestSuite('Splitter_Storage_EmailTest');
		$suite->addTestSuite('Splitter_Storage_FileTest');
		return $suite;
	}
}

if (PHPUnit_MAIN_METHOD == 'AllTests::main') {
	AllTests::main();
}