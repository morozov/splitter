<?php

/**
 * Интерфейс к хранилищам. Попутно реализует разбиение файлов на куски.
 *
 * @version $Id$
 */
final class Splitter_Storage_Intf extends Splitter_Storage_Abstract {

	/**
	 * Номер текущий части, на которые разбивается файл.
	 *
	 * @var integer
	 */
	private $part = 0;

	/**
	 * Размер данных записанных в текущую часть.
	 *
	 * @var integer
	 */
	private $written_part = 0;

	/**
	 * Общий размер записанных данных.
	 *
	 * @var integer
	 */
	private $written_total = 0;

	/**
	 * Тип реализации для сохранения данных.
	 *
	 * @var string
	 */
	private $type;

	/**
	 * Размер частей, на которые нужно разбивать данные.
	 *
	 * @var integer
	 */
	private $split_size;

	/**
	 * Объект хранилища для текущей части.
	 *
	 * @var Splitter_Storage_Abstract
	 */
	private $storage;

	/**
	 * Контекст хэша для вычисления контрольной суммы файла.
	 *
	 * @var resource
	 */
	private $hash;

	private $constructor_arguments = array();

	/**
	 * Конструктор.
	 *
	 * @param string	$type
	 * @param integer $splitSize
	 */
	function __construct($type, $split_size) {
		$this->type = $type;
		$this->split_size = $split_size;
		$this->constructor_arguments = array_slice(func_get_args(), 2);
		$this->hash = hash_init('crc32b');
		$this->part = floor($this->getResumePosition() / $this->split_size);
	}

	/**
	 * Возвращает позицию, с которой нужно возобновить скачивание файла.
	 * Реализуется в производных классах.
	 *
	 * @return integer
	 */
	function getResumePosition() {

		// если не указано, под каким именем сохранять файл, о докачке говорить
		// еще рано
		if (is_null($this->filename)) {
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
	 * Обрезает сохраняемый файл до указанной длины. Используется в случае, если
	 * сервер не поддерживает докачку.
	 *
	 * @param integer $size
	 * @throws Splitter_Storage_Exception
	 */
	public function truncate($size) {
		throw new Splitter_Storage_Exception('Currently not implemented');
	}

	/**
	 * Пишет данные в хранилище.
	 *
	 * @param string $data
	 * @return Splitter_Storage_Abstract
	 * @throws Splitter_Storage_Exception
	 */
	public function write($data) {

		if (!$this->storage) {
			$this->_next($this->getResumePosition() - $this->part * $this->split_size);
		}

		hash_update($this->hash, $data);

		do {
			// сколько места осталось в текущей части
			$space = $this->split_size - $this->written_part;

			// если в текущей части закончилось место, пытаемся открыть следующую
			if ($space <= 0) {
				$this->_next();
			}

			// этот кусок данных нужно записать в текущую часть
			$portion = substr($data, 0, $space);

			// пытаемся записать в нее кусок данных
			$this->storage->write($portion);

			// собираем суммарный размер данных, записанных в текущую часть
			$written = strlen($portion);
			$this->written_part += $written;
			$this->written_total+= $written;

			// а этот кусок данных нужно дописать в следующую часть
			$data = substr($data, $space);
		} while (strlen($data) > 0);
		return $this;
	}

	/**
	 * Фиксирует данные в хранилище.
	 *
	 * @throws Splitter_Storage_Exception
	 */
	public function commit() {
		$this->storage->commit();
		$this->_createStorage()
			->setFileName($this->_getPartFileName('crc'))
			->write(
				$this->getCrcFileContents(
					$this->filename,
					$this->written_total,
					hash_final($this->hash)
				)
			)->commit();
	}

	/**
	 * Создает реализацию хранилища.
	 *
	 * @param string
	 * @return splitter_storage_Abstract
	 */
	function _createStorage() {
		return call_user_func_array(array('Splitter_Storage_Abstract', 'factory'), array_merge(array($this->type), $this->constructor_arguments));
	}

	/**
	 * Создает реализацию для сохранения следующей части файла.
	 *
	 * @param integer $position
	 * @return boolean
	 */
	function _next($position = 0) {

		// пытаемся закрыть предыдущую часть
		if ($this->storage) {
			$this->storage->commit();
		}

		// следующая часть
		++$this->part;

		$this->storage = $this->_createStorage();

		$this->storage->setFileName($this->_getPartFileName($this->part));

		$this->written_part = $position;
		//$this->storage->truncate($position);
	}

	/**
	 * Возвращает имя файла для текущей части.
	 *
	 * @param mixed $postfix
	 * @return string
	 */
	function _getPartFileName($postfix) {

		// приделываем номер части в том случае, если включено разбивание на
		// части, и при этом либо размер файла больше размера части (т.е. частей
		// будет больше одной), либо размер файла неизвестен
		//
		// :NOTE: morozov 16092007: временно отключено по причине принципиального
		// конфликта c докачкой как таковой
		$fileName = $this->filename;

		if (true/* && (is_null($this->size) || $this->size > $this->split_size)*/) {
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
		// :KLUDGE: morozov 15062009: похоже, это зависит от ОС, но не факт
		if (!Application::isWindows()) {
			$corrected = '';
			for ($i = strlen($crc32) - 2; $i >= 0; $i -= 2) {
				$corrected .= substr($crc32, $i, 2);
			}
			$crc32 = $corrected;
		}
		return sprintf(implode(PHP_EOL, array(
			'filename=%s',
			'size=%d',
			'crc32=%s',
		)), self::utf2win($filename), $size, strtoupper($crc32));
	}
}
