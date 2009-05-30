<?php

/**
 * @package	 Splitter
 * @subpackage  response
 * @version	 $Id$
 */
/**
 * Класс ответа приложения для веб-интерфейса.
 *
 * @package	 Splitter
 * @subpackage  response
 * @see		 Splitter_Response_Abstract
 */
class Splitter_Response_Web extends Splitter_Response_Abstract {

	/**
	 * Формат отображения времени.
	 *
	 */
	const TIME_FORMAT = 'd.m.Y H:i:s';

	/**
	 * Объект, методы которого нужно вызывать на клиенте.
	 *
	 */
	const CALLEE = 'parent.controller';

	/**
	 * Конструктор.
	 *
	 */
	function Splitter_Response_Web() {
		// отправляем заголовок ответа
		$this->_writeHeader();

		// назначаем отправку ответа по окончании работы скрипта
		register_shutdown_function(array($this, '_writeFooter'));
	}

	/**
	 * Вызывет указанный метод компонента представления с переданными
	 * аргументами.
	 *
	 * @param   string   $method   Наименование метода
	 */
	function call($method) {
		$args = array();

		// проходим по аргументам функции начиная со второго
		for ($i = 1; $i < func_num_args(); $i ++) {
			// оборачиваем аргумент в строку и добавляем в массив
			$args[] = json_encode(func_get_arg($i));
		}

		// выдаем скрипт вызова
		$this->_write('<script type="text/javascript">' . self::CALLEE . '.' . $method . '(' . implode(',', $args) . ');</script>' . PHP_EOL);
	}

	/**
	 * Выводит сообщение в журнал.
	 *
	 * @param   string  $message
	 * @param   string  $type
	 */
	function write($message, $type = 'info') {
		// разбиваем сообщение на строки
		$this->call('trace', $type, date(self::TIME_FORMAT), preg_split("/[\r\n]+/", $message));
	}

	/**
	 * Выводит строку.
	 *
	 * @param   string $string
	 */
	function _write($string) {
		echo $string;

		// сбрасываем буферы
		flush(); @ob_flush();
	}

	/**
	 * Отправляет заголовок ответа.
	 *
	 */
	function _writeHeader() {
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' . PHP_EOL
			. '<html xmlns="http://www.w3.org/1999/xhtml">' . PHP_EOL
			. '<head>' . PHP_EOL
			. '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . PHP_EOL;
	}

	/**
	 * Отправляет окончание ответа.
	 *
	 */
	function _writeFooter() {
		echo '</head>' . PHP_EOL . '</html>' . PHP_EOL;
	}
}
