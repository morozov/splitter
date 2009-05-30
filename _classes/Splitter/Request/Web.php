<?php

/**
 * @package	 Splitter
 * @subpackage  request
 * @version	 $Id$
 */
/**
 * Объект запроса для веб-интерфейса.
 *
 * @access	  public
 * @package	 Splitter
 * @subpackage  request
 * @see		 Splitter_abstract_Request
 */
class Splitter_Request_Web extends Splitter_Request_Abstract
{
	/**
	 * �?нициализирует массив параметров запроса.
	 *
	 * @access  protected
	 */
	function _initParams()
	{
		// удаляем лишние пробелы прямо на уровне разбора параметров запуска
		// приложения. может и криво, зато централизованно. иначе для всех
		// строковых параметров их придется обрезать руками
		$this->_params = array_map('trim', $this->_getRawParams());

		// чистим входные параметры от мусора magic_quotes
		if (get_magic_quotes_gpc())
		{
			$this->_params = array_map('stripslashes', $this->_params);
		}
	}

	/**
	 * Возвращает параметры в исходном виде.
	 *
	 * @return  array
	 */
	function _getRawParams()
	{
		return $_POST;
	}
}
