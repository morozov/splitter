<?php

/**
 * Базовый класс соединений.
 *
 * @version $Id$
 */
abstract class Splitter_Connection_Abstract {

	/**
	 * Перевод строки для соединений — один и тот же, независимо от платформы.
	 */
	const CRLF = "\r\n";

	/**
	 * Порт соединения по умолчанию.
	 *
	 * @var integer
	 */
	var $DEFAULT_PORT = null;

	/**
	 * Числовое представление статуса ответа.
	 *
	 * @var integer
	 */
	var $_status;

	/**
	 * Строковое представление статуса ответа.
	 *
	 * @var string
	 */
	var $_statusText;

	/**
	 * Сокет управления соединением.
	 *
	 * @var Splitter_Socket
	 */
	var $_controlSocket;

	/**
	 * Сокет передачи данных.
	 *
	 * @var Splitter_Socket
	 */
	var $_dataSocket;

	/**
	 * Объект URL.
	 *
	 * @var Url
	 */
	var $_url;

	/**
	 * Возвращает управляющий сокет.
	 *
	 * @return Splitter_Socket
	 */
	function getControlSocket()
	{
		return $this->_controlSocket;
	}

	/**
	 * Возвращает сокет данных.
	 *
	 * @return Splitter_Socket
	 */
	function getDataSocket()
	{
		return $this->_dataSocket;
	}

	/**
	 * Возвращает числовое представление статуса ответа сервера.
	 *
	 * @return integer
	 */
	function getStatus()
	{
		return $this->_status;
	}

	/**
	 * Возвращает строковое представление статуса ответа сервера.
	 *
	 * @return string
	 */
	function getStatusText()
	{
		return $this->_statusText;
	}

	/**
	 * Закрывает соединение с сервером.
	 *
	 */
	function abort()
	{
		if ($this->_isConnected())
		{
			$this->_controlSocket = null;
			Application::getResponse()->log('Закрытие соединения');
		}
	}

	/**
	 * Подготавливает объект для нового соединения.
	 *
	 */
	function _onBeforeConnect()
	{
		// закрываем предыдущее соединение, если оно было установлено
		$this->abort();

		// сбрасываем состояние объекта
		$this->_resetState();
	}

	/**
	 * Пытается открыть соединение с сервером, возвращает результат попытки.
	 *
	 * @return boolean
	 */
	function _connect($url)
	{
		// подготавливаем объект для нового соединения
		$this->_onBeforeConnect();

		// создаем объект URL
		$this->_url = new Lib_Url($url);

		// определяем хост, на который открывать сокет
		$host = $this->_url->getHost();

		// определяем порт соединения
		$port = $this->_url->getPort($this->DEFAULT_PORT);

		// пытаемся открыть управляющий сокет
		$this->_controlSocket = $this->_createSocket($host, $port);

		// если соединились
		return $this->_isConnected()

			// выполняем соотв. действия (реализация в наследниках)
			&& $this->_onAfterConnect();
	}

	/**
	 * Выполняет некоторые действия сразу после установки соединения.
	 * Возвращает, успешно были ли выполнены действия.
	 *
	 * @return boolean
	 */
	function _onAfterConnect()
	{
		return true;
	}

	/**
	 * Открывает сокет на указанный хост и порт.
	 *
	 * @param string   $host
	 * @param integer  $port
	 * @return Splitter_Socket
	 */
	function _createSocket($host, $port) {
		$response = Application::getResponse();
		$response->log('Установка соединения с ' . $host . ':' . $port, 'request');
		$socket = new Splitter_Socket($host, $port);
		$response->log('Соединение установлено');
		return $socket;
	}

	/**
	 * Возвращает, установлено ли соединение с сервером.
	 *
	 * @return boolean
	 */
	function _isConnected()
	{
		return is_object($this->_controlSocket);
	}

	/**
	 * Сбрасывает состояние объекта в исходное состояние.
	 *
	 */
	function _resetState()
	{
		// проходим по массиву переменных класса
		foreach (get_class_vars(get_class($this)) as $var => $value)
		{
			// устанавливаем переменную в значение по умолчанию
			$this->$var = $value;
		}
	}

	/**
	 * Пишет строку в сокет соединения.
	 *
	 * @param string   $string
	 * @return boolean
	 */
	function _write($string)
	{
		return $this->_controlSocket->write($string);
	}

	/**
	 * Пишет строку в сокет соединения. Добавляет символ перевода строки.
	 *
	 * @param string   $string
	 * @return boolean
	 */
	function _writeln($string)
	{
		return $this->_write($string . self::CRLF);
	}
}
