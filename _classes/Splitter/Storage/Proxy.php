<?php

/**
 * Хранилище-прокси — выводит сохраняемые данные в браузер.
 *
 * @version $Id$
 */
class Splitter_Storage_Proxy extends Splitter_Storage_Abstract {

	/**
	 * Размер сохраняемых данных.
	 *
	 * @var integer
	 */
	protected $size;

	/**
	 * Флаг, указывающий, были ли отправлены заголовки HTTP при выдаче ответа.
	 *
	 * @var boolean
	 */
	var $_headersSent = false;

	/**
	 * Позиция возобновления закачки
	 *
	 * @var integer
	 */
	var $_resume = 0;

	/**
	 * Флаг, запоминающий, было ли хранилище открыто для записи данных.
	 *
	 * @var boolean
	 */
	var $_opened = false;

	/**
	 * Конструктор. Отключает вывод лога в браузер (временно).
	 *
	 */
	public function __construct() {
		$matches = null;
		$this->_resume = (isset($_SERVER['HTTP_RANGE'])
			&& preg_match('/bytes=(\d*)\-/', $_SERVER['HTTP_RANGE'], $matches))
			? (int)$matches[1] : parent::getResumePosition();
	}

	/**
	 * Устанавливает размер сохраняемых данных.
	 *
	 * @param integer $size
	 */
	public function setSize($size) {
		$this->size = $size;
	}

	/**
	 * Возвращает позицию, с которой нужно докачивать файл.
	 *
	 * @return integer
	 */
	function getResumePosition()
	{
		return $this->_resume;
	}

	/**
	 * Возвращает, нужно ли сохранять данные в хранилище.
	 *
	 * @return boolean
	 */
	function isDownloadNeeded()
	{
		return parent::isDownloadNeeded() && 'HEAD' != $_SERVER['REQUEST_METHOD'];
	}

	/**
	 * Пишет данные в файл.
	 *
	 * @param string $data
	 * @return Splitter_Storage_Abstract
	 * @throws Splitter_Storage_Exception
	 */
	public function write($data) {
		if (!$this->_headersSent) {
			$this->_sendHeaders();
			$this->_headersSent = true;
		}
		echo $data;
		return $this;
	}

	/**
	 * Обрезает файл до указанной длины. Используется, если сервер не
	 * поддерживает докачку.
	 *
	 * @param integer  $size
	 * @return boolean
	 */
	function truncate($size)
	{
		$this->_resume = $size;

		return true;
	}

	/**
	 * Отправляет HTTP-заголовки.
	 *
	 */
	function _sendHeaders()
	{
		if ($this->_opened)
		{
			header($_SERVER['SERVER_PROTOCOL'] . ' '
			. ($this->_resume > 0 ? '206 Partial Content' : '200 OK'));

			// отправляем два заголовка с именем файла, чтобы удовлетворить всех
			// польвательских агентов
			header('Content-Type: text/plain; name="' . $this->filename . '"');
			header('Content-Disposition: inline; filename="' . $this->filename . '"');

			// показываем агенту, что мы поддерживаем докачку
			header('Accept-Ranges: bytes');

			if ($this->_resume > 0)
			{
				header('Content-Range: bytes ' . $this->_resume . '-' . ($this->size - 1) . '/' . $this->size);
			}

			if (!is_null($this->size))
			{
				header('Content-Length: ' . ($this->size - $this->_resume));
			}
		}
		else
		{
			header($_SERVER['SERVER_PROTOCOL'] . ' ' . '404 Not Found');
		}
	}

	/**
	 * Фиксирует данные в хранилище.
	 *
	 * @throws Splitter_Storage_Exception
	 */
	public function commit() { }
}
