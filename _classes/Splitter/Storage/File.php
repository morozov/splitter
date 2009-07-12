<?php

/**
 * Класс объектов, сохраняющий скачанный файл в файловой системе.
 *
 * @version $Id$
 */
class Splitter_Storage_File extends Splitter_Storage_Abstract {

	/**
	 * Опции хранилища.
	 *
	 * @var array
	 */
	protected $options = array('dir' => null);

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
	 * @param array $options
	 */
	public function __construct(array $options = array()) {
		if (!isset($options['dir'])) {
			throw new Splitter_Storage_Exception('Directory option is required to be set');
		}
		parent::__construct($options);
	}

	/**
	 * Устанавливает директорию сохранения файла.
	 *
	 * @param string $dir
	 */
	protected function setDir($dir) {
		$this->implementation = 0 === strpos($dir, 'ftp://')
			? new Splitter_Storage_File_Ftp
			: new Splitter_Storage_File_Local;
		$this->options['dir'] = $this->implementation->transformPath($dir);
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
	 * Возвращает позицию, с которой нужно докачивать файл.
	 *
	 * @return integer
	 */
	public function getResumePosition() {
		if (!$this->isFilenameSet()) {
			return 0;
		}
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
		return $this->options['dir'] . $this->filename;
	}

	/**
	 * Возвращает, установлено ли имя файла для сохранения.
	 *
	 * @return boolean
	 */
	protected function isFilenameSet() {
		return strlen($this->filename) > 0;
	}

	/**
	 * Возвращает ресурс файла для записи.
	 *
	 * @return resource
	 * @throws Splitter_Storage_Exception
	 */
	protected function getResource() {
		if (!is_resource($this->resource)) {
			if (!$this->isFilenameSet()) {
				throw new Splitter_Storage_Exception('Couldn’t create storage resource: filename id not set');
			}
			if (!$this->isFilenameSet()) {
				throw new Splitter_Storage_Exception('Couldn’t create storage resource: filename id not set');
			}
			$this->resource = $this->implementation->open($this->getSavePath());
		}
		return $this->resource;
	}
}
