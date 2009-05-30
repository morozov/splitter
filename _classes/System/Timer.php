<?php

/**
 * @package	 Splitter
 * @subpackage  System
 * @version	 $Id$
 */
/**
 * Системный таймер.
 *
 * @access	  public
 * @version	 $Id$
 * @package	 Splitter
 * @subpackage  System
 * @see		 abstract_Object
 */
class System_Timer
{
	/**
	 * Временная метка начала работы таймера.
	 *
	 * @var	 float
	 * @access  private
	 */
	var $_startTime = 0;

	/**
	 * Временная метка окончания работы таймера.
	 *
	 * @var	 float
	 * @access  private
	 */
	var $_endTime = 0;

	/**
	 * Конструктор. Автоматически инициализирует запуск.
	 *
	 * @access  public
	 * @param   boolean  $autoStart
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
	 * @access  public
	 */
	function start()
	{
		$this->_startTime = $this->_getMicroTime();
		$this->_endTime = null;
	}

	/**
	 * Останавливает таймер.
	 *
	 * @access  public
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
	 * @access  public
	 * @return  float
	 */
	function getTime()
	{
		return (is_null($this->_endTime)
			? $this->_getMicroTime() : $this->_endTime) - $this->_startTime;
	}

	/**
	 * Возвращет текущую временную метку в микросекундах.
	 *
	 * @access  protected
	 * @return  float
	 */
	function _getMicroTime()
	{
		$mtime = explode(' ', microtime());
		$mtime = (float)$mtime[1] + (float)$mtime[0];
		return $mtime;
	}
}
