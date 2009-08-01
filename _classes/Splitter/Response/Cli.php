<?php

/**
 * Класс ответа приложения для режима командной строки.
 *
 * @version $Id$
 */
class Splitter_Response_Cli extends Splitter_Response_Abstract {

	/**
	 * Windows ли?
	 *
	 * @var boolean
	 */
	protected $is_windows;

	/**
	 * Конструктор.
	 */
	public function __construct() {
		$this->is_windows = Application::isWindows();
	}

	/**
	 * Проекция типов сообщений на знаки, которыми они помечаются в журнале.
	 *
	 * @var array
	 */
	protected static $types = array(
		'request'  => '<',
		'response' => '>',
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
		$first = true;
		$sign = isset(self::$types[$type]) ? self::$types[$type] : ' ';
		$date = $this->getDate();
		foreach (preg_split('/[\r\n]+/', $message) as $line) {
			$this->write(
				sprintf('%s | %s | %s' . PHP_EOL, $sign , $date, $line)
			);
			if ($first) {
				$sign = ' ';
				$date = str_repeat(' ', strlen($date));
				$first = false;
			}
		}
	}

	/**
	 * Вызывет указанный метод компонента представления с переданными
	 * аргументами.
	 *
	 * @param string $method
	 * @param array $arguments
	 */
	public function __call($method, array $arguments) {
		$this->write(
			sprintf('%s(%s)' . PHP_EOL, $method, substr(json_encode($arguments), 1, -1))
		);
	}

	/**
	 * Выводит сообщение в терминал. Попутно преобразует кодировку для терминала
	 * Windows. Использование обрабочика буфера вывода здесь не подходит, т.к.
	 * вывод должен быть небуферизованным.
	 *
	 * @param string $message
	 */
	protected function write($message) {
		if ($this->is_windows) {
			$message = mb_convert_encoding($message, 'cp866', 'utf-8');
		}
		echo $message;
	}
}
