<?php

/**
 * @package	 Splitter
 * @subpackage  storage
 * @version	 $Id$
 */
/**
 * Реализация сохранения данных в локальной файловой системе.
 *
 * @package	 Splitter
 * @subpackage  storage.file
 * @see		 abstract_Object
 * @abstract
 */
class Splitter_Storage_File_Local extends Splitter_Storage_File_Abstract {

	/**
	 * Открывает файл по указанному пути для записи.
	 *
	 * @param string  $path
	 * @return mixed
	 */
	public function open($path) {
		$resource = false;

		$dir = dirname($path);

		switch (false)
		{
			// проверяем существование целевой директории
			case is_dir($dir) || $this->mkdir($dir):
				trigger_error('Невозможно создать целевую директорию "' . $dir . '"', E_USER_WARNING);
				break;

			// пытаемся открыть файл на запись
			case $file = parent::open(
				Application::isWindows()
					? Splitter_Storage_Abstract::utf2win($path)
					: $path);
				break;

			// пытаемся установить блокировку. не ждем, если файл заблокирован,
			// т.к. это означает повторный запуск скачивания одного и того же файла
			case flock($file, LOCK_EX | LOCK_NB):
				trigger_error('Файл "' . $path . '" заблокирован другим процессом', E_USER_WARNING);
				break;

			// если всё получилось, сохраняем ссылку на созданный ресурс
			default:
				$resource = $file;
				break;
		}

		return $resource;
	}

	/**
	 * Закрывает указанный ресурс.
	 *
	 * @param   resource	$resource
	 * @return  boolean
	 */
	function close($resource)
	{
		return flock($resource, LOCK_UN) && parent::close($resource);
	}

	/**
	 * Преобразует путь в соответствии со спецификой конкретной реализации.
	 *
	 * @param   string  $path
	 * @return  string
	 */
	function transformPath($path)
	{
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
	 * @return  boolean
	 */
	function mkdir($path)
	{
		// убираем закрывающий слэш, иначе после разбиении строки на секции
		// последним элементом массива будет пустая строка, а следовательно,
		// при сборке в конце появится второй закрывающий слэш
		$path = preg_replace('|' . preg_quote(DIRECTORY_SEPARATOR) . '+$|', '', $path);

		// разбиваем путь на секции
		$sections = explode(DIRECTORY_SEPARATOR, $path);

		// пытаемся определить пути директорий, которые нужно создать
		if (false !== ($paths = $this->_getPathsToCreate($sections)))
		{
			foreach ($paths as $path)
			{
				// пытаемся создать поддиректорию
				if (!@mkdir($path))
				{
					return false;
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Возвращает пути директорий, которые нужно создать.
	 *
	 * @param   array   $sections
	 * @return  array
	 */
	function _getPathsToCreate($sections)
	{
		$result = array();

		for ($i = count($sections); $i >= 1; $i--)
		{
			// формируем проверяемый путь
			$path = $this->_getSubPath($sections, $i);

			// если указанный путь существует
			if (file_exists($path))
			{
				// если существующий путь - не директория, путь создать не удастся
				if (!is_dir($path))
				{
					return false;
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
	 * @param   array   $sections
	 * @param   integer $length
	 * @return  string
	 */
	function _getSubPath($sections, $length)
	{
		$path = implode(DIRECTORY_SEPARATOR, array_slice($sections, 0, $length));

		return 0 == strlen($path) ? DIRECTORY_SEPARATOR : $path;
	}
}
