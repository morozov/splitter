<?php

/**
 * @package	 Splitter
 * @subpackage  storage
 * @version	 $Id$
 */
/**
 * Базовый класс реализация сохранения даннвх в файл.
 *
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
	 * @var	 string
	 */
	var $FOPEN_MODE = 'ab';

	/**
	 * Открывает файл по указанному пути для записи.
	 *
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
	 * @param   string  $path
	 * @return  string
	 */
	function transformPath($path)
	{
		return $path;
	}
}
