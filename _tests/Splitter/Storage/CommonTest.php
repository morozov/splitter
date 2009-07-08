<?php

class Splitter_Storage_CommonTest extends PHPUnit_Framework_TestCase {

	protected static $types = array(
		'file'  => array('/tmp'),
		'email' => array('dummy@example.com'),
		'ram'   => array(),
	);

	public function testResumePositionIsZero() {
		$this->_test(__FUNCTION__);
	}

	protected function _test($testName) {
		foreach (self::$types as $type => $arguments) {
			$storage = call_user_func_array(array('Splitter_Storage_Abstract', 'factory'), array_merge(array($type), $arguments));
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
