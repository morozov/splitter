<?php

/**
 * Класс объектов, сохраняющий скачанный файл в файловой системе.
 *
 * @version $Id$
 */
class Splitter_Storage_File extends Splitter_Storage_Abstract {

	/**
	 * Директория для сохранения файла.
	 *
	 * @var string
	 */
	protected $dir = '.';

	/**
	 * Реализация сохранения.
	 *
	 * @var Splitter_Storage_File_Abstract
	 */
	protected $implementation;

	/**
	 * Ресурс записи в файл файловой системы.
	 *
	 * @var resource
	 */
	protected $resource;

	/**
	 * Конструктор.
	 *
	 * @param string $dir
	 */
	public function __construct($dir) {
		$this->implementation = 0 === strpos($dir, 'ftp://')
			? new Splitter_Storage_File_Ftp
			: new Splitter_Storage_File_Local;
		$this->dir = $this->implementation->transformPath($dir);
	}

	/**
	 * Фиксирует данные в хранилище.
	 *
	 * @throws Splitter_Storage_Exception
	 */
	public function commit() {
		if (is_resource($this->resource)) {
			fclose($this->resource);
		}
	}

	/**
	 * Устанавливает имя файла, в котором будут сохранены данные.
	 *
	 * @return Splitter_Storage_Abstract
	 */
	public function setFileName($fileName) {
		if (is_resource($this->resource)) {
			throw new Splitter_Storage_Exception('Unable to change filename. Data has been already written');
		}
		if (0 === strpos($fileName, '.ht')) {
			throw new Splitter_Storage_Exception(sprintf('Given filename "%s" is not acceptable', $fileName));
		}
		return parent::setFileName($fileName);
	}

	/**
	 * Возвращает позицию, с которой нужно докачивать файл.
	 *
	 * @return integer
	 */
	public function getResumePosition() {
		clearstatcache();
		$path = $this->getSavePath();
		return file_exists($path) ? filesize($path) : 0;
	}

	/**
	 * Пишет данные в файл.
	 *
	 * @param string $data
	 * @return Splitter_Storage_Abstract
	 * @throws Splitter_Storage_Exception
	 */
	public function write($data) {
		if (strlen($data) != fwrite($this->getResource(), $data)) {
			throw new Splitter_Storage_Exception('Unable to write to storage');
		}
		return $this;
	}

	/**
	 * Обрезает файл до указанной длины. Используется, если сервер не
	 * поддерживает докачку.
	 *
	 * @param integer $size
	 * @throws Splitter_Storage_Exception
	 */
	public function truncate($size) {
		if (ftruncate($this->getResource(), $size)) {
			throw new Splitter_Storage_Exception('Unable to truncate storage');
		}
	}

	/**
	 * Возвращает полный путь файла, в который будут сохраняться данные.
	 *
	 * @return string
	 */
	protected function getSavePath() {
		return $this->dir . $this->filename;
	}

	protected function getResource() {
		if (!is_resource($this->resource)) {
			$this->resource = $this->implementation->open($this->getSavePath());
		}
		return $this->resource;
	}
}
