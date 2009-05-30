<?php

/**
 * @package	 Splitter
 * @subpackage  utils
 * @version	 $Id$
 */
/**
 * Утилита получения размера файла.
 *
 * @package	 Splitter
 * @subpackage  utils
 * @see		 abstract_Object
 */
class Splitter_Utils_GetSize
{
	/**
	 * Возвращает размера файла.
	 *
	 * @param   Lib_Url $url
	 * @return  integer
	 */
	function getSize(&$url)
	{
		//:TODO: morozov 03122006: надо создать отдельный класс ресурсов
		$params['url'] =& $url;

		// создаем сервис закачки
		$service = new Splitter_Service_Download_Intf();

		// запускаем сервис
		$result =& $service->run($params);

		// если сервис отработал успешно
		return DOWNLOAD_STATUS_OK == $result->offsetGet('status')

			// возвращаем размер
			? $result->offsetGet('size') : false;
	}
}
