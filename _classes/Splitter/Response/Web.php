<?php

/**
 * Класс ответа приложения для веб-интерфейса.
 *
 * @version $Id$
 */
class Splitter_Response_Web extends Splitter_Response_Abstract {

	/**
	 * Объект, методы которого нужно вызывать на клиенте.
	 */
	const CALLEE = 'parent.controller';

	/**
	 * Конструктор.
	 */
	public function __construct() {
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' . PHP_EOL
			. '<html xmlns="http://www.w3.org/1999/xhtml">' . PHP_EOL
			. '<head>' . PHP_EOL
			. '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . PHP_EOL;
	}

	/**
	 * Деструктор.
	 */
	public function __destruct() {
		echo '</head>' . PHP_EOL . '</html>' . PHP_EOL;
	}

	/**
	 * Записывает сообщение в журнал.
	 *
	 * @param string $message
	 * @param string $type
	 */
	public function log($message, $type = null) {
		$arguments = array($message, $this->getDate());
		if (null !== $type) {
			$arguments[] = $type;
		}
		$this->callClient('log', $arguments);
	}

	/**
	 * Вызывет указанный метод компонента представления с переданными
	 * аргументами.
	 *
	 * @param string $method
	 * @param array $arguments
	 */
	public function __call($method, array $arguments) {
		$this->callClient($method, $arguments);
	}

	/**
	 * Вызывет метод на стороне клиента.
	 *
	 * @param string $method
	 * @param array $arguments
	 */
	protected function callClient($method, array $arguments) {
		echo '<script type="text/javascript">' . self::CALLEE . '.' . $method
			. '(' . implode(',', array_map('json_encode', $arguments)) . ');</script>' . PHP_EOL;
		flush();
	}
}
