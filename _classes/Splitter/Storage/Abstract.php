<?php

/**
 * Базовый класс объектов, сохраняющий скачанный файл.
 *
 * @version $Id$
 */
abstract class Splitter_Storage_Abstract {

	/**
	 * Опции хранилища.
	 *
	 * @var array
	 */
	protected $options = array();

	/**
	 * Имя файла, в котором будут сохранены данные.
	 *
	 * @var string
	 */
	protected $filename;

	/**
	 * Конструктор.
	 *
	 * @param array $options
	 */
	public function __construct(array $options = array()) {
		foreach ($options as $name => $value) {
			$this->setOption($name, $value);
		}
	}

	/**
	 * Устанавливает значение указанной опции.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return Splitter_Storage_Abstract
	 * @throws Splitter_Storage_Exception
	 */
	protected function setOption($name, $value) {
		if (!array_key_exists($name, $this->options)) {
			$this->onSetOptionFailed($name, $value);
		}
		$method = 'set' . ucfirst($name);
		if (method_exists($this, $method)) {
			$this->$method($value);
		} else {
			$this->options[$name] = $value;
		}
		return $this;
	}

	/**
	 * Обрабатывает установку неподдерживаемой опции.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @throws Splitter_Storage_Exception
	 */
	protected function onSetOptionFailed($name, $value) {
		throw new Splitter_Storage_Exception('Unsupported option ' . $name . ' => ' . var_export($value, true));
	}

	/**
	 * Фабрика хранилищ. Создает хранилище с указанными параметрами.
	 *
	 * @param string $type
	 * @return Splitter_Storage_Abstract
	 */
	public static function factory($type, array $options = array()) {
		$class = 'Splitter_Storage_' . ucfirst($type);
		if (!class_exists($class)) {
			throw new Splitter_Storage_Exception('Wrong storage type "' . $type . '" specified');
		}
		return new $class($options);
	}

	/**
	 * Возвращает имя файла, в котором будут сохранены данные.
	 *
	 * @return string
	 */
	public function getFileName() {
		return $this->filename;
	}

	/**
	 * Устанавливает имя файла, в котором будут сохранены данные.
	 *
	 * @param string $filename
	 * @return Splitter_Storage_Abstract
	 */
	public function setFileName($filename) {
		// здесь нужно убедиться, что установлена верная локаль
		if ($filename != basename($filename)) {
			throw new Splitter_Storage_Exception(sprintf('No path allowed in filename, "%s" is given', $filename));
		}
		$this->filename = $filename;
		return $this;
	}

	/**
	 * Возвращает позицию, с которой нужно возобновить скачивание файла.
	 *
	 * @return integer
	 */
	abstract public function getResumePosition();

	/**
	 * Обрезает сохраняемый файл до указанной длины. Используется в случае, если
	 * сервер не поддерживает докачку.
	 *
	 * @param integer $size
	 * @throws Splitter_Storage_Exception
	 */
	abstract public function truncate($size);

	/**
	 * Пишет данные в хранилище.
	 *
	 * @param string $data
	 * @return Splitter_Storage_Abstract
	 * @throws Splitter_Storage_Exception
	 */
	abstract public function write($data);

	/**
	 * Фиксирует данные в хранилище.
	 *
	 * @throws Splitter_Storage_Exception
	 */
	abstract public function commit();
}
