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
	protected $target;

	/**
	 * Имя файла, в котором будут сохранены данные.
	 *
	 * @var	 string
	 */
	protected $filename;

	/**
	 * Размер данных, которые предполагается записать.
	 *
	 * @var	 integer
	 */
	protected $size;

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
	function factory($type, $target) {
		$class = 'Splitter_Storage_' . ucfirst($type);
		return new $class($target);
	}

	/**
	 * Возвращает имя файла, в котором будут сохранены данные.
	 *
	 * @return  string
	 */
	function getFileName()
	{
		return $this->filename;
	}

	/**
	 * Устанавливает имя файла, в котором будут сохранены данные.
	 *
	 * @return  string
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
		$this->size = $size;

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
		$this->target = $target;
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
