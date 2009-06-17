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
	 * Обрезает файл до указанной длины. Используется, если сервер не
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
	 * Непосредственно, без преобразований, устанавливает имя файла.
	 *
	 * @param string $fileName
	 * @throws Splitter_Storage_Exception
	 */
	protected function _setFileName($fileName) {
		if (0 === strpos($fileName, '.ht')) {
			throw new Splitter_Storage_Exception(sprintf('Given filename "%s" is not acceptable', $fileName));
		}
		parent::_setFileName($fileName);
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
			. '" target="_blank">' . $path . '</a>';
	}

	/**
	 * Возвращает полный путь файла, в который будут сохраняться данные.
	 *
	 * @return  string
	 */
	function _getSavePath()
	{
		return $this->target . $this->filename;
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
		preg_match('|^([a-z0-9]+)://|i', is_null($path) ? $this->target : $path, $scheme);

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
