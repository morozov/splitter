<?php

/**
 * Объект запроса. Поднимает параметры из аргументов командной строки.
 * Формат параметров: -param1 value1 -param2 -param3 value3
 *
 * @version $Id$
 */
class Splitter_Request_Cli extends Splitter_Request_Abstract
{
	/**
	 * Инициализирует массив параметров запроса.
	 *
	 */
	function _initParams()
	{
		$argc = $_SERVER['argc'];
		$argv = $_SERVER['argv'];

		// если переданы аргументы командной строки (первый элемент массива не
		// считается, это имя файла)
		if ($argc > 1)
		{
			for ($i = 1; $i < $argc; $i++)
			{
				// если это наименование параметра
				if ($this->_isParamName($argv[$i]))
				{
					// и после него следует еще один аргумент
					if (($argc > $i + 1)

						// и это не наименование параметра
						&& !$this->_isParamName($argv[$i + 1]))
					{
						// добавляем наименование параметра и его значение
						// в ассоциативный массив
						$this->_params[substr($argv[$i], 1)] = $argv[++$i];
					}
					else
					{
						// иначе добавляем в массив просто наименование параметра
						$this->_params[$argv[$i]] = true;
					}
				}
			}
		}
	}

	/**
	 * Возвращает, является ли указанный аргумент наименованием параметра.
	 *
	 * @param string   $arg
	 * @return boolean
	 */
	function _isParamName($arg)
	{
		return '-' == substr($arg, 0, 1);
	}
}
