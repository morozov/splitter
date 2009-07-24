<?php

/**
 * Объектная обертка для сокетов PHP.
 *
 * @version $Id$
 */
class Splitter_Socket {

	/**
	 * Время, в течение которого скрипт пытается открыть сокет, сек.
	 */
	const CONNECTION_TIMEOUT = 4.00;

	/**
	 * Сокет-ресурс.
	 *
	 * @var resource
	 */
	var $resource;

	/**
	 * Конструктор.
	 *
	 * @param string   $host
	 * @param integer  $port
	 */
	public function __construct($host, $port) {
		$errmsg = $errno = null;
		if (!$resource = @fsockopen($host, $port, $errno, $errmsg, self::CONNECTION_TIMEOUT)) {
			throw new Splitter_Socket_Exception($errmsg, $errno);
		}
		$this->resource = $resource;
	}

	/**
	 * Читает до $length байт из сокета.
	 *
	 * @param integer $length
	 * @return string | false
	 */
	public function read($length) {
		return fread($this->resource, $length);
	}

	/**
	 * Читает до $length байт или до символа перевода строки из сокета.
	 *
	 * @return string | false
	 */
	public function gets() {
		return fgets($this->resource);
	}

	/**
	 * Пишет строку в сокет.
	 *
	 * @param string $string
	 * @return boolean
	 */
	public function write($string) {
		return fwrite($this->resource, $string);
	}

	/**
	 * Возвращает, достигнут ли конец файла.
	 *
	 * @return boolean
	 */
	public function eof() {
		return feof($this->resource);
	}

	/**
	 * Деструктор.
	 */
	public function __destruct() {
		fclose($this->resource);
	}
}
