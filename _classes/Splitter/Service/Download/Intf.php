<?php

define('DOWNLOAD_STATUS_REDIRECT', 3);

/**
 * Абстракция сервиса закачки файла. В зависимости от протокола URL'a делегирует
 * обязанность одной из доступных реализаций - потомку Splitter_Service_Download_Abstract.
 * Введена ввиду необходимости иметь возможность менять реализацию сервиса во
 * время выполнения (редирект с HTTP на другой протокол).
 *
 * @version $Id$
 */
class Splitter_Service_Download_Intf extends Splitter_Service_Abstract
{
	/**
	 * Протокол по умолчанию.
	 *
	 * @var string
	 */
	var $DEFAULT_PROTOCOL = 'http';

	/**
	 * Максимальное количество перенаправлений, которое должен обработать клиент
	 * (пока не реализовано).
	 *
	 * @var integer
	 */
	var $MAX_REDIRECTS_COUNT = 10;

	/**
	 * Добавляет загрузку файла.
	 *
	 * @param string   $url
	 * @return ArrayObject
	 */
	function run($params, $reset = true)
	{
		$result = parent::run($params, $reset);

		do
		{
			$isRedirected = false;

			// пытаемся создать сервис скачивания файла
			if (is_object($service = $this->_createDownloadService($params['url'])))
			{
				// запускаем сервис
				$result = $this->_runService($service, $params);

				if (DOWNLOAD_STATUS_REDIRECT == $result->offsetGet('status'))
				{
					$params['method'] = 'get';
					$params['url'] = $result->offsetGet('url');
					$params['referer'] = $result->offsetGet('referer');

					$isRedirected = true;
				}
			}
			else
			{
				// отправляем сообщение об ошибке протокола
				trigger_error('Скачивание по протоколу "'
					. strtoupper($params['url']->getScheme())
					. '" не реализовано', E_USER_WARNING);

				// передаем ошибку в результат
				$result->offsetSet('status', DOWNLOAD_STATUS_ERROR);
			}
		}
		while ($isRedirected);

		return $result;
	}

	/**
	 * Создает и возвращает объект сервиса скачивания файла.
	 *
	 * @param Url
	 * @return Splitter_Service_Download_Abstract
	 */
	function _createDownloadService(&$url)
	{
		$service = null;

		// определяем протокол урла
		$className = 'Splitter_Service_Download_'
			. ucfirst($url->getScheme($this->DEFAULT_PROTOCOL));

		if (class_exists($className))
		{
			// создаем экземпляр сервиса для обработки данного протокола
			$service = new $className();
		}

		return $service;
	}

	/**
	 * Запускает сервис-реализацию и обрабатывает результат его работы.
	 *
	 * @param Splitter_Service_Download_Abstract $service
	 * @param array $params
	 * @return Lib_ArrayObject
	 */
	function _runService(&$service, $params)
	{
		$settings = Application::getSettings();

		$useAutoResume = $settings->getParam('use-auto-resume');
		$autoResumeCount = $settings->getParam('auto-resume-count');
		$autoResumeInterval = $settings->getParam('auto-resume-interval');

		$restartsCount = 0;

		do
		{
			$restartNeeded = false;

			// запускаем сервис-реализацию
			$result = $service->run($params);

			// получаем статус результата
			switch ($result->offsetGet('status'))
			{
				// в случае, если получено содержимое файла
				case DOWNLOAD_STATUS_OK:
					break;

				// в случае, если получено перенаправление
				// ничего не делаем, этот статус обрабатывается снаружи
				case DOWNLOAD_STATUS_REDIRECT:
					break;

				// в случае, если файл недокачан
				case DOWNLOAD_STATUS_ERROR:
				case DOWNLOAD_STATUS_INCOMPLETE:

					if ($useAutoResume && (++$restartsCount < $autoResumeCount))
					{
						$response = Application::getResponse();
						$response->write
						(
							sprintf
							(
								'Повторный перезапуск закачки (%d из %d) через %d секунд',
								$restartsCount,
								$autoResumeCount,
								$autoResumeInterval
							)
						);

						// делаем паузу
						sleep($autoResumeInterval);

						$restartNeeded = true;
					}

					break;
			}
		}
		while ($restartNeeded);

		return $result;
	}
}
