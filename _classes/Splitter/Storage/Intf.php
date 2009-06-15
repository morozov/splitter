<?php

/**
 * @package 	Splitter
 * @subpackage	storage
 * @version 	$Id$
 */
/**
 * Интерфейс к хранилищам. Попутно реализует разбиение файлов на куски.
 *
 * @package 	Splitter
 * @subpackage	storage
 * @see 		abstract_Object
 */
final class Splitter_Storage_Intf extends Splitter_Storage_Abstract {

	/**
	 * Номер текущий части, на которые разбивается файл.
	 *
	 * @var 	integer
	 */
	private $part = 0;

	/**
	 * Размер данных записанных в текущую часть.
	 *
	 * @var 	integer
	 */
	private $written_part = 0;

	/**
	 * Общий размер записанных данных.
	 *
	 * @var 	integer
	 */
	private $written_total = 0;

	/**
	 * Тип реализации для сохранения данных.
	 *
	 * @var 	string
	 */
	private $type;

	/**
	 * Размер частей, на которые нужно разбивать данные.
	 *
	 * @var 	integer
	 */
	private $split_size;

	/**
	 * Объект хранилища для текущей части.
	 *
	 * @var		Splitter_Storage_Abstract
	 */
	private $storage;

	/**
	 * Контекст хэша для вычисления контрольной суммы файла.
	 *
	 * @var		resource
	 */
	private $hash;

	/**
	 * Конструктор.
	 *
	 * @param	string	$type
	 * @param	string	$target
	 * @param	integer $splitSize
	 */
	function Splitter_Storage_Intf($type, $target, $splitSize) {
		parent::Splitter_Storage_Abstract($target);
		$this->type = $type;
		$this->split_size = $splitSize;
	}

	/**
	 * Возвращает позицию, с которой нужно возобновить скачивание файла.
	 * Реализуется в производных классах.
	 *
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

			$diff = $position - $this->split_size;

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

		return ($part - 1) * $this->split_size + $position;
	}

	/**
	 * Открывает хранилище.
	 *
	 * @param	integer $size
	 * @return	boolean
	 */
	function open($size) {

		$this->hash = hash_init('crc32b');

		$resume = $this->getResumePosition();

		$this->part = floor($resume / $this->split_size);

		$written = $resume - $this->part * $this->split_size;

		return parent::open($size) && $this->_next($written);
	}

	/**
	 * Пишет данные в хранилище.
	 *
	 * @param	string $data
	 * @return	boolean
	 */
	function write($data) {

		hash_update($this->hash, $data);

		do {
			// сколько места осталось в текущей части
			$space = $this->split_size - $this->written_part;

			// если в текущей части закончилось место, пытаемся открыть следующую
			if ($space <= 0 && !$this->_next())
			{
				return false;
			}

			// этот кусок данных нужно записать в текущую часть
			$portion = substr($data, 0, $space);

			// пытаемся записать в нее кусок данных
			if (!$this->storage->write($portion))
			{
				return false;
			}

			// собираем суммарный размер данных, записанных в текущую часть
			$written = strlen($portion);
			$this->written_part += $written;
			$this->written_total+= $written;

			// а этот кусок данных нужно дописать в следующую часть
			$data = substr($data, $space);
		} while (strlen($data) > 0);

		return true;
	}

	/**
	 * Завершает сохранение данных. Закрывает хранилище для текущей части.
	 *
	 * @return	boolean
	 */
	function close() {
		$closed = parent::close();
		$crc32 = hash_final($this->hash);
		$storage = $this->_createStorage();
		$storage->setFileName($this->_getPartFileName('crc'));
		$storage->open(null);
		$storage->write(
			$this->getCrcFileContents($this->_fileName, $this->written_total, $crc32)
		);
		$storage->close();
		return $closed;
	}

	/**
	 * Завершает сохранение данных. Закрывает хранилище для текущей части.
	 *
	 * @return	boolean
	 */
	function _close() {

		$result = $this->storage->close();

		// разрушаем ссылку на реализацию хранилища, иначе при скачивании
		// следующего файла в self::_next() оно будет закрыто еще раз.
		// :TODO: morozov 07032007: подумать, может это можно решить
		// каким-нибудь более красивым способом, к примеру, почтовое хранилище
		// не должно отправлять сообщение с одним и тем же фалом дважды
		$this->storage = null;

		return $result;
	}

	/**
	 * Возвращает содержимое хранилища.
	 *
	 * @return  string
	 */
	function getContents() {
		throw new Exception('Not applicable');
	}

	/**
	 * Создает реализацию хранилища.
	 *
	 * @param	string
	 * @return	splitter_storage_Abstract
	 */
	function _createStorage() {
		$storage =& Splitter_Storage_Abstract::factory($this->type, $this->_target);
		return $storage;
	}

	/**
	 * Создает реализацию для сохранения следующей части файла.
	 *
	 * @param	integer $position
	 * @return	boolean
	 */
	function _next($position = 0) {

		// пытаемся закрыть предыдущую часть
		if (is_object($this->storage) && !$this->_close())
		{
			return false;
		}

		// следующая часть
		++$this->part;

		$this->storage =& $this->_createStorage();

		$this->storage->setFileName($this->_getPartFileName($this->part));

		$this->written_part = $position;

		// предполагаемый размер следующей части
		$size = !is_null($this->_size)

			// если это последняя часть
			? ($this->part == ($parts = $this->split_size ? ceil($this->_size / $this->split_size) : 1)

				// определяем размер последней части
				? $this->_size - ($parts - 1) * $this->split_size

				// иначе размер должен быть равен размеру разбиения
				: $this->split_size)

			: null;

		return $this->storage->open($size)
			&& $this->storage->truncate($position);
	}

	/**
	 * Возвращает имя файла для текущей части.
	 *
	 * @param mixed $postfix
	 * @return	string
	 */
	function _getPartFileName($postfix) {

		// приделываем номер части в том случае, если включено разбивание на
		// части, и при этом либо размер файла больше размера части (т.е. частей
		// будет больше одной), либо размер файла неизвестен
		//
		// :NOTE: morozov 16092007: временно отключено по причине принципиального
		// конфликта c докачкой как таковой
		$fileName = $this->_fileName;

		if (true/* && (is_null($this->_size) || $this->_size > $this->split_size)*/) {
			// постфикс в стиле Total Commander
			$fileName .= '.' . (is_numeric($postfix) ? sprintf('%03d', $postfix) : $postfix);
		}

		return $fileName;
	}

	/**
	 * Возвращает содержимое файла контрольной суммы.
	 *
	 * @param string $filename
	 * @param integer $size
	 * @param integer $crc32
	 * @return string
	 */
	function getCrcFileContents($filename, $size, $crc32) {
		$format = <<<EOF
filename=%s
size=%d
crc32=%s
EOF;

		// :KLUDGE: morozov 15062009: похоже, это зависит от ОС, но не факт
		if (!Application::isWindows()) {
			$crc32 = sprintf('%08x', 0x100000000 + hexdec($crc32));
			$corrected = '';
			for ($i = strlen($crc32) - 2; $i >= 0; $i -= 2) {
				$corrected .= substr($crc32, $i, 2);
			}
			$crc32 = $corrected;
		}

		$encoding_from = 'utf-8';
		$encoding_to = 'windows-1251';

		if (extension_loaded('iconv')) {
			$filename = iconv($encoding_from, $encoding_to, $filename);
		} elseif (extension_loaded('mbstring')) {
			mb_convert_encoding($filename, $encoding_to, $encoding_from);
		}

		return sprintf($format, $filename, $size, strtoupper($crc32));
	}
}
