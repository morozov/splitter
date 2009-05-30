<?php

/**
 * @package	 Splitter
 * @subpackage  System.run
 * @version	 $Id$
 */
/**
 * Реализация запуска процесса в фоне под Windows®.
 *
 * @package	 Splitter
 * @subpackage  System.run
 * @see		 abstract_Object
 */
class System_Run_Windows extends System_Run_Abstract
{
	/**
	 * Путь к исполняемому файлу PHP.
	 *
	 * @var	 string
	 */
	var $EXECUTABLE_PATH = '\\usr\\local\\php\\php.exe';

	/**
	 * Запускает фоновый процесс PHP с указанными аргументами. Возвращает
	 * идентификатор процесса или FALSE в случае неудачи.
	 *
	 * @param   string   $args
	 * @return  integer
	 */
	function run($args)
	{
		parent::run($args);

		pclose(popen('start "splitter" ' . $this->_getCommand($args), 'r'));
	}

	/**
	 * Возвращает, подходит ли данная реализация для платформы, на которой
	 * запущено приложение.
	 *
	 * @return  boolean
	 */
	function suits()
	{
		return Application::isWindows();
	}
}
