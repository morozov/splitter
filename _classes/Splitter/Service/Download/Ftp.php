<?php

/**
 * @package	 Splitter
 * @subpackage  service.download
 * @version	 $Id$
 */
/**
 * Сервис скачивания файла по протоколу FTP.
 *
 * @package	 Splitter
 * @subpackage  service.download
 * @see		 Splitter_Service_Download_Abstract
 */
class Splitter_Service_Download_Ftp extends Splitter_Service_Download_Abstract
{
	/**
	 * Запускает скачивание файла. Принимает параметры:
	 *  - url	  - URL скачиваемого файла (объект)
	 *  - fileName - имя, под которым файл должен быть сохранен
	 *  - storage  - Хранилище для скачанных данных
	 *
	 * @param   array   $params   Параметры запуска
	 * @param   array   $reset	Указывает, нужно ли сбрасывать значения
	 *							параметров с предыдущего запуска (используется
	 *							при внутреннем перезапуске сервиса)
	 * @return  Lib_ArrayObject
	 * @see	 Splitter_Service_Download_Abstract::run
	 */
	function run($params, $reset = true)
	{
		$result =& parent::run($params, $reset);

		// получаем объект URL из параметров запуска
		$url =& $this->_getUrl();

		// открываем соединение с сервером
		if ($this->_conn->connect($url->toString()))
		{
			// залогиниваемся
			if ($this->_conn->login($url->getUserName(), $url->getPassword()))
			{
				// определяем систему, на которой запущен сервер
				$this->_conn->system();

				// определяем директорию, в которой находится скачиваемый файл
				$path = $url->getPath();

				// пытаемся выставить текущую директорию
				if ($this->_conn->chdir(str_replace(DIRECTORY_SEPARATOR, '/', dirname($path))))
				{
					// определяем имя файла относительно текущей директории
					$fileName = rawurldecode(basename($path));
				}
				else
				{
					// определяем имя файла относительно корня
					$fileName = rawurldecode($path);
				}

				// пытаемся определить размер файла
				$size = $this->_conn->filesize($fileName);
				$this->fireEvent('onFileSizeChange', $size);

				// сохраняем размер в результате работы сервиса
				$result->offsetSet('size', $size);

				// если нужно скачивать файл
				if ($this->_isDownloadNeeded())
				{
					$storage =& $this->_getStorage();

					// отправляем запрос на получение данных
					if (false !== ($start = $this->_conn->retrieve($fileName, $storage->getResumePosition()))

						// открываем хранилище
						&& $storage->open($size))
					{
						// сохраняем скачанные данные
						$this->_store($result, $start, $size);
					}
				}
				else
				{
					$result->offsetSet('status', DOWNLOAD_STATUS_OK);
				}

				// закрываем соединение
				$this->_conn->quit();
			}
		}

		return $result;
	}

	/**
	 * Возвращает наименование класса используемого соединения.
	 *
	 * @return  string
	 */
	function _getConnectionClassName()
	{
		return 'Splitter_Connection_Ftp';
	}
}
