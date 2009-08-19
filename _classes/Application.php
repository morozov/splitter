<?php

/**
 * Вообще-то это всего лишь автоматически настраивающийся реестр
 * объектов-синглтонов, плюс пространство имён кое-каких функций.
 * Стоит подумать о расформировании.
 *
 * @version $Id$
 */
abstract class Application {

	/**
	 * Возвращает объект пользовательского запроса.
	 *
	 * @return Splitter_Request_Abstract
	 */
	public static function getRequest() {
		return self::getObject('request');
	}

	/**
	 * Возвращает объект ответа пользователю.
	 *
	 * @return Splitter_Response_Abstract
	 * @static */
	public static function getResponse() {
		return self::getObject('response');
	}

	/**
	 * Возвращает, запущено ли приложение под Windows®.
	 *
	 * @return boolean
	 * @static */
	public static function isWindows() {
		return 'WIN' == substr(PHP_OS, 0, 3);
	}

	/**
	 * Возвращает объект приложения.
	 *
	 * @return object
	 */
	private static function getObject($name) {
		static $context = array(),
			$mode = null;
		if (!isset($context[$name])) {
			if (!isset($mode)) {
				$mode = self::getMode();
			}
			$class = sprintf('Splitter_%s_%s', ucfirst($name), $mode);
			$context[$name] = new $class;
		}
		return $context[$name];
	}

	/**
	 * Возвращает наименование интерфейса, через который запущено приложение.
	 *
	 * @return string
	 */
	private function getMode()
	{
		return isset($_SERVER['SERVER_PROTOCOL'])
			? ('POST' == $_SERVER['REQUEST_METHOD'] ? 'web' : 'proxy')
			: 'cli';
	}
}
