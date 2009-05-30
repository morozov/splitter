<?php

/**
 * @package	 Splitter
 * @subpackage  storage
 * @version	 $Id$
 */
/**
 * Класс объектов, сохраняющий скачанный файл в оперативной памяти. �?спользуется
 * для скачивания небольших страниц HTML c фалойвых сервисов.
 *
 * @package	 Splitter
 * @subpackage  storage
 * @see		 Splitter_Storage_Abstract
 */
class Splitter_Storage_Ram extends Splitter_Storage_Abstract
{
	/**
	 * Содержимое, записанное в хранилище.
	 *
	 * @var	 string
	 */
	var $_contents = '';

	/**
	 * Флажок, указывающий, открыто ли хранилище для записи. На самом деле
	 * никакой пользы от него нет, разве что для отладки.
	 *
	 * @var	 boolean
	 */
	var $_isOpened = false;

	/**
	 * Конструктор.
	 *
	 */
	function Splitter_Storage_Ram()
	{
		// этот тип хранилища не поддерживает цель сохранения
		parent::Splitter_Storage_Abstract(null);
	}

	/**
	 * Возвращает позицию, с которой нужно докачивать файл.
	 *
	 * @return  integer
	 */
	function getResumePosition()
	{
		return strlen($this->_contents);
	}

	/**
	 * Открывает хранилище.
	 *
	 * @return  boolean
	 */
	function open()
	{
		// очищаем содержимое
		$this->_contents = '';

		// выставляем флажок "файл открыт"
		$this->_isOpened = true;

		return true;
	}

	/**
	 * Пишет данные в файл.
	 *
	 * @param   string   $data
	 * @return  boolean		  Были ли данные успешно записаны
	 */
	function write($data)
	{
		if ($this->_isOpened)
		{
			$this->_contents .= $data;

			return true;
		}

		return false;
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
		$this->_contents = substr($this->_contents, 0, $size);

		return true;
	}

	/**
	 * Возвращает содержимое хранилища.
	 *
	 * @return  string
	 */
	function getContents()
	{
		return $this->_contents;
	}
}
