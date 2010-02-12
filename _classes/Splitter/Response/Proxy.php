<?php

/**
 * Класс ответа приложения для режима прокси. Заглушка.
 *
 * @version $Id$
 */
class Splitter_Response_Proxy extends Splitter_Response_Abstract {

	/**
	 * Конструктор.
	 *
	 */
	public function __construct() {
		// на случай, если что-то обломится — выставляем статус ответа в 404 для
		// того, чтобы пользовательский агент не сохранял данные
		header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
	}

	/**
	 * Вызывет указанный метод компонента представления с переданными
	 * аргументами.
	 *
	 * @param string $method
	 * @param array $arguments
	 */
	public function __call($method, array $arguments) { }

	/**
	 * Записывает сообщение в журнал.
	 *
	 * @param string $message
	 * @param string $type
	 */
	public function log($message, $type = null) { }

	/**
	 * Регистрирует ошибку вызвавшую завершение приложения.
	 *
	 * @param string  $message
	 */
	public function error($message) {
		header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found (' . $this->truncate($message) . ')');
	}

	/**
	 * Обрезает строку до первого перевода строки.
	 *
	 * @param string  string
	 * @return string
	 */
	protected function truncate($string) {
		return current(preg_split('/[\r\n]/', $string, -1, PREG_SPLIT_NO_EMPTY));
	}
}
