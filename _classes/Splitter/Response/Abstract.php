<?php

/**
 * Класс ответа приложения.
 *
 * @version $Id$
 */
abstract class Splitter_Response_Abstract
{
	/**
	 * Вызывет указанный метод компонента представления с переданными
	 * аргументами.
	 *
	 * @param string   $method   Наименование метода
	 */
	abstract function call($method);

	/**
	 * Выводит сообщение в журнал.
	 *
	 * @param string  $message
	 * @param string  $type
	 */
	abstract function write($message, $type = 'info');

	/**
	 * Регистрирует ошибку вызвавшую завершение приложения.
	 *
	 * @param string  $message
	 */
	function error($message) { }

	function debug() {
		$args = func_get_args();
		ob_start();
		call_user_func_array('var_dump', $args);
		$message = ob_get_contents();
		ob_end_clean();
		$this->write($message, 'debug');
	}
}
