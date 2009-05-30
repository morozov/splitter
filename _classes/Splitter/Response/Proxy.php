<?php

/**
 * @package	 Splitter
 * @subpackage  response
 * @version	 $Id$
 */
/**
 * Класс ответа приложения для режима прокси. Заглушка.
 *
 * @package	 Splitter
 * @subpackage  response
 * @see		 Splitter_Response_Abstract
 */
class Splitter_Response_Proxy extends Splitter_Response_Abstract
{
	/**
	 * Конструктор.
	 *
	 */
	function Splitter_Response_Proxy()
	{
		// на случай, если что-то обломится — выставляем статус ответа в 404 для
		// того, чтобы пользовательский агент не сохранял данные
		header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
	}

	/**
	 * Вызывет указанный метод компонента представления с переданными
	 * аргументами.
	 *
	 * @param   string   $method   Наименование метода
	 */
	function call($method)
	{
	}

	/**
	 * Выводит сообщение в журнал.
	 *
	 * @param   string  $message
	 * @param   string  $type
	 */
	function write($message, $type = 'info')
	{
		$fp = fopen('files/log.txt', 'ab');
		fwrite($fp, $message . "\r\n");
		fclose($fp);
	}

	/**
	 * Регистрирует ошибку вызвавшую завершение приложения.
	 *
	 * @param   string  $message
	 */
	function error($message)
	{
		header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found (' . $this->_truncate($message) . ')');
	}

	/**
	 * Обрезает строку до первого перевода строки.
	 *
	 * @param   string  string
	 * @return  string
	 */
	function _truncate($string)
	{
		return current(preg_split('/[\r\n]/', $string, -1, PREG_SPLIT_NO_EMPTY));
	}
}
