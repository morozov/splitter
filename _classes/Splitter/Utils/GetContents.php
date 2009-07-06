<?php

/**
 * Утилита получения содержимого ресурса с указанными параметрами.
 *
 * @version $Id$
 */
class Splitter_Utils_GetContents
{
	/**
	 * Возвращает содержимое ресурса.
	 *
	 * @param Lib_Url $url
	 * @param array   $params   Параметры запуска
	 * @return string
	 */
	function getContents(&$url, $params = array())
	{
		// создаем хранилище данных в оперативной памяти
		$params['storage'] = new Splitter_Storage_Ram();

		//:TODO: morozov 03122006: надо создать отдельный класс ресурсов
		$params['url'] = $url;

		// создаем сервис закачки
		$service = new Splitter_Service_Download_Intf();

		// запускаем сервис
		$result = $service->run($params);

		// если сервис отработал успешно
		return DOWNLOAD_STATUS_OK == $result->offsetGet('status')

			// возвращаем содержимое скачанной страницы
			? $params['storage']->getContents() : false;
	}
}
