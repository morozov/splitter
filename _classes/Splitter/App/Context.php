<?php

/**
 * Контекст приложения. Контейнер объектов-синглтонов.
 *
 * @version $Id$
 */
class Splitter_App_Context
{
	/**
	 * Массив наименований классов объектов контекста приложения.
	 *
	 * @var array
	 */
	var $_classes = array();

	/**
	 * Массив именованных объектов контекста приложения.
	 *
	 * @var array
	 */
	var $_objects = array();

	/**
	 * Конструктор. Cоздает объекты запроса, ответа и сессии.
	 *
	 * @return Splitter_App_Context
	 */
	function Splitter_App_Context($classes)
	{
		$this->_classes = $classes;
	}

	/**
	 * Возвращает именованный объект контекста приложения.
	 *
	 * @return abstract_Object
	 */
	function getObject($name)
	{
		if (!array_key_exists($name, $this->_objects))
		{
			$this->_objects[$name] = $this->_createObject($name);
		}

		return $this->_objects[$name];
	}

	/**
	 * Создает именованный объект.
	 *
	 * @return BaseObject
	 */
	function _createObject($name)
	{
		$object = null;

		if (array_key_exists($name, $this->_classes))
		{
			$object = new $this->_classes[$name];
		}

		return $object;
	}
}
