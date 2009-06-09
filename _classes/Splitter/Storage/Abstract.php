<?php

/**
 * @package	 Splitter
 * @subpackage  storage
 * @version	 $Id$
 */
/**
 * Базовый класс объектов, сохраняющий скачанный файл.
 *
 * @package	 Splitter
 * @subpackage  storage
 * @see		 abstract_Object
 * @abstract
 */
abstract class Splitter_Storage_Abstract
{
	/**
	 * Абстрактная цель сохранения данных. Конкретизируется в производных классах.
	 *
	 * @var	 string
	 */
	var $_target;

	/**
	 * Имя файла, в котором будут сохранены данные.
	 *
	 * @var	 string
	 */
	var $_fileName;

	/**
	 * Размер данных, которые предполагается записать.
	 *
	 * @var	 integer
	 */
	var $_size;

	/**
	 * Конструктор.
	 *
	 * @param   string   $target
	 * @return  Splitter_Storage_Abstract
	 */
	function Splitter_Storage_Abstract($target)
	{
		// устанавливаем цель сохранения данных
		$this->_setTarget($target);
	}

	/**
	 * Фабрика хранилищ. Создает хранилище с указанными параметрами.
	 *
	 * @param   string	  $type
	 * @param   string	  $target
	 * @param   integer	 $splitSize
	 * @return  Splitter_Storage_Abstract
	 */
	function factory($type, $target, $splitSize = 0) {
		if ($splitSize > 0) {
			$storage = new Splitter_Storage_Intf(
				$type, $target, $splitSize
			);
		} else {
			$class = 'Splitter_Storage_' . ucfirst($type);
			$storage = new $class($target);
		}
		return $storage;
	}

	/**
	 * Возвращает имя файла, в котором будут сохранены данные.
	 *
	 * @return  string
	 */
	function getFileName()
	{
		return $this->_fileName;
	}

	/**
	 * Устанавливает имя файла, в котором будут сохранены данные.
	 *
	 * @return  string
	 */
	function setFileName($fileName)
	{
		// нужно убедиться, что установлена верная локаль
		$fileName = basename($fileName);

		if (isset($GLOBALS['rename']))
		{
			$fileName = $GLOBALS['rename']->rename($fileName);
		}

		if (!$this->_isFilenameAllowed($fileName))
		{
			trigger_error('Указанное имя файла "' . $fileName . '" запрещено', E_USER_WARNING);
		}

		$this->_fileName = $fileName;
	}

	/**
	 * Возвращает позицию, с которой нужно возобновить скачивание файла.
	 * Реализуется в производных классах.
	 *
	 * @return  integer
	 */
	function getResumePosition()
	{
		return 0;
	}

	/**
	 * Возвращает, нужно ли сохранять данные в хранилище.
	 *
	 * @return  boolean
	 */
	function isDownloadNeeded()
	{
		return true;
	}

	/**
	 * Открывает хранилище.
	 *
	 * @param   integer $size
	 * @return  boolean
	 */
	function open($size)
	{
		$this->_size = $size;

		return true;
	}

	/**
	 * Пишет данные в хранилище.
	 *
	 * @param   string $data
	 * @return  boolean
	 */
	abstract function write($data);

	/**
	 * Завершает сохранение данных. Закрывает файл.
	 *
	 * @return  boolean
	 */
	function close()
	{
		$closed = $this->_close();

		if ($closed && is_string($message = $this->_getSucessMessage()))
		{
			$response =& Application::getResponse();
			$response->write($message);
		}

		return $closed;
	}

	/**
	 * Возвращает содержимое хранилища.
	 *
	 * @return  string
	 */
	abstract function getContents();

	/**
	 * Обрезает сохраняемый файл до указанной длины. используется в случае, если
	 * сервер не поддерживает докачку.
	 *
	 * @access  public
	 * @param   integer  $size
	 * @return  boolean
	 */
	function truncate($size)
	{
		// по умолчанию хранилище не умеет обрезать данные. чтоб умело, надо это
		// реализовать
		return false;
	}

	/**
	 * Возвращает количество свободного места в хранилище.
	 *
	 * @return  integer
	 */
	function getFreeSpace()
	{
		return null;
	}

	/**
	 * Определяет, разрешеено ли указанное имя файла при сохранении в данный тип
	 * хранилища.
	 *
	 * @access  protected
	 * @param   string  $fileName
	 * @return  boolean
	 */
	function _isFilenameAllowed($fileName)
	{
		return true;
	}

	/**
	 * Завершает сохранение данных, закрывает хранилище. Может быть реализован
	 * в производных классах.
	 *
	 * @access  protected
	 * @return  boolean
	 */
	function _close()
	{
		return true;
	}

	/**
	 * Возвращает сообщение об успешном сохранении данных. По умолчанию никакого
	 * сообщения нет, реализуется в производных классах.
	 *
	 * @access  protected
	 * @return  mixed
	 */
	function _getSucessMessage()
	{
		return null;
	}

	/**
	 * Устанавливает цель сохранения данных.
	 *
	 * @access  protected
	 * @param   string
	 */
	function _setTarget($target)
	{
		$this->_target = $target;
	}
}
