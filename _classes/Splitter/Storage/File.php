<?php

/**
 * @package	 Splitter
 * @subpackage  storage
 * @version	 $Id$
 */
/**
 * Класс объектов, сохраняющий скачанный файл в файловой системе.
 *
 * @package	 Splitter
 * @subpackage  storage
 * @see		 Splitter_Storage_Abstract
 */
class Splitter_Storage_File extends Splitter_Storage_Abstract
{
	/**
	 * Ресурс записи в файл файловой системы.
	 *
	 * @var	 resource
	 */
	var $_resource;

	/**
	 * Возвращает позицию, с которой нужно докачивать файл.
	 *
	 * @return  integer
	 */
	function getResumePosition()
	{
		clearstatcache();

		// определяем путь, по которому будем сохранять файл
		$path = $this->_getSavePath();

		return file_exists($path)
			? filesize($path) : parent::getResumePosition();
	}

	/**
	 * Пытается создать ресурс записи в файл файловой системы. Возвращает
	 * результат попытки.
	 *
	 * @param   integer $size
	 * @return  boolean
	 */
	function open($size)
	{
		parent::open($size);

		// сбрасываем ссылку на ресурс файла для записи
		$this->_resource = null;

		// получаем полный путь файла
		$path = $this->_getSavePath();

		$imp = $this->_getImplementation();

		$this->_resource = $imp->open($path);

		return $this->_opened();
	}

	/**
	 * Пишет данные в файл.
	 *
	 * @param   string   $data   Данные для записи
	 * @return  boolean		  Были ли данные успешно записаны
	 */
	function write($data)
	{
		return strlen($data) == $this->_write($data);
	}

	/**
	 * Возвращает содержимое хранилища.
	 *
	 * @return  string
	 */
	function getContents() {
		return file_get_contents($this->getSavePath());
	}

	/**
	 * Обрезает файл до указанной длины. �?спользуется, если сервер не
	 * поддерживает докачку.
	 *
	 * @param   integer  $size
	 * @return  boolean
	 */
	function truncate($size)
	{
		return ftruncate($this->_resource, $size);
	}

	/**
	 * Определяет, разрешеено ли указанное имя файла при сохранении в данный тип
	 * хранилища.
	 *
	 * @param   string  $fileName
	 * @return  boolean
	 */
	function _isFilenameAllowed($fileName)
	{
		return parent::_isFilenameAllowed($fileName)

			// запрещаем закачивать на сервер файлы .htaccess и т.п.
			&& 0 !== strpos($fileName, '.ht');
	}

	/**
	 * Устанавливает путь, по которому будет сохранен скачиваемый файл.
	 *
	 */
	function _setTarget($target)
	{
		$imp = $this->_getImplementation($target);
		$target = $imp->transformPath($target);
		parent::_setTarget($target);
	}

	/**
	 * Завершает сохранение данных. Закрывает хранилище.
	 *
	 * @return  boolean
	 */
	function _close()
	{
		$imp = $this->_getImplementation();

		$result = !$this->_opened() || $imp->close($this->_resource);

		// уничтожаем ресурс
		$this->_resource = null;

		return $result;
	}

	/**
	 * Возвращает сообщение об успешном сохранении данных.
	 *
	 * @return  mixed
	 */
	function _getSucessMessage()
	{
		$path = $this->_getSavePath();

		return 'Файл успешно сохранен в <a href="'
			. str_replace (DIRECTORY_SEPARATOR, '/',  $path)
			. '" target="_blank">' . realpath($path) . '</a>';
	}

	/**
	 * Возвращает полный путь файла, в который будут сохраняться данные.
	 *
	 * @return  string
	 */
	function _getSavePath()
	{
		return $this->_target . $this->_fileName;
	}

	/**
	 * Ваозвращает, открыт ли целевой файл для записи.
	 *
	 * @return  boolean
	 */
	function _opened()
	{
		return is_resource($this->_resource);
	}

	/**
	 * Пишет данные в файл. Возвращает количество записанных байт или FALSE
	 * в случае неудачи.
	 *
	 * @param   string $data
	 * @return  mixed
	 */
	function _write($data)
	{
		return $this->_opened()
			? fwrite($this->_resource, $data) : false;
	}

	/**
	 * Возвращает реализацию хранения файла.
	 *
	 * @return  Splitter_Storage_File_Abstract
	 */
	function _getImplementation($path = null)
	{
		preg_match('|^([a-z0-9]+)://|i', is_null($path) ? $this->_target : $path, $scheme);

		switch (isset($scheme[1]) ? $scheme[1] : null)
		{
			case 'ftp':
				$className = 'Splitter_Storage_File_Ftp';
				break;

			default:
				$className = 'Splitter_Storage_File_Local';
				break;
		}

		return new $className;
	}
}