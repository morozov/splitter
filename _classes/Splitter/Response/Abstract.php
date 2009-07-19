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
	 * Типы сообщений, которые можно вывести в ответ приложения.
	 *
	 * @var array
	 */
	protected static $types = array('info', 'request', 'response', 'error', 'debug');

	/**
	 * Вызывет указанный метод компонента представления с переданными
	 * аргументами.
	 *
	 * @param string $method
	 * @param array $arguments
	 */
	public function __call($method, array $arguments) {
		if (in_array($method, self::$types)) {
			foreach ($arguments as $message) {
				$this->write($message, $method);
			}
		} else {
			$this->onCallFailed($method, $arguments);
		}
	}

	/**
	 * Выводит сообщение указанного типа.
	 *
	 * @param string $message
	 * @param string $type
	 */
	abstract protected function write($message, $type);

	/**
	 * Обрабатывает вывод несуществующего метода.
	 *
	 * @param string $method
	 * @param array $arguments
	 * @throws Splitter_Response_Exception
	 */
	protected function onCallFailed($method, array $arguments) {
		throw new Splitter_Response_Exception('Call to undefined method "' . $method . '" on '. get_class($this));
	}

	/**
	 * Выводит отладочное сообщение через var_dump().
	 */
	public function debug() {
		$args = func_get_args();
		ob_start();
		call_user_func_array('var_dump', $args);
		$this->write(ob_get_clean(), 'debug');
	}
}
