<?php

/**
 * @package	 Splitter
 * @subpackage  connection
 * @version	 $Id$
 */
/**
 * Базовый класс соединений.
 *
 * @access	  public
 * @package	 Splitter
 * @subpackage  connection
 * @see		 abstract_Object
 * @abstract
 */
abstract class Splitter_Connection_Abstract
{
	/**
	 * Порт соединения по умолчанию.
	 *
	 * @access  public
	 * @var	 integer
	 */
	var $DEFAULT_PORT = null;

	/**
	 * Числовое представление статуса ответа.
	 *
	 * @access  protected
	 * @var	 integer
	 */
	var $_status;

	/**
	 * Строковое представление статуса ответа.
	 *
	 * @access  protected
	 * @var	 string
	 */
	var $_statusText;

	/**
	 * Сокет управления соединением.
	 *
	 * @access  protected
	 * @var	 Socket
	 */
	var $_controlSocket;

	/**
	 * Сокет передачи данных.
	 *
	 * @access  protected
	 * @var	 Socket
	 */
	var $_dataSocket;

	/**
	 * Объект URL.
	 *
	 * @access  private
	 * @var	 Url
	 */
	var $_url;

	/**
	 * Возвращает управляющий сокет.
	 *
	 * @access  public
	 * @return  Socket
	 */
	function getControlSocket()
	{
		return $this->_controlSocket;
	}

	/**
	 * Возвращает сокет данных.
	 *
	 * @access  public
	 * @return  Socket
	 */
	function getDataSocket()
	{
		return $this->_dataSocket;
	}

	/**
	 * Возвращает числовое представление статуса ответа сервера.
	 *
	 * @access  public
	 * @return  integer
	 */
	function getStatus()
	{
		return $this->_status;
	}

	/**
	 * Возвращает строковое представление статуса ответа сервера.
	 *
	 * @access  public
	 * @return  string
	 */
	function getStatusText()
	{
		return $this->_statusText;
	}

	/**
	 * Закрывает соединение с сервером.
	 *
	 * @access  public
	 */
	function abort()
	{
		if ($this->_isConnected())
		{
			$this->_controlSocket->close();

			$this->_trace('Закрытие соединения');
		}
	}

	/**
	 * Подготавливает объект для нового соединения.
	 *
	 * @access  protected
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
	 * @access  protected
	 * @return  boolean
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
		$this->_controlSocket =& $this->_createSocket($host, $port);

		// если соединились
		return $this->_isConnected()

			// выполняем соотв. действия (реализация в наследниках)
			&& $this->_onAfterConnect();
	}

	/**
	 * Выполняет некоторые действия сразу после установки соединения.
	 * Возвращает, успешно были ли выполнены действия.
	 *
	 * @access  protected
	 * @return  boolean
	 */
	function _onAfterConnect()
	{
		return true;
	}

	/**
	 * Открывает сокет на указанный хост и порт.
	 *
	 * @access  protected
	 * @param   string   $host
	 * @param   integer  $port
	 * @return  Socket
	 */
	function _createSocket($host, $port)
	{
		// выдаем сообщение в лог
		$this->_trace('Установка соединения с ' . $host . ':' . $port, 'request');

		// создаем объект сокета
		$socket = new Lib_Socket();

		// пытаемся установить соединение с сервером
		if ($socket->open($host, $port))
		{
			// выдаем сообщение в лог
			$this->_trace('Соединение установлено');
		}
		else
		{
			trigger_error('Невозможно установить соединение с сервером.', E_USER_WARNING);
		}

		return $socket;
	}

	/**
	 * Возвращает, установлено ли соединение с сервером.
	 *
	 * @access  private
	 * @return  boolean
	 */
	function _isConnected()
	{
		return is_object($this->_controlSocket)
			&& $this->_controlSocket->isOpened();
	}

	/**
	 * Сбрасывает состояние объекта в исходное состояние.
	 *
	 * @access  private
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
	 * "Вещает" системное сообщение в объект-слушатель.
	 *
	 * @access  protected
	 */
	function _trace()
	{
		$response = &Application::getResponse();

		$args = func_get_args();

		call_user_func_array(array($response, 'write'), $args);
	}

	/**
	 * Пишет строку в сокет соединения.
	 *
	 * @access  private
	 * @param   string   $string
	 * @return  boolean
	 */
	function _write($string)
	{
		return $this->_controlSocket->write($string);
	}

	/**
	 * Пишет строку в сокет соединения. Добавляет символ перевода строки.
	 *
	 * @access  private
	 * @param   string   $string
	 * @return  boolean
	 */
	function _writeln($string)
	{
		return $this->_write($string . PHP_EOL);
	}
}
