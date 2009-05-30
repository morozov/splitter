<?php

/**
 * @package 	Splitter
 * @subpackage	storage
 * @version 	$Id$
 */
/**
 * �?нтерфейс к хранилищам. Попутно реализует разбиение файлов на куски.
 *
 * @access		public
 * @package 	Splitter
 * @subpackage	storage
 * @see 		abstract_Object
 */
class Splitter_Storage_Intf extends Splitter_Storage_Abstract {

	/**
	 * Номер текущий части, на которые разбивается файл.
	 *
	 * @access	private
	 * @var 	integer
	 */
	var $_part = 0;

	/**
	 * Размер данных записанных в текущую часть.
	 *
	 * @access	private
	 * @var 	integer
	 */
	var $_written = 0;

	/**
	 * Тип реализации для сохранения данных.
	 *
	 * @access	private
	 * @var 	string
	 */
	var $_type;

	/**
	 * Размер частей, на которые нужно разбивать данные.
	 *
	 * @access	private
	 * @var 	integer
	 */
	var $_splitSize;

	/**
	 * Объект хранилища для текущей части.
	 *
	 * @access	private
	 * @var		Splitter_Storage_Abstract
	 */
	var $_storage;

	/**
	 * Контекст хэша для вычисления контрольной суммы файла.
	 *
	 * @access	private
	 * @var		resource
	 */
	var $hash;

	/**
	 * Конструктор.
	 *
	 * @access	public
	 * @param	string	$type
	 * @param	string	$target
	 * @param	integer $splitSize
	 */
	function Splitter_Storage_Intf($type, $target, $splitSize) {
		parent::Splitter_Storage_Abstract($target);
		$this->_type = $type;
		$this->_splitSize = $splitSize;
	}

	/**
	 * Возвращает позицию, с которой нужно возобновить скачивание файла.
	 * Реализуется в производных классах.
	 *
	 * @access	public
	 * @return	integer
	 */
	function getResumePosition() {

		// если не указано, под каким именем сохранять файл, о докачке говорить
		// еще рано
		if (is_null($this->_fileName)) {
			return 0;
		}

		$storage =& $this->_createStorage();

		$part = 0;

		do {
			$next = false;

			$storage->setFileName($this->_getPartFileName(++$part));

			$position = $storage->getResumePosition();

			$diff = $position - $this->_splitSize;

			switch (true) {

				// если текущая часть недокачана
				case $diff < 0:
					break;

				// если размер текущей части превышает размер разбиения,
				// перекачаем его снова
				case $diff > 0:
					$position = 0;
					break;

				// если текущая часть полностью скачана
				default:
					$next = true;
					break;
			}
		} while ($next);

		return ($part - 1) * $this->_splitSize + $position;
	}

	/**
	 * Открывает хранилище.
	 *
	 * @access	public
	 * @param	integer $size
	 * @return	boolean
	 */
	function open($size) {

		$this->hash = hash_init('crc32b');

		$resume = $this->getResumePosition();

		$this->_part = floor($resume / $this->_splitSize);

		$written = $resume - $this->_part * $this->_splitSize;

		return parent::open($size) && $this->_next($written);
	}

	/**
	 * Пишет данные в хранилище.
	 *
	 * @access	public
	 * @param	string $data
	 * @return	boolean
	 */
	function write($data) {

		hash_update($this->hash, $data);

		do {
			// сколько места осталось в текущей части
			$space = $this->_splitSize - $this->_written;

			// если в текущей части закончилось место, пытаемся открыть следующую
			if ($space <= 0 && !$this->_next())
			{
				return false;
			}

			// этот кусок данных нужно записать в текущую часть
			$portion = substr($data, 0, $space);

			// пытаемся записать в нее кусок данных
			if (!$this->_storage->write($portion))
			{
				return false;
			}

			// собираем суммарный размер данных, записанных в текущую часть
			$this->_written += strlen($portion);

			// а этот кусок данных нужно дописать в следующую часть
			$data = substr($data, $space);
		} while (strlen($data) > 0);

		return true;
	}

	/**
	 * Завершает сохранение данных. Закрывает хранилище для текущей части.
	 *
	 * @access	protected
	 * @return	boolean
	 */
	function close() {
		$crc = hash_final($this->hash);
		$crc = sprintf('%08x', 0x100000000 + hexdec($crc));
		Application::getResponse()->debug(substr($crc, 6, 2) . substr($crc, 4, 2) . substr($crc, 2, 2) . substr($crc, 0, 2));
		return parent::close();
	}

	/**
	 * Завершает сохранение данных. Закрывает хранилище для текущей части.
	 *
	 * @access	protected
	 * @return	boolean
	 */
	function _close() {

		$result = $this->_storage->close();

		// разрушаем ссылку на реализацию хранилища, иначе при скачивании
		// следующего файла в self::_next() оно будет закрыто еще раз.
		// :TODO: morozov 07032007: подумать, может это можно решить
		// каким-нибудь более красивым способом, к примеру, почтовое хранилище
		// не должно отправлять сообщение с одним и тем же фалом дважды
		$this->_storage = null;

		return $result;
	}

	/**
	 * Создает реализацию хранилища.
	 *
	 * @access	protected
	 * @param	string
	 * @return	splitter_storage_Abstract
	 */
	function _createStorage() {
		$storage =& Splitter_Storage_Abstract::factory($this->_type, $this->_target);
		return $storage;
	}

	/**
	 * Создает реализацию для сохранения следующей части файла.
	 *
	 * @access	private
	 * @param	integer $position
	 * @return	boolean
	 */
	function _next($position = 0) {

		// пытаемся закрыть предыдущую часть
		if (is_object($this->_storage) && !$this->_close())
		{
			return false;
		}

		// следующая часть
		++$this->_part;

		$this->_storage =& $this->_createStorage();

		$this->_storage->setFileName($this->_getPartFileName($this->_part));

		$this->_written = $position;

		// предполагаемый размер следующей части
		$size = !is_null($this->_size)

			// если это последняя часть
			? ($this->_part == ($parts = $this->_splitSize ? ceil($this->_size / $this->_splitSize) : 1)

				// определяем размер последней части
				? $this->_size - ($parts - 1) * $this->_splitSize

				// иначе размер должен быть равен размеру разбиения
				: $this->_splitSize)

			: null;

		return $this->_storage->open($size)
			&& $this->_storage->truncate($position);
	}

	/**
	 * Возвращает имя файла для текущей части.
	 *
	 * @access	private
	 * @return	string
	 */
	function _getPartFileName($part) {

		// приделываем номер части в том случае, если включено разбивание на
		// части, и при этом либо размер файла больше размера части (т.е. частей
		// будет больше одной), либо размер файла неизвестен
		//
		// :NOTE: morozov 16092007: временно отключено по причине принципиального
		// конфликта c докачкой как таковой
		$fileName = $this->_fileName;

		if (true/* && (is_null($this->_size) || $this->_size > $this->_splitSize)*/) {
			// постфикс в стиле Total Commander
			$fileName .= '.' . sprintf('%03d', $part);
		}

		return $fileName;
	}
}
