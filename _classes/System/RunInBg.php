<?php

/**
 * @package	 Splitter
 * @subpackage  System
 * @version	 $Id$
 */
/**
 * Перезапускает процесс с текущими параметрами в фоне.
 *
 * @package	 Splitter
 * @subpackage  System
 * @see		 abstract_Object
 */
class System_RunInBg
{
	/**
	 * Массив параметров запроса, которые не должны быть переданы в фоновый
	 * процесс.
	 *
	 * @var	 array
	 */
	var $PARAMETERS_TO_EXCLUDE = array('in-background');

	/**
	 * Перезапускает процесс в фоне. Возвращает идентификатор процесса или
	 * FALSE в случае неудачи.
	 *
	 * @return  integer
	 */
	function run()
	{
		$imp =& $this->_getImplementation();
		return $imp->run($_SERVER['SCRIPT_FILENAME'] . ' ' . $this->_getArgs());
	}

	/**
	 * Возвращает строку аргументов для запуска скрипта.
	 *
	 * @return  string
	 */
	function _getArgs()
	{
		$args = array();

		// @link http://bugs.php.net/bug.php?id=40928
		$PERCENT = '__' . md5(uniqid(rand(), true)) . '__';

		$request =& Application::getRequest();

		// проходим по массиву параметров пользовательского запроса
		foreach ($request->getParams() as $name => $value)
		{
			// исключаем параметры с пустыми значениями и те, которые не должны
			// быть переданы
			if (strlen($value) > 0 && !in_array($name, $this->PARAMETERS_TO_EXCLUDE))
			{
				$args[] = '-' . $name . ' ' . str_replace($PERCENT, '%', escapeshellarg(str_replace('%', $PERCENT, $value)));
			}
		}

		return implode(' ', $args);
	}

	/**
	 * Возвращает объект-реализацию запуска фонового процесса.
	 *
	 * @return  System_Run_Abstract
	 */
	function _getImplementation()
	{
		foreach (getPackageClasses('System_Run') as $className)
		{
			$candidate = new $className();

			if ($candidate->suits())
			{
				return $candidate;
			}
		}

		return null;
	}
}
