<?php

/**
 * Интерфейс хранилища.
 *
 */
interface Splitter_Storage_Interface {

	/**
	 * Возвращает имя сохраняемого файла.
	 *
	 * @return string
	 */
	public function getFileName();

	/**
	 * Устанавливает имя сохраняемого файла.
	 *
	 * @param string $filename
	 */
	public function setFileName($filename);

	/**
	 * Возвращает позицию, с которой нужно возобновить скачивание файла.
	 *
	 * @return integer
	 */
	public function getResumePosition();

	/**
	 * Обрезает сохраняемый файл до указанной длины. Используется в случае, если
	 * сервер не поддерживает докачку.
	 *
	 * @param integer $size
	 * @throws Splitter_Storage_Exception
	 */
	public function truncate($size);

	/**
	 * Пишет данные в хранилище.
	 *
	 * @param string $data
	 * @throws Splitter_Storage_Exception
	 */
	public function write($data);
}
