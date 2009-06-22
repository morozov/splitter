<?php

/**
 * Базовый класс объектов, сохраняющий скачанный файл.
 *
 */
abstract class Splitter_Storage_Abstract {

	/**
	 * Имя файла, в котором будут сохранены данные.
	 *
	 * @var string
	 */
	protected $filename;

	/**
	 * Фабрика хранилищ. Создает хранилище с указанными параметрами.
	 *
	 * @param string $type
	 * @return Splitter_Storage_Abstract
	 */
	public static function factory($type) {
		$arguments = func_get_args();
		array_shift($arguments);
		$reflection = new ReflectionClass('Splitter_Storage_' . ucfirst($type));
		return count($arguments) > 0
			? $reflection->newInstanceArgs($arguments)
			: $reflection->newInstance();
	}

	/**
	 * Возвращает имя файла, в котором будут сохранены данные.
	 *
	 * @return string
	 */
	public function getFileName() {
		return $this->filename;
	}

	/**
	 * Устанавливает имя файла, в котором будут сохранены данные.
	 *
	 * @return string
	 */
	public function setFileName($fileName) {

		if (isset($GLOBALS['rename'])) {
			$fileName = $GLOBALS['rename']->rename($fileName);
		}

		// здесь нужно убедиться, что установлена верная локаль
		if ($fileName != basename($fileName)) {
			throw new Splitter_Storage_Exception(sprintf('No path allowed in filename, "%s" is given', $fileName));
		}

		$this->_setFileName($fileName);
	}

	/**
	 * Непосредственно, без преобразований, устанавливает имя файла.
	 *
	 * @param string $fileName
	 * @throws Splitter_Storage_Exception
	 */
	protected function _setFileName($fileName) {
		$this->filename = $fileName;
	}

	/**
	 * Преобразует строку из UTF-8 в Windows-1251, если есть такая возможность.
	 *
	 * @param string $string
	 * @return string
	 */
	public static function utf2win($string) {

		$encoding_from = 'utf-8';
		$encoding_to = 'windows-1251';

		if (extension_loaded('iconv')) {
			$string = iconv($encoding_from, $encoding_to, $string);
		} elseif (extension_loaded('mbstring')) {
			$string = mb_convert_encoding($string, $encoding_to, $encoding_from);
		}

		return $string;
	}
}
