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

		if (!is_dir($dir)) {
			$this->mkdir($dir);
		}

		$resource = parent::open(
			Application::isWindows()
				? Splitter_Storage_Abstract::utf2win($path)
				: $path);

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

	/**
	 * Проверяет, существует ли директория с указанным путем, и в случае, если нет,
	 * пытается ее создать. Возвращает результат (существует или создана).
	 *
	 * @return boolean
	 */
	private function mkdir($path) {

		// убираем закрывающий слэш, иначе после разбиении строки на секции
		// последним элементом массива будет пустая строка, а следовательно,
		// при сборке в конце появится второй закрывающий слэш
		$path = preg_replace('|' . preg_quote(DIRECTORY_SEPARATOR) . '+$|', '', $path);

		$sections = explode(DIRECTORY_SEPARATOR, $path);

		foreach ($this->getPathsToCreate($sections) as $path) {
			if (!@mkdir($path)) {
				throw new Splitter_Storage_Exception('Could not create directory "' . $path . '"');
			}
		}
	}

	/**
	 * Возвращает пути директорий, которые нужно создать.
	 *
	 * @param array $sections
	 * @return array
	 */
	private function getPathsToCreate($sections) {
		$result = array();
		for ($i = count($sections); $i >= 1; $i--) {
			// формируем проверяемый путь
			$path = $this->getSubPath($sections, $i);
			// если указанный путь существует
			if (file_exists($path)) {
				// если существующий путь — не директория, путь создать не удастся
				if (!is_dir($path)) {
					throw new Splitter_Storage_Exception('"' . $path . '" already exists and it’s not a directory');
				}
				break;
			}
			array_unshift($result, $path);
		}
		return $result;
	}

	/**
	 * Возвращает путь поддиректории указанной длины, собранный из секций.
	 *
	 * @param array $sections
	 * @param integer $length
	 * @return string
	 */
	function getSubPath($sections, $length) {
		$path = implode(DIRECTORY_SEPARATOR, array_slice($sections, 0, $length));
		return 0 == strlen($path) ? DIRECTORY_SEPARATOR : $path;
	}
}
