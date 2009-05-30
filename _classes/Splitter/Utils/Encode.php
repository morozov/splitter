<?php

/**
 * @package	 Splitter
 * @subpackage  utils
 * @version	 $Id$
 */
/**
 * Утилита преобразования строк (урлов и имен файлов) для обхода ограничений
 * прокси.
 *
 * @package	 Splitter
 * @subpackage  utils
 * @see		 abstract_Object
 */
class Splitter_Utils_Encode
{
	/**
	 * Описания алгоритмов преобразования
	 *
	 * @var	 array
	 */
	var $_methods = array
	(
		'base64' => array('base64_encode', 'base64_decode'),
		'rot13'  => array('str_rot13', 'str_rot13'),
	);

	/**
	 * Наименование текущего алгоритма.
	 *
	 * @var	 string
	 */
	var $_method;

	/**
	 * Конструктор.
	 *
	 * @param   string $method
	 * @return  Splitter_Utils_Encode
	 */
	function Splitter_Utils_Encode($method)
	{
		if (!array_key_exists($method, $this->_methods))
		{
			trigger_error('Unsupported encoding method "' . $method . '"', E_USER_WARNING);
		}

		$this->_method = $method;
	}

	/**
	 * Выполняет прямое преобразование значения.
	 *
	 * @param   string  $value
	 * @return  string
	 */
	function encode($value)
	{
		return call_user_func($this->_methods[$this->_method][0], $value);
	}

	/**
	 * Выполняет обратное преобразование значения.
	 *
	 * @param   string  $value
	 * @return  string
	 */
	function decode($value)
	{
		return call_user_func($this->_methods[$this->_method][1], $value);
	}
}
