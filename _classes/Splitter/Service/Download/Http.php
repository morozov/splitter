<?php

/**
 * Сервис скачивания файла по протоколу HTTP.
 *
 * @version $Id$
 */
class Splitter_Service_Download_Http extends Splitter_Service_Download_Abstract {

	/**
	 * Массив наименований заголовков ответа и директив, из которых можно
	 * получить имя скачиваемого файла.
	 *
	 * @var array
	 */
	protected static $filename_source_headers = array (
		'Content-Disposition' => 'filename',
		'Content-Type'        => 'name',
	);

	/**
	 * Имя файла по умолчанию. используется, если не удалось определить ни
	 * одним из доступных способов.
	 */
	const DEFAULT_FILENAME = 'noname.html';

	/**
	 * Запускает скачивание файла по протоколу HTTP.
	 *
	 * @param array   $params   Параметры запуска
	 * @param array   $reset	Указывает, нужно ли сбрасывать значения
	 *							параметров с предыдущего запуска (используется
	 *							при внутреннем перезапуске сервиса)
	 * @return Lib_ArrayObject
	 * @see Splitter_Service_Download_Abstract::run
	 */
	function run($params, $reset = true)
	{
		$result = parent::run($params, $reset);

		// получаем объект URL из параметров запуска
		$url = $this->_getUrl();

		// подправляем значение метода запроса
		$this->_setParam('method', $method = $this->_getRequestMethod());

		// открываем соединение с сервером
		if ($this->_conn->open($method, $url->toString()))
		{
			// устанавливаем заголовки запроса
			$this->_setRequestHeaders();

			// отправляем запрос на сервер
			if ('post' == $method)
			{
				$this->_conn->setRequestHeader('Content-Type', $this->_getParam('content-type'));
				$this->_conn->send($this->_getParam('post-data'));
			}
			else
			{
				$this->_conn->send();
			}

			// обрабатываем ответ
			$this->_processResponse($result);
		}

		return $result;
	}

	/**
	 * Возвращает имя, под которым будет сохранен скачиваемый файл.
	 *
	 * @return string
	 */
	function _getFileName() {
		return null !== ($filename = parent::_getFileName())
			? $filename : self::DEFAULT_FILENAME;
	}

	/**
	 * Возвращает наименование класса используемого соединения.
	 *
	 * @return string
	 */
	function _getConnectionClassName()
	{
		return 'Splitter_Connection_Http';
	}

	/**
	 * Возвращает метод запроса, полученный из параметров запуска приложения.
	 *
	 * @return string
	 */
	function _getRequestMethod()
	{
		return $this->_isDownloadNeeded()
			? $this->_getParam('method', 'get') : 'head';
	}

	/**
	 * Возвращает размер скачиваемого файла.
	 *
	 * @return integer
	 */
	function _getFileSize()
	{
		switch (true)
		{
			// пытаемся определить размер файла по Content-Range
			case preg_match(
				'|bytes\s+(\d*)\-((\d*)(/(\d*))?)?$|',
				$this->_conn->getResponseHeader('Content-Range'),
				$matches
			) && isset($matches[5]):
				$size = (int)$matches[5];
				break;

			// пытаемся определить размер файла по Content-Length
			case preg_match(
				'|(\d+)|',
				$this->_conn->getResponseHeader('Content-Length'),
				$matches
			):
				$size = (int)$matches[1];
				break;

			// рзамер не определен
			default:
				$size = null;
				break;
		}

		return $size;
	}

	/**
	 * Проверяет статус ответа сервера на соответствие 2xx (Client request
	 * successful).
	 *
	 * @return boolean
	 */
	function _checkStatus()
	{
		return '2' == substr($this->_conn->getStatus(), 0, 1);
	}

	/**
	 * Возвращает значение начала диапазона файла, который отдает сервер.
	 *
	 * @return integer
	 */
	function _getResponseStartPosition()
	{
		return preg_match
		(
			'|(\d+)|',
			$this->_conn->getResponseHeader('Content-Range'),
			$matches
		) ? (int)$matches[1] : 0;
	}

	/**
	 * Устанавливает заголовки запроса.
	 *
	 */
	function _setRequestHeaders()
	{
		// представляемся серверу
		$this->_setUserAgentHeader();

		// устанавливаем заголовок диапазона
		if ('get' == $this->_getParam('method'))
		{
			$this->_setRangeHeader();
		}

		// устанавливаем заголовок авторизации
		$this->_setAuthHeader();

		// устанавливаем заголовок cookie
		$this->_setCookieHeader();

		// отправляем заголовок реферера
		$this->_setRefererHeader();

		// отправляем пользовательские заголовки
		$this->_setCustomHeaders();
	}

