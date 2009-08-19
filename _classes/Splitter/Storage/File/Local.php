<?php

/**
 * Реализация сохранения данных в локальной файловой системе.
 *
 */
class Splitter_Storage_File_Local extends Splitter_Storage_File_Abstract {

	/**
	 * Открывает файл по указанному пути для записи.
	 *
	 * @param string $path
	 * @return mixed
	 */
	public function open($path) {

		$dir = dirname($path);

		if (!is_dir($dir) && !@mkdir($dir, 0777, true)) {
			throw new Splitter_Storage_Exception('Could not create directory "' . $path . '"');
		}

		$resource = parent::open(Splitter_Os_Windows::toACPCharset($path));

		if (!flock($resource, LOCK_EX | LOCK_NB)) {
			throw new Splitter_Storage_Exception('Файл "' . $path . '" заблокирован другим процессом');
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
		if (!flock($resource, LOCK_UN)) {
			throw new Splitter_Storage_Exception('Couldn’t unlock file');
		}
		parent::close($resource);
	}

	/**
	 * Преобразует путь в соответствии со спецификой конкретной реализации.
	 *
	 * @param string  $path
	 * @return string
	 */
	public function transformPath($path) {

		// заменяем слэши на бэкслэши для Win32. оставляем относительный путь,
		// т.к. из него потом ссылку на скачанный файл делать
		$path = str_replace('/', DIRECTORY_SEPARATOR, $path);

		// добавялем закрывающий слэш только для непустого пути, иначе получится
		// корень ФС вместо текущей директории
		$path = preg_replace('|(.+[^' . preg_quote(DIRECTORY_SEPARATOR) . '])$|',
			'$1' . DIRECTORY_SEPARATOR, $path);

		return $path;
	}
}
