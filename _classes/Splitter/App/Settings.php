<?php

/**
 * Объект настроек системы.
 *
 * @version $Id$
 */
class Splitter_App_Settings
{
	/**
	 * Объект-массив параметров.
	 *
	 * @var ArrayObject
	 */
	var $_params;

	/**
	 * Конструктор.
	 *
	 * @return Settings
	 */
	function Splitter_App_Settings()
	{
		// загружаем параметры из cookies
		$this->_params = new Lib_ArrayObject($_COOKIE);
	}

	/**
	 * Возвращает значение указанного параметра.
	 *
	 * @param string   $name   Наименование параметра
	 * @return string
	 */
	function getParam($name)
	{
		return $this->_params->offsetGet($name);
	}
}
