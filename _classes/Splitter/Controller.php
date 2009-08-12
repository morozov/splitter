<?php

define('DOWNLOAD_STATUS_OK',	0);
define('DOWNLOAD_STATUS_ERROR', 1);
define('DOWNLOAD_STATUS_FATAL', 2);
define('DOWNLOAD_STATUS_INCOMPLETE', 3);

/**
 * Контроллер. Обрабатывает данные пользовательского запроса.
 *
 * @version $Id$
 */
final class Splitter_Controller {

	/**
	 * Запускает контроллер.
	 *
	 * @return boolean
	 */
	public function main() {
		set_error_handler(array(new Splitter_ErrorHandler, 'handle'));
		try {
			$this->process();
			return true;
		} catch (Exception $e) {
			$message = $e->getMessage();
			if (0 != ($code = $e->getCode())) {
				$message = 'Ошибка №' . $code . ': ' . $message;
			}
			Application::getResponse()->log($message, 'error');
		}
		return false;
	}

	/**
	 * Выполняет обработку данных пользовательского запроса.
	 *
	 */
	private function process() {

		$request = Application::getRequest();

		if (!$request->hasParam('url')) {
			throw new Exception('Не указан URL источника');
		}

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

		if ($this->isDownloadNeeded())
		{
			$type = $request->getParam('storage', 'file');
			$params['storage'] = $this->getStorage(
				$type,
				$request->getParam('split-size'),
				$this->getStorageOptions($request, $type)
			);
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

				if (is_object($handler = $this->getShareHandler($url, $request->getParam('method', 'get'))))
				{
					$handler->process($url, $params);

					break;
				}

				$params['url'] = $url;

				$result = $service->run($params);

				if (DOWNLOAD_STATUS_FATAL == $result->offsetGet('status'))
				{
					return false;
				}
			}
		}

		if ($make_links)
		{
			$response = Application::getResponse();

			$messages = array();

			foreach ($links as $link)
			{
				$messages[] = '<a href="' . $link . '" target="_blank">' . $link . '</a>';
			}

			$response->log(implode(PHP_EOL, $messages));
		}
	}

	/**
	 * Возвращает хранилище указанного типа.
	 *
	 * @param string $type
	 * @param integer $split_size
	 * @param string $target
	 * @return Splitter_Storage_Abstract
	 */
	private function getStorage($type, $split_size, $options) {
		return $split_size > 0
			? new Splitter_Storage($type, $split_size, $options)
			: Splitter_Storage_Abstract::factory($type, $options);
	}

	/**
	 * Возвращает цель для хранилища указанного типа.
	 *
	 * @param string $type
	 * @return string
	 */
	private function getStorageOptions($request, $type) {
		$params_map = array(
			'file'  => array('dir'),
			'email' => array('to', 'subject'),
		);
		$options = array();
		if (isset($params_map[$type])) {
			foreach ($params_map[$type] as $param) {
				if ($request->hasParam($param)) {
					$options[$param] = $request->getParam($param);
				}
			}
		}
		return $options;
	}

	/**
	 * Возвращает обработчик файлового сервера для указанного URL.
	 *
	 * @param Lib_Url $url
	 * @return Splitter_Share_Abstract
	 */
	private function getShareHandler($url, $method) {
		foreach (System_Loader::getClasses('Splitter_Share') as $class) {
			$handler = new $class;
			if ($handler->canProcess($url, $method)) {
				return $handler;
			}
		}
		return null;
	}

	/**
	 * Возвращает, нужно ли скачивать файл.
	 *
	 * @return boolean
	 */
	private function isDownloadNeeded() {
		// проверяем, не была ли нажата кнопка "получить размер", т.к. если
		// форма была отправлена не по нажатию кнопки, файл нужно скачивать
		return !Application::getRequest()->hasParam('get-size');
	}
}
