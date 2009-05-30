<?php

/**
 * @package	 Splitter
 * @subpackage  System.run
 * @version	 $Id$
 */
/**
 * Реализация запуска процесса в фоне под Unix.
 *
 * @access	  public
 * @package	 Splitter
 * @subpackage  System.run
 * @see		 abstract_Object
 */
class System_Run_Unix extends System_Run_Abstract
{
	/**
	 * Путь к исполняемому файлу PHP.
	 *
	 * @access  protected
	 * @var	 string
	 */
	var $EXECUTABLE_PATH = '/usr/bin/php';

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
		parent::run($args);

		$pid = exec($this->_getCommand($args) . ' >/dev/null & echo \$!');

		return is_numeric($pid) ? (int)$pid : false;
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
		return !Application::isWindows();
	}
}
