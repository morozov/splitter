<?php

require_once 'Zend/Loader.php';

/**
 * Системный загрузчик.
 *
 * @version $Id$
 */
class System_Loader extends Zend_Loader {

	/**
	 * Возвращает массив наименований классов пакета за исключением 'Abstract'.
	 *
	 * @param string $package
	 * @return array
	 */
	public function getPackageClasses($package) {
		$result = false;
		if (false !== ($handle = opendir(realpath(dirname(__FILE__) . '/..') . DIRECTORY_SEPARATOR
			. str_replace('_', DIRECTORY_SEPARATOR, $package)))) {
			$result = array();
			while (false !== ($file = readdir($handle))) {
				if (preg_match('/(.+)(?<!Abstract)\.php$/', $file, $matches)) {
					$result[] = $package . '_' . $matches[1];
				}
			}
		}
		return $result;
	}
}
