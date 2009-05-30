<?php

/**
 * @package	 Splitter
 * @subpackage  Lib
 * @version	 $Id$
 */
/**
 * Объектная обертка для сокетов PHP.
 *
 * @package	 Splitter
 * @subpackage  Lib
 * @see		 abstract_Object
 */
class Lib_Socket
{
	/**
	 * Время, в течение которого скрипт пытается открыть сокет, сек.
	 *
	 * @var	 float
	 */
	var $CONNECTION_TIMEOUT = 4.00;

	/**
	 * Сокет-ресурс.
	 *
	 * @var	 resource
	 */
	var $_socket;

	/**
	 * Открывает сокет на указанный хост и порт.
	 *
	 * @param   string   $host
	 * @param   integer  $port
	 * @return  boolean
	 */
	function open($host, $port)
	{
		$this->_socket = @fsockopen($host, $port, $errno, $errmsg, $this->CONNECTION_TIMEOUT);

		return $this->isOpened();
	}

	/**
	 * Читает до $length байт из сокета.
	 *
	 * @param   integer  $length
	 * @return  string  -  Подстрока ответа
	 *		   FALSE  -  Если достигнут конец файла
	 */
	function read($length)
	{
		return fread($this->_socket, $length);
	}

	/**
	 * Читает до $length байт или до символа перевода строки из сокета.
	 *
	 * @return  string  -  Подстрока ответа
	 *		   FALSE  -  Если достигнут конец файла
	 */
	function gets()
	{
		return fgets($this->_socket);
	}

	/**
	 * Пишет строку в сокет.
	 *
	 * @param   string   $string
	 * @return  boolean
	 */
	function write($string)
	{
		return fwrite($this->_socket, $string);
	}

	/**
	 * Возвращает TRUE в случае, если сокет не открыт или достигнут конец файла.
	 *
	 * @return  boolean
	 */
	function eof()
	{
		return !$this->isOpened() || feof($this->_socket);
	}

	/**
	 * Возвращает, открыт ли сокет.
	 *
	 * @return  boolean
	 */
	function isOpened()
	{
		return is_resource($this->_socket);
	}

	/**
	 * Закрывает сокет.
	 *
	 * @return  boolean
	 */
	function close()
	{
		// если сокет открыт
		$result = !$this->isOpened()

			// закрываем сокет
			|| fclose($this->_socket);

		// уничтожаем ресурс
		$this->_socket = null;

		return $result;
	}
}
