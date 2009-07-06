<?php

/**
 * Базовый класс объектов, описывающих параметры доступа к скачиваемым данным.
 *
 * @version $Id$
 */
abstract class Splitter_Resource_Abstract
{
	/**
	 * URL ресурса.
	 *
	 * @var Lib_Url
	 */
	var $_url;

	/**
	 * Конструктор.
	 *
	 * @param string   $target
	 * @return Splitter_Resource_Abstract
	 */
	function Splitter_Resource_Abstract(&$url)
	{
		$this->_url = $url;
	}

	/**
	 * Возвращает URL ресурса.
	 *
	 * @return Lib_Url
	 */
	function getUrl()
	{
		return $this->_url;
	}

	/**
	 * Возвращает ресурс для указаных URL и параметров.
	 *
	 * @param Lib_Url $url
	 * @param array $params
	 * @return Splitter_Resource_Abstract
	 */
	function factory(&$url, $params)
	{
		$className = Splitter_Resource_Abstract::_findClassName('Splitter_Resource_', ucfirst($url->getSchema()));

		if (!is_null($className))
		{
			$resource = call_user_func_array(array($className, 'factory'), array(&$url, $params));
		}
		else
		{
			$resource = new Splitter_Resource_Abstract($url, $params);
		}

		return $resource;
	}

	/**
	 * Ищет класс с указанным наименованием в указанном пэкидже.
	 *
	 * @return mixed
	 */
	function _findClassName($package, $className)
	{
		$className1 = $package . '_' . $className;
		$className2 = $className1 . '_Abstract';

		switch (true)
		{
			case classExists($className1) :
				$result = $className1;
				break;

			case classExists($className2) :
				$result = $className2;
				break;

			default:
				$result = $package . '_Abstract';
				break;
		}

		return $result;
	}
}
