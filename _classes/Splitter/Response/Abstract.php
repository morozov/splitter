<?php

/**
 * Класс ответа приложения.
 *
 * @version $Id$
 */
abstract class Splitter_Response_Abstract {

	/**
	 * Формат отображения времени.
	 *
	 */
	const TIME_FORMAT = 'd.m.Y H:i:s';

	/**
	 * Возвращает временную метку сообщения.
	 *
	 * @return string
	 */
	protected function getDate() {
		return date(self::TIME_FORMAT);
	}

	/**
	 * Записывает сообщение в журнал.
	 *
	 * @param string $message
	 * @param string $type
	 */
	abstract public function log($message, $type = null);

	/**
	 * Выводит отладочное сообщение через var_dump().
	 *
	 * @param mixed $value1[, mixed $value2...]
	 */
	public function debug() {
		foreach (func_get_args() as $value) {
			ob_start();
			var_dump($value);
			$this->log(ob_get_clean(), 'debug');
		}
	}
}
