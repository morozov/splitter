<?php

/**
 * @package	 Splitter
 * @subpackage  connection
 * @version	 $Id$
 */
/**
 * Класс HTTP-запроса.
 * �?нтерфейс - аналогичен XMLHttpRequest или Microsoft.XMLHTTP за той разницей,
 * что вместо синхронного/aсинхронного запроса используется получение ответа
 * сервера целиком (после завершения соединения) или чтение данных из потока.
 *
 * @access	  public
 * @package	 Splitter
 * @subpackage  connection
 * @see		 Splitter_Connection_Abstract
 */
class Splitter_Connection_Http extends Splitter_Connection_Abstract
{
	/**
	 * Порт соединения по умолчанию.
	 *
	 * @access  public
	 * @var	 integer
	 */
	var $DEFAULT_PORT = 80;

	/**
	 * Регулярное выражение для разбора статуса ответа.
	 *
	 * @access  private
	 * @var	 string
	 */
	var $REGEXP_STATUS = '/^HTTP\/1\.[0|1]\s+(\d{3})\s*(.*)/';

	/**
	 * Протокол запроса.
	 * Пока используем версию 1.0, т.к. я пока не знаю, почему при запросе
	 * с использованием 1.1 сервер приписывает в ответ строчку из 3-х байтов
	 * в начале и из 1-го в конце.
	 *
	 *:TODO: morozov 19012006: Разобраться с HTTP 1.1, читать про chunks.
	 *
	 * @access  private
	 * @var	 string
	 */
	var $REQUEST_PROTOCOL = 'HTTP/1.0';

	/**
	 * Метод запроса (HEAD, GET, POST, etc.).
	 *
	 * @access  private
	 * @var	 string
	 */
	var $_method;

	/**
	 * Ассоциативный массив заголовков запроса.
	 *
	 * @access  private
	 * @var	 array
	 */
	var $_requestHeaders = array();

	/**
	 * Ассоциативный массив заголовков ответа.
	 *
	 * @access  private
	 * @var	 array
	 */
	var $_responseHeaders = array();

	/**
	 * Текст ответа.
	 *
	 * @access  private
	 * @var	 string
	 */
	var $_responseText;

	/**
	 * Открывает соединение с сервером.
	 *
	 * @access  public
	 * @param   string   $method
	 * @param   string   $url
	 */
	function open($method, $url)
	{
		$result = $this->_connect($url);

		// приводим метод к верхнему регистру
		$this->_method = strtoupper($method);

		return $result;
	}

	/**
	 * Отправляет запрос серверу.
	 *
	 * @access  public
	 * @param   string   $body  - Тело запроса
	 */
	function send($body = null)
	{
		// если в запрос передаются данные, указываем их длину
		if (!is_null($body))
		{
			$this->setRequestHeader('Content-Length', strlen($body));
		}

		// похуй, что там пользователь за заголовки понавыставил, но соединение
		// полюбому должно быть закрыто, иначе скрипт будет стоять, раскрыв рот
		// и ждать, что ему ещё сервер ответит
		$this->setRequestHeader('Connection', 'close');

		// отправляем заголовки запроса
		$this->_sendHeaders();

		// заканчиваем отправку заголовков
		$this->_writeln('');

		// отправляем тело запроса
		if (!is_null($body))
		{
			$this->_write($body);
		}

		// сразу же читаем заголовки ответа, не дожидаясь, пока пользователь их
		// спросит, потому что может и не спросить, а прочитать всё равно надо
		$this->_readHeaders();
	}

	/**
	 * Возвращает указанный заголовок ответа сервера в полной или краткой форме.
	 *
	 * @access  public
	 * @param   string   $param
	 * @param   boolean  $complete
	 * @return  string
	 */
	function getResponseHeader($param, $complete = true)
	{
		return !is_null($value = $this->_getArrayElement($this->_responseHeaders, $param))
			? ($complete
				? $value
				: preg_replace('/^([^;]*).*/', '$1', $value))
			: null;
	}

	/**
	 * Возвращает все заголовки ответа сервера.
	 *
	 * @access  public
	 * @return  array
	 */
	function getAllResponseHeaders()
	{
		return $this->_responseHeaders;
	}

	/**
	 * Устанавливает заголовок запроса к серверу.
	 *
	 * @access  public
	 * @param   string   $param
	 * @param   string   $value
	 */
	function setRequestHeader($param, $value)
	{
		$this->_setArrayElement($this->_requestHeaders, $param, $value);
	}

	/**
	 * Перекрывает метод предка и возвращает управляющий сокет, т.к. в HTTP
	 * управление и передача данных осуществляются вместе.
	 *
	 * @access  public
	 * @return  Socket
	 */
	function getDataSocket()
	{
		return $this->_controlSocket;
	}

