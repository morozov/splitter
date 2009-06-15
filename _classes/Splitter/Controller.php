<?php

define('DOWNLOAD_STATUS_OK',	0);
define('DOWNLOAD_STATUS_ERROR', 1);
define('DOWNLOAD_STATUS_FATAL', 2);
define('DOWNLOAD_STATUS_INCOMPLETE', 3);

/**
 * @package	 Splitter
 * @subpackage
 * @version	 $Id$
 */
/**
 * Контроллер. Обрабатывает данные пользовательского запроса.
 *
 * @package	 Splitter
 * @subpackage
 * @see		 abstract_Object
 */
class Splitter_Controller
{
	/**
	 * Выполняет обработку данных пользовательского запроса.
	 *
	 * @return  boolean
	 */
	function process()
	{
		$request =& Application::getRequest();

		if ($request->hasParam('in-background'))
		{
			Application::runAsCli();
		}
		elseif ($request->hasParam('url'))
		{
			// собираем массив параметров запуска сервиса
			$params = $request->getParams();

			// добавляем captcha
			if ($request->hasParam('captcha-param'))
			{
				$postData = $request->getParam('post-data');

				if (strlen($postData) > 0)
				{
					$postData .= '&';
				}

				$postData .= $request->getParam('captcha-param')
					. '=' . $request->getParam('captcha-text');

				$params['post-data'] = $postData;
			}

			// создаем абстракцию сервиса закачки
			$service = new Splitter_Service_Download_Intf();

			if ($request->getParam('rename'))
			{
				// пока не знаю, куда это запихнуть
				$GLOBALS['rename'] = new Splitter_Utils_Rename(
					$request->getParam('rename-search'),
					$request->getParam('rename-replace'),
					$request->getParam('rename-regexp')
				);
			}

			if ($this->_isDownloadNeeded())
			{
				$type = $request->getParam('storage', 'file');

				$target = $this->_getTarget($type);

				$params['storage'] =& $this->_getStorage($type, $target, $request->getParam('split-size'));
			}
			else
			{
				unset($params['storage']);
			}

			$make_links = $request->hasParam('links');

			$comp = new Splitter_Utils_ProxyLink();

			$links = array();

			foreach (explode(PHP_EOL, trim($request->getParam('url'))) as $url)
			{
				if ($make_links)
				{
					$links = array_merge($links, $comp->generate($url, $request->getParam('split-size'), $request->getParam('encoding')));
				}
				else
				{
					$url = new Lib_Url($url);

					if (is_object($handler =& $this->_getShareHandler($url, $request->getParam('method', 'get'))))
					{
						$handler->process($url, $params);

						break;
					}

					$params['url'] =& $url;

					$result =& $service->run($params);

					if (DOWNLOAD_STATUS_FATAL == $result->offsetGet('status'))
					{
						return false;
					}
				}
			}

			if ($make_links)
			{
				$response =& Application::getResponse();

				$messages = array();

				foreach ($links as $link)
				{
					$messages[] = '<a href="' . $link . '" target="_blank">' . $link . '</a>';
				}

				$response->write(implode(PHP_EOL, $messages));
			}
		}
		else
		{
			trigger_error('Не указан URL источника', E_USER_WARNING);
			return false;
		}

		return true;
	}

	/**
	 * Возвращает хранилище указанного типа.
	 *
	 * @param   string $type
	 * @param   string $target
	 * @param   integer $splitSize
	 * @return  Splitter_Storage_Abstract
	 */
	function _getStorage($type, $target, $splitSize) {
		return $splitSize > 0
			? new Splitter_Storage_Intf($type, $target, $splitSize)
			: Splitter_Storage_Abstract::factory($type, $target);
	}

	/**
	 * Возвращает цель для хранилища указанного типа.
	 *
	 * @param   string $type
	 * @return  string
	 */
	function _getTarget($type)
	{
		$request =& Application::getRequest();
		return $request->getParam('target-' . $type);
	}

	/**
	 * Возвращает обработчик файлового сервера для указанного URL.
	 *
	 * @param   Lib_Url $url
	 * @return  Splitter_Share_Abstract
	 */
	function _getShareHandler($url, $method)
	{
		$result = null;

		foreach (System_Loader::getPackageClasses('Splitter_Share') as $className)
		{
			$handler = new $className();

			if ($handler->canProcess($url, $method))
			{
				$result =& $handler;

				break;
			}
		}

		return $result;
	}

	/**
	 * Возвращает, нужно ли скачивать файл.
	 *
	 * @return  boolean
	 */
	function _isDownloadNeeded()
	{
		// определяем по кнопке, нажатой при отправке формы
		$request =& Application::getRequest();

		// проверяем, не была ли нажата кнопка "получить размер", т.к. если
		// форма была отправлена не по нажатию кнопки, файл нужно скачивать
		return !$request->hasParam('get-size');
	}
}
