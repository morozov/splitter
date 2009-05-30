<?php

/**
 * @package	 Splitter
 * @subpackage  app
 * @version	 $Id$
 */
/**
 * Объект настроек системы.
 *
 * @access	  public
 * @package	 Splitter
 * @subpackage  app
 * @see		 abstract_Object
 */
class Splitter_App_Settings
{
	/**
	 * Объект-массив параметров.
	 *
	 * @access  protected
	 * @var	 ArrayObject
	 */
	var $_params;

	/**
	 * Конструктор.
	 *
	 * @access  public
	 * @return  Settings
	 */
	function Splitter_App_Settings()
	{
		// загружаем параметры из cookies
		$this->_params = new Lib_ArrayObject($_COOKIE);
	}

	/**
	 * Возвращает значение указанного параметра.
	 *
	 * @access  public
	 * @param   string   $name   Наименование параметра
	 * @return  string
	 */
	function getParam($name)
	{
		return $this->_params->offsetGet($name);
	}
}