	/**
	 * Устанавливает заголовки запроса в значения по умолчанию.
	 *
	 * @access  protected
	 */
	function _onAfterConnect()
	{
		// сразу устанавливаем заголовок "Host", иначе мы ничего не сможем
		// скачивать с виртуальных серверов
		$this->setRequestHeader('Host', $this->_url->getHost());

		// сообщаем серверу о том, что мы принимаем данные любых типов
		$this->setRequestHeader('Accept', '*/*');

		return parent::_onAfterConnect();
	}

	/**
	 * Возвращает значение элемента массива с указанным ключом.
	 * Нечувствителен к регистру.
	 *
	 * @access  private
	 * @param   array	$searchArray
	 * @param   string   $searchKey
	 * @return  string
	 */
	function _getArrayElement($searchArray, $searchKey)
	{
		// проходим по ключам массива
		foreach (array_keys($searchArray) as $arrayKey)
		{
			// сравниваем с искомым без учета регистра
			if (strtolower($arrayKey) == strtolower($searchKey))
			{
				return $searchArray[$arrayKey];
			}
		}

		return null;
	}

	/**
	 * Устанавливает значение элемента массива с указанным ключом.
	 * Нечувствителен к регистру.
	 *
	 * @access  private
	 * @param   array	$searchArray
	 * @param   string   $searchKey
	 * @param   string   $value
	 * @return  string
	 */
	function _setArrayElement(&$searchArray, $searchKey, $value)
	{
		// проходим по ключам массива
		foreach (array_keys($searchArray) as $arrayKey)
		{
			// сравниваем с искомым без учета регистра
			if (strtolower($arrayKey) == strtolower($searchKey))
			{
				// удаляем все элементы с заданным ключом
				unset($searchArray[$arrayKey]);
			}
		}

		// создаем новый элемент
		$searchArray[$searchKey] = $value;
	}

	/**
	 * Разбирает текст заголовков ответа сервера.
	 *
	 * @access  private
	 */
	function _parseHeaders($headers)
	{
		// очищаем массив заголовков ответа
		$this->_responseHeaders = array();

		// первая строка содержит статус ответа
		$statusLine = array_shift($headers);

		// добавляем ее в массив сообщения лога
		$messageArr[] = $statusLine;

		// пытаемся разобрать статус
		if (preg_match($this->REGEXP_STATUS, $statusLine, $matches))
		{
			$this->_status = (int)$matches[1];
			$this->_statusText = $matches[2];
		}
		else
		{
			trigger_error('Неверный ответ сервера: невозможно определить статус ответа');
		}

		// проходим по оставшимся строкам
		foreach ($headers as $header)
		{
			$messageArr[] = $header;

			// разбиваем строки на пары параметр - значение
			list($param, $value) = explode(':', $header, 2);

			// запихиваем в ассоциативный массив
			// для заголовков типа Set-Cookie делаем так, что если ответ содержит
			// более одного значения заголовка, то значения оборачиваются
			// в массив
			if (array_key_exists($param, $this->_responseHeaders))
			{
				// если получено второе значение заголовка (т.е. значение еще
				// не массив, а строка)
				if (!is_array($this->_responseHeaders[$param]))
				{
					// оборачиваем строку в массив
					$this->_responseHeaders[$param] = array($this->_responseHeaders[$param]);
				}

				// добавляем новое значение в массив
				$this->_responseHeaders[$param][] = trim($value);
			}
			else
			{
				$this->_responseHeaders[$param] = trim($value);
			}
		}

		// выдаем заголовки в лог
		$this->_trace(implode(PHP_EOL, $messageArr), 'response');
	}

	/**
	 * Читает заголовки ответа сервера.
	 *
	 * @access  private
	 */
	function _readHeaders()
	{
		$headers = array();

		// читаем данные из сокета
		while (!$this->_controlSocket->eof())
		{
			// читаем строку из ответа
			$header = rtrim($this->_controlSocket->gets(), "\r\n");

			// если пришла пустая строка, значит заголовки закончились
			if (0 == strlen($header))
			{
				// отправляем заголовки в разбор
				$this->_parseHeaders($headers);

				return;
			}

			$headers[] = $header;
		}

		// если до самого конца ответа не удалось определить заголовки, значит
		// что-то не так. в принципе, это невозможная ситуация
		trigger_error('Неверный ответ сервера: невозможно определить границу между заголовками и телом ответа');
	}

	/**
	 * Отправляет заголовки запроса.
	 *
	 * @access  private
	 */
	function _sendHeaders()
	{
		// выдаем в сокет строку вида "GET /path/file.ext?param=value HTTP/1.1"
		$methodNUri = $this->_method . ' ' . $this->_url->getUri() . ' ' . $this->REQUEST_PROTOCOL;

		$this->_writeln($methodNUri);

		$messageArr = array($methodNUri);

		// проходим по массиву заголовком
		foreach ($this->_requestHeaders as $param => $value)
		{
			// составляем строку из пары "Параметр: значение"
			$headerLine = $param . ': ' . $value;

			// выдаем строку в запрос
			$this->_writeln($headerLine);

			// добавляем строку в лог
			$messageArr[] = $headerLine;
		}

		$this->_trace(implode(PHP_EOL, $messageArr), 'request');
	}
}
