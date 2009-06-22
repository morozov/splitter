<?php

/**
 * Базовый класс реализация сохранения даннвх в файл.
 *
 */
abstract class Splitter_Storage_File_Abstract {

	/**
	 * Режим открытия файлов для записи.
	 *
	 * @var string
	 */
	protected $fopen_mode = 'ab';

	/**
	 * Открывает файл по указанному пути для записи.
	 *
	 * @param string $path
	 * @return mixed
	 */
	public function open($path) {
		if (!$resource = fopen($path, $this->fopen_mode)) {
			throw new Splitter_Storage_Exception('Couldn’t open file "' . $path . '" for writing');
		}
		return $resource;
	}

	/**
	 * Закрывает указанный ресурс.
	 *
	 * @param resource $resource
	 * @return boolean
	 */
	public function close($resource) {
		if (!fclose($resource)) {
			throw new Splitter_Storage_Exception('Couldn’t close file');
		}
	}

	/**
	 * Преобразует путь в соответствии со спецификой конкретной реализации.
	 *
	 * @param string $path
	 * @return string
	 */
	public function transformPath($path) {
		return $path;
	}
}
