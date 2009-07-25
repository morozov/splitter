<?php

/**
 * Класс ответа приложения для режима командной строки.
 *
 * @version $Id$
 */
class Splitter_Response_Cli extends Splitter_Response_Abstract {

	/**
	 * Проекция типов сообщений на знаки, которыми они помечаются в журнале.
	 *
	 * @var array
	 */
	protected static $types = array(
		'request'  => '<',
		'response' => '<',
		'error'    => '!',
		'debug'    => '*',
	);

	/**
	 * Записывает сообщение в журнал.
	 *
	 * @param string $message
	 * @param string $type
	 */
	public function log($message, $type = null) {
		printf('%s | %s | %s' . PHP_EOL, isset(self::$types[$type]) ? self::$types[$type] : ' ', $this->getDate(), $message);
	}

	/**
	 * Вызывет указанный метод компонента представления с переданными
	 * аргументами.
	 *
	 * @param string $method
	 * @param array $arguments
	 */
	public function __call($method, array $arguments) {
		printf('%s(%s)' . PHP_EOL, $method, implode(',', array_map('json_encode', $arguments)));
	}
}
