<?php

/**
 * @package	 Splitter
 * @subpackage  System.run
 * @version	 $Id$
 */
/**
 * Базовый класс реализаций запуска процесса в фоне.
 *
 * @access	  public
 * @package	 Splitter
 * @subpackage  System.run
 * @see		 abstract_Object
 * @abstract
 */
abstract class System_Run_Abstract
{
	/**
	 * Путь к исполняемому файлу PHP.
	 *
	 * @access  protected
	 * @var	 string
	 */
	var $EXECUTABLE_PATH;

	/**
	 * Запускает фоновый процесс PHP с указанными аргументами. Возвращает
	 * идентификатор процесса или FALSE в случае неудачи.
	 *
	 * @access  public
	 * @param   string   $args
	 * @return  integer
	 */
	function run($args)
	{
		if (!is_executable($this->EXECUTABLE_PATH))
		{
			trigger_error('Файл "' . $this->EXECUTABLE_PATH . '" недоступен для исполнения', E_USER_WARNING);
		}
	}

	/**
	 * Возвращает, подходит ли данная реализация для платформы, на которой
	 * запущено приложение.
	 *
	 * @access  public
	 * @return  boolean
	 */
	function suits()
	{
		return false;
	}

	/**
	 * Возвращает команду процесса.
	 *
	 * @access  public
	 * @param   string   $cmd
	 * @return  integer
	 */
	function _getCommand($args)
	{
		return $this->EXECUTABLE_PATH  . ' -q ' . $args;
	}
}