	/**
	 * Устанавливает заголовок авторизации.
	 *
	 */
	function _setAuthHeader()
	{
		// получаем объект URL скачиваемого файла
		$url = $this->_getUrl();

		// определяем имя пользователя для доступа к файлу
		$userName = $url->getUserName();

		// если имя пользователя указано
		if (strlen($userName) > 0)
		{
			// добавляем заголовок базовой авторизвции
			$this->_conn->setRequestHeader('Authorization', 'Basic '
				. base64_encode($userName . ':' . $url->getPassword()));
		}
	}

	/**
	 * Устанавливает заголовок cookie.
	 *
	 */
	function _setCookieHeader()
	{
		// получаем значение cookie из параметров запуска
		if ($this->_hasParam('cookie'))
		{
			// добавляем заголовок в запрос
			$this->_conn->setRequestHeader('Cookie', $this->_getParam('cookie'));
		}
	}

	/**
	 * Устанавливает заголовок реферера.
	 *
	 */
	function _setRefererHeader()
	{
		// если реферер указан явно при запуске сервиса
		if ($this->_hasParam('referer'))
		{
			// используем указанное значение
			$referer = $this->_getParam('referer');
		}
		else
		{
			// получаем объект URL скачиваемого файла (копированием)
			$url = clone($this->_getUrl());

			// сбрасываем имя пользователя, чтобы не отправлять его
			// в лог в незашифрованном виде, а в самом запросе оно вообще не надо
			$url->setUserName('');

			// редиректим в корень директории
			$url->applyRedirect('./');

			// то, что получилось, устанавливаем заместо реферера
			$referer = $url->toString();
		}

		// передаем заголовок в запрос
		$this->_conn->setRequestHeader('Referer', $referer);
	}

	/**
	 * Устанавливает заголовок диапазона.
	 *
	 */
	function _setRangeHeader()
	{
		$storage = $this->_getStorage();

		// определяем, нужно ли докачивать частично скачанный файл
		if (($pos = $storage->getResumePosition()) > 0)
		{
			// добавляем заголовок диапазона
			$this->_conn->setRequestHeader('Range', 'bytes=' . $pos . '-');
		}
	}

	/**
	 * Устанавливает заголовок пользовательского агента.
	 *
	 */
	function _setUserAgentHeader()
	{
		$this->_conn->setRequestHeader('User-Agent', 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)');
	}

	/**
	 * Устанавливает пользовательские заголовки.
	 *
	 */
	function _setCustomHeaders()
	{
		if (is_array($headers = $this->_getParam('headers')))
		{
			foreach ($headers as $name => $value)
			{
				$this->_conn->setRequestHeader($name, $value);
			}
		}
	}

