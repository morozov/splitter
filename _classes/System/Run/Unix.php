<?php

/**
 * Реализация запуска процесса в фоне под Unix.
 *
 * @version $Id$
 */
class System_Run_Unix extends System_Run_Abstract
{
	/**
	 * Путь к исполняемому файлу PHP.
	 *
	 * @var string
	 */
	var $EXECUTABLE_PATH = '/usr/bin/php';

	/**
	 * Запускает фоновый процесс PHP с указанными аргументами. Возвращает
	 * идентификатор процесса или FALSE в случае неудачи.
	 *
	 * @param string   $args
	 * @return integer
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
	 * @return boolean
	 */
	function suits()
	{
		return !Application::isWindows();
	}
}
