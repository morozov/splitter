<?php

/**
 * Системный таймер.
 *
 * @version $Id$
 */
class System_Timer {

	/**
	 * Временная метка начала работы таймера.
	 *
	 * @var float
	 */
	protected $start_time;

	/**
	 * Временная метка окончания работы таймера.
	 *
	 * @var float
	 */
	protected $end_time;

	/**
	 * Конструктор.
	 *
	 * @param boolean $autoStart
	 */
	public function __construct($autostart = true) {
		if ($autostart) {
			$this->start();
		}
	}

	/**
	 * Запускает таймер.
	 *
	 */
	public function start() {
		$this->start_time = microtime(true);
		$this->end_time = null;
	}

	/**
	 * Останавливает таймер.
	 *
	 */
	public function stop() {
		$this->end_time = microtime(true);
	}

	/**
	 * Возвращет длину промежутка времени между стартом и текущей временной
	 * меткой или остановкой в зависимости от того, запущен в данный момент
	 * таймер или нет соответственно.
	 *
	 * @return float
	 */
	public function getTime() {
		return (null === $this->end_time
			? microtime(true) : $this->end_time) - $this->start_time;
	}
}