	/**
	 * Выполняет действия после того, как получен ответ сервера.
	 *
	 * @param ArrayObject  $result
	 */
	function _processResponse(&$result)
	{
		// в случае, если было получено перенаправление, прекращаем обработку
		if (is_object($url = $this->_getRedirectUrl()))
		{
			// генерируем сообщение о преренаправлении
			$response = Application::getResponse();
			$response->log('Получено перенаправление на ' . $url->toString());

			$referer = $this->_getUrl();

			// перезапускаем закачку с указанием старого урла в качестве
			// реферера, нового урла и старого явного имени
			$result->offsetSet('status', DOWNLOAD_STATUS_REDIRECT);
			$result->offsetSet('url', $url);
			$result->offsetSet('referer', $referer->toString());
		}

		// проверяем статус ответа сервера
		elseif ($this->_checkStatus())
		{
			// выводим в браузер сообщение с размером файла
			$size = $this->_getFileSize();
			$this->fireEvent('onFileSizeChange', $size);

			// сохраняем размер в результате работы сервиса
			$result->offsetSet('size', $size);

			// если нужно скачивать файл
			if ($this->_isDownloadNeeded())
			{
				// инициализируем флаг, обозначающий, изменилось ли имя файла,
				// для которого определялась стартовая позиция скачивания
				$fileNameChanged = false;

				$storage = $this->_getStorage();

				// определяем, изменилось ли имя файла. при этом если имя файла
				// было указано явно, то все изменения игнорируем, оно имеет
				// самый высокий приоритет
				if (!$this->_hasParam('filename')
					&& (!is_null($fileName = $this->getFileNameFromResponse())))
				{
					// устанавливаем измененное имя файла
					$storage->setFileName($fileName);

					// уведомляем обозревателей об изменении имени файла
					$this->fireEvent('onFileNameChange', $fileName);

					// имя файла изменилось
					$fileNameChanged = true;
				}

				// определяем позицию восстановления закачки из ответа сервера
				$responseStartPosition = $this->_getResponseStartPosition();

				// если она не соответствует началу запрошенного диапазона
				if ($storage->getResumePosition() != $responseStartPosition)
				{
					// и вместе с началом диапазона изменилось и имя файла,
					// значит кусок этого файла был скачан заранее
					if ($fileNameChanged)
					{
						// перезапускаем закачку с явным указанием нового имени
						// для того, чтобы хранилище определило позицию
						// возобновления закачки конкретно для этого имени файла
						$this->run(array
						(
							'filename' => $storage->getFileName(),
						), false);

						return;
					}
				}

				// выводим в браузер сообщение с текущим прогрессом
				$this->fireEvent('onProgressChange', $storage->getResumePosition());

				// открываем хранилище
				if (method_exists($storage, 'setSize')) {
					$storage->setSize($size);
				}

				// сохраняем скачанные данные
				$this->_store($result, $responseStartPosition, $size);
			}
			else
			{
				$result->offsetSet('status', DOWNLOAD_STATUS_OK);
			}
		}

		// если статус не равен 2xx - выводим объяснение ошибки
		else
		{
			trigger_error('Сервер ответил: ' . $this->_conn->getStatus()
				. ' (' . $this->_conn->getStatusText() . ')');

			$result->offsetSet('status', DOWNLOAD_STATUS_FATAL);
		}

		// закрываем соединение явным образом для того, чтобы вывести
		// соответствующее сообщение в лог
		$this->_conn->abort();
	}

	/**
	 * Возвращает имя скачиваемого файла, определенное из ответа сервера.
	 *
	 * @return string
	 */
	protected function getFileNameFromResponse() {

		$filename = null;

		// проходим по массиву заголовков и директив для определения имени файла,
		// пока не определим имя файла по одному из них
		for (reset(self::$filename_source_headers);
			is_null($filename)
				&& list($header_name, $directive_name)
					= each(self::$filename_source_headers);) {
			// определяем имя файла как значение соответствующей директивы
			$filename = $this->_getHeaderDirective($header_name, $directive_name);
		}

		// если не удалось определить, и ответ сервера содержит HTML
		if (is_null($filename) && $this->isHtml()) {

			// определяем, под каким именем мы собираемся сохранить файл
			$filename_from_storage = $this->_getStorage()->getFileName();

			// если это имя не содержит расширения 'html'
			if (!preg_match('|\.html?$|', $filename_from_storage)) {

				// добавляем его
				$filename = $filename_from_storage . '.html';
			}
		}

		return $filename;
	}

	/**
	 * Возвращает значение директивы заголовка или NULL, если заголовок или
	 * директива не существуют.
	 *
	 * @return string
	 */
	function _getHeaderDirective($headerName, $directiveName)
	{
		return preg_match(
			sprintf(
				'/%s\s*=\s*(?(?=")"([^"]*)|([^;]*))/i',
				preg_quote($directiveName)
			),
			// проверяем совпадение в искомом заголовке ответа
			$this->_conn->getResponseHeader($headerName), $matches
		)
			// достаем последнее из совпадений (т.к. RegExp - с условием)
			? end($matches) : null;
	}

	/**
	 * Обрабатывает заголовок Location. Возвращает TRUE в случае, если получено
	 * перенаправление.
	 *
	 * @return Url
	 */
	function _getRedirectUrl()
	{
		// пытаемся получить значение заголовка Location
		if (!is_null($location = $this->_conn->getResponseHeader('Location')))
		{
			// получаем объект URL скачиваемого файла (копирование)
			$url = clone($this->_getUrl());

			// применяем полученный редирект к новому урлу
			$url->applyRedirect($location);
		}
		else
		{
			$url = null;
		}

		return $url;
	}

	/**
	 * Возвращает, являются ли данные в ответе сервера текстом HTML.
	 *
	 * @return boolean
	 */
	protected function isHtml() {
		return 'text/html' == $this->_conn->getResponseHeader('Content-Type', false);
	}
}
