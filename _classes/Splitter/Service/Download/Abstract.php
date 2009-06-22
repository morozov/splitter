<?php

// размер буфера чтения из сокета
define('SOCKET_READ_BUFFER', 8192);

/**
 * @package	 Splitter
 * @subpackage  service.download
 * @version	 $Id$
 */
/**
 * Базовый класс реализаций сервиса скачивания файлов.
 *
 * @package	 Splitter
 * @subpackage  service.download
 * @see		 Splitter_Service_Abstract
 * @abstract
 */
abstract class Splitter_Service_Download_Abstract extends Splitter_Service_Abstract
{
	/**
	 * Интервал времени, через который клиенту отдаются сообщения о прогрессе
	 * при скачивании файла (в секундах).
	 *
	 * @var	 integer
	 */
	var $PROGRESS_TRACE_INTERVAL = 3;

	/**
	 * Объект соединения с сервером.
	 *
	 * @var	 Splitter_Connection_Abstract
	 */
	var $_conn;

	/**
	 * Объект сохраняющий скачанные данные.
	 *
	 * @var	 Splitter_Storage_Abstract
	 */
	var $_storage;

	/**
	 * Конструктор.
	 *
	 * @return  Splitter_Service_Download_Abstract
	 */
	function Splitter_Service_Download_Abstract()
	{
		$class = $this->_getConnectionClassName();
		// создаем объект соединения с сервером
		$this->_conn = new $class;
	}

	/**
	 * Запускает скачивание файла.
	 *
	 * @param   array   $params   Параметры запуска
	 * @param   array   $reset	Указывает, нужно ли сбрасывать значения
	 *							параметров с предыдущего запуска (используется
	 *							при внутреннем перезапуске сервиса)
	 * @return  ArrayObject
	 */
	function run($params, $reset = true)
	{
		$result =& parent::run($params, $reset);

		// по умолчанию статус закачки - ERROR. Успешный статус будет выставлен
		// только после успешного сохранения файла
		$result->offsetSet('status', DOWNLOAD_STATUS_ERROR);

		// определяем имя, под которым будет сохранен файл
		$this->_setFileName();

		return $result;
	}

	/**
	 * Возвращает имя, под которым будет сохранен скачиваемый файл.
	 *
	 * @return  string
	 */
	function _getFileName()
	{
		// такой вот странный, бестелесный свич :)
		switch (true)
		{
			// если имя файла указано явно, то всё ясно
			case strlen($fileName = $this->_getParam('filename')) > 0:
				break;

			// если удается определить из урла - тоже хорошо
			case strlen($fileName = $this->_getFileNameFromUrl()) > 0:
				break;

			// иначе, устанавливаем в NULL — производные классы должны разрешать
			// эту ситуацию
			default:
				$fileName = null;
				break;
		}

		return $fileName;
	}

	/**
	 * Устанавливает имя, под которым будет сохранен скачиваемый файл.
	 *
	 */
	function _setFileName()
	{
		$fileName = $this->_getFileName();

		if ($this->_isDownloadNeeded())
		{
			$storage =& $this->_getStorage();

			if (isset($GLOBALS['rename'])) {
				$fileName = $GLOBALS['rename']->rename($fileName);
			}

			$storage->setFileName($fileName);
		}

		$this->fireEvent('onFileNameChange', $fileName);
	}

	/**
	 * Пишет данные из ответа серера в хранилище.
	 *
	 * @param   ArrayObject $result
	 * @param   integer $position
	 * @param   integer $size
	 */
	function _store(&$result, $position, $size)
	{
		// получаем ссылку на хранилище
		$storage =& $this->_getStorage();

		// если позиция возобновления закачки сервера не соответствует размеру
		// скачанного файла, обрезаем файл до соответствующего размера
		if ($position != $storage->getResumePosition())
		{
			trigger_error('Cервер не поддерживает докачку', E_USER_NOTICE);

			if (!$storage->truncate($position))
			{
				trigger_error('…а хранилище не умеет обрезать файлы', E_USER_ERROR);
			}
		}

		// получаем ссылку на сокет с данными
		$socket =& $this->_conn->getDataSocket();

		// устанавливаем временную метку в значение начала чтения данных
		$timer = new System_Timer();

		// пока не закончился файл ответа сервера
		while (!$socket->eof())
		{
			// читаем данные из сокета
			$data = $socket->read(SOCKET_READ_BUFFER);

			// пытаемся записать данные в хранилище
			if ($storage->write($data))
			{
				// если получилось, определяем текущий размер скачанных данных
				$position += strlen($data);
			}
			// иначе прерываем скачивание
			else
			{
				$result->offsetSet('status', DOWNLOAD_STATUS_FATAL);

				break;
			}

			// если пора выводить сообщение о прогрессе
			if ($timer->getTime() >= $this->PROGRESS_TRACE_INTERVAL)
			{
				// уведомляем обозревателей об изменении прогрессса
				$this->fireEvent('onProgressChange', $position);

				// сбрасываем временную метку
				$timer->start();
			}
		}

		// выводим сообщение о прогрессе в конце скачивания
		$this->fireEvent('onProgressChange', $position);

		// в случае, если успешно дошли до конца данных
		if (DOWNLOAD_STATUS_FATAL != $result->offsetGet('status'))
		{
			// посылаем в хранилище сообщение об окончании передачи данных
			$storage->close();

			// если известен размер скачиваемого  файла, и размер сохраненного
			// файла меньше, чем оригинала
			if (!is_null($size) && $position < $size)
			{
				$result->offsetSet('status', DOWNLOAD_STATUS_INCOMPLETE);

				trigger_error('Сервер неожиданно закрыл соединение');
			}
			else
			{
				$result->offsetSet('status', DOWNLOAD_STATUS_OK);
			}
		}
	}

	/**
	 * Возвращает имя скачиваемого файла, определенное из URL, декодирует.
	 *
	 * @return  string
	 */
	function _getFileNameFromUrl()
	{
		// получаем объект URL из параметров запуска
		$url =& $this->_getUrl();

		return rawurldecode($url->getFileName());
	}

	/**
	 * Возвращает URL скачиваемого файла в виде объекта.
	 *
	 * @return  Url
	 */
	function _getUrl()
	{
		// получаем объект URL из параметров запуска
		$url =& $this->_getParam('url');

		return $url;
	}

	/**
	 * Возвращает объект Storage.
	 *
	 * @return  splitter_storage_Abstract
	 */
	function _getStorage()
	{
		$storage =& $this->_getParam('storage');

		return $storage;
	}

	/**
	 * Возвращает нужно ли сохранять данные файла в хранилище.
	 *
	 * @return  boolean
	 */
	function _isDownloadNeeded()
	{
		return is_object($storage =& $this->_getStorage())
			&& (!method_exists($storage, 'isDownloadNeeded') || $storage->isDownloadNeeded());
	}

	/**
	 * Возвращает наименование класса используемого соединения.
	 *
	 * @return  string
	 */
	abstract function _getConnectionClassName();
}
