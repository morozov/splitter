<?php

/**
 * Объект запроса.
 *
 * @version $Id$
 */
abstract class Splitter_Request_Abstract
{
	/**
	 * Массив для хранения переменных запроса
	 *
	 * @var array
	 */
	var $_params = array();

	/**
	 * Конструктор.
	 *
	 * @return Splitter_abstract_Request
	 */
	function Splitter_Request_Abstract()
	{
		$this->_initParams();
	}

	/**
	 * Возвращает значение заданного параметра.
	 *
	 * @param string   $name
	 * @param mixed	$default
	 * @return mixed
	 */
	function getParam($name, $default = null)
	{
		return $this->hasParam($name, true) ? $this->_params[$name] : $default;
	}

	/**
	 * Устанавливает значение заданного параметра.
	 *
	 * @param string   $name
	 * @param mixed	$value
	 */
	function setParam($name, $value)
	{
		$this->_params[$name] = $value;
	}

	/**
	 * Определяет наличие заданного параметра
	 *
	 * @param string  $name	   Наименование параметра
	 * @param boolean $allowEmpty Если true, то функция будет считать
	 *							  пустые параметры существующими
	 * @return boolean
	 */
	function hasParam($name, $allowEmpty = false)
	{
		return isset($this->_params[$name])
			&& ($allowEmpty || !empty($this->_params[$name]));
	}

	/**
	 * Возвращает ассоциативный массив параметров.
	 *
	 * @return array
	 */
	function getParams()
	{
		return $this->_params;
	}

	/**
	 * Метод должен инициализировать массив параметров запроса.
	 *
	 */
	function _initParams()
	{
	}
}
