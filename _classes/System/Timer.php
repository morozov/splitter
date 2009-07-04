<?php

/**
 * Системный таймер.
 *
 * @version $Id$
 */
class System_Timer
{
	/**
	 * Временная метка начала работы таймера.
	 *
	 * @var float

	 */
	var $_startTime = 0;

	/**
	 * Временная метка окончания работы таймера.
	 *
	 * @var float

	 */
	var $_endTime = 0;

	/**
	 * Конструктор. Автоматически инициализирует запуск.
	 *
	 * @param boolean  $autoStart
	 */
	function System_Timer($autoStart = true)
	{
		if ($autoStart)
		{
			$this->start();
		}
	}

	/**
	 * Запускает таймер.
	 *
	 */
	function start()
	{
		$this->_startTime = $this->_getMicroTime();
		$this->_endTime = null;
	}

	/**
	 * Останавливает таймер.
	 *
	 */
	function stop()
	{
		$this->_endTime = $this->_getMicroTime();
	}

	/**
	 * Возвращет длину промежутка времени между стартом и текущей временной
	 * меткой или остановкой в зависимости от того, запущен в данный момент
	 * таймер или нет соответственно.
	 *
	 * @return float
	 */
	function getTime()
	{
		return (is_null($this->_endTime)
			? $this->_getMicroTime() : $this->_endTime) - $this->_startTime;
	}

	/**
	 * Возвращет текущую временную метку в микросекундах.
	 *
	 * @return float
	 */
	function _getMicroTime()
	{
		$mtime = explode(' ', microtime());
		$mtime = (float)$mtime[1] + (float)$mtime[0];
		return $mtime;
	}
}
