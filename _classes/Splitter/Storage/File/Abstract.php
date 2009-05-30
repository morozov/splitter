<?php

/**
 * @package	 Splitter
 * @subpackage  storage
 * @version	 $Id$
 */
/**
 * Базовый класс реализация сохранения даннвх в файл.
 *
 * @access	  public
 * @package	 Splitter
 * @subpackage  storage.file
 * @see		 abstract_Object
 * @abstract
 */
abstract class Splitter_Storage_File_Abstract
{
	/**
	 * Режим открытия файлов для записи.
	 *
	 * @access  protected
	 * @var	 string
	 */
	var $FOPEN_MODE = 'ab';

	/**
	 * Открывает файл по указанному пути для записи.
	 *
	 * @access  public
	 * @param   string  $path
	 * @return  mixed
	 */
	function open($path)
	{
		if (false === ($file = fopen($path, $this->FOPEN_MODE)))
		{
			trigger_error('Невозможно открыть файл "' . $path . '" для записи', E_USER_WARNING);
		}

		return $file;
	}

	/**
	 * Закрывает указанный ресурс.
	 *
	 * @access  public
	 * @param   resource	$resource
	 * @return  boolean
	 */
	function close($resource)
	{
		return fclose($resource);
	}

	/**
	 * Преобразует путь в соответствии со спецификой конкретной реализации.
	 *
	 * @access  public
	 * @param   string  $path
	 * @return  string
	 */
	function transformPath($path)
	{
		return $path;
	}
}
