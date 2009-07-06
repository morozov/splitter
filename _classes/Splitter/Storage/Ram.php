<?php

// отключаем ограничение используемой памяти
ini_set('memory_limit', -1);

/**
 * Класс объектов, сохраняющий скачанный файл в оперативной памяти.
 *
 * @version $Id$
 */
class Splitter_Storage_Ram extends Splitter_Storage_Abstract {

	/**
	 * Содержимое, записанное в хранилище.
	 *
	 * @var string
	 */
	var $_contents = '';

	/**
	 * Возвращает позицию, с которой нужно докачивать файл.
	 *
	 * @return integer
	 */
	public function getResumePosition() {
		return strlen($this->_contents);
	}

	/**
	 * Пишет данные в файл.
	 *
	 * @param string $data
	 * @return Splitter_Storage_Abstract
	 * @throws Splitter_Storage_Exception
	 */
	public function write($data) {
		$this->_contents .= $data;
		return $this;
	}

	/**
	 * Обрезает файл до указанной длины.
	 *
	 * @param integer $size
	 */
	public function truncate($size) {
		$this->_contents = substr($this->_contents, 0, $size);
	}

	/**
	 * Фиксирует данные в хранилище.
	 *
	 * @throws Splitter_Storage_Exception
	 */
	public function commit() { }

	/**
	 * Возвращает содержимое хранилища.
	 *
	 * @return string
	 */
	public function getContents() {
		return $this->_contents;
	}
}
