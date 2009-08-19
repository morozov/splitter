<?php

require_once dirname(__FILE__) . '/../../TestHelper.php';

class Splitter_Storage_CommonTest extends PHPUnit_Framework_TestCase {

	protected static $types = array(
		'file'  => array('dir' => '/tmp'),
		'email' => array('to' => 'dummy@example.com'),
		'ram'   => array(),
	);

	public function testFactoryWrongType() {
		$this->setExpectedException('Splitter_Storage_Exception');
		Splitter_Storage_Abstract::factory('dummy');
	}

	public function testFactoryWrongOption() {
		$this->setExpectedException('Splitter_Storage_Exception');
		Splitter_Storage_Abstract::factory('file', array('dummy' => 'dummy'));
	}

	public function testResumePositionIsZero() {
		$this->_test(__FUNCTION__);
	}

	public function testCreationWithOptions() {
		foreach (self::$types as $type => $options) {
			if (count($options) > 0) {
				try {
					$storage = Splitter_Storage_Abstract::factory($type);
					$this->fail('Storage of type "' . $type . '" is created with empty options');
				} catch (Splitter_Storage_Exception $e) { }
			}
		}
	}

	protected function _test($testName) {
		foreach (self::$types as $type => $options) {
			$storage = Splitter_Storage_Abstract::factory($type, $options);
			$method = '_' . $testName;
			try {
				$this->$method($storage);
			} catch (PHPUnit_Framework_AssertionFailedError $e) {
				$this->fail($e->getMessage() . '(' . get_class($storage) . ')');
			}
		}
	}

	protected function _testResumePositionIsZero($storage) {
		$this->assertEquals(0, $storage->getResumePosition());
	}
}
