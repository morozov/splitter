<?php

foreach (array(
	'FTP_AUTOASCII' => -1,
	'FTP_BINARY' => 1,
	'FTP_ASCII' => 0,
	'FTP_FORCE' => true,
	) as $const => $value)
{
	if (!defined($const)) define($const, $value);
}

/**
 * @package	 Splitter
 * @subpackage  connection
 * @version	 $Id$
 */
/**
 * Класс соединеня с FTP-сервером.
 *
 * @access	  public
 * @package	 Splitter
 * @subpackage  connection
 * @see		 Splitter_Connection_Abstract
 */
class Splitter_Connection_Ftp extends Splitter_Connection_Abstract
{
	/**
	 * Порт соединения по умолчанию.
	 *
	 * @access  protected
	 * @var	 integer
	 */
	var $DEFAULT_PORT = 21;

	/**
	 * Регулярное выражение для разбора ответа на код и текстовую часть.
	 *
	 * @access  private
	 * @var	 string
	 */
	var $REGEXP_RESPONSE = '/^(\d{3})((-(.*\r\n)+\\1)? [^\r\n]+\r\n)/';

	/**
	 * Регулярное выражение для разбора ответа сервера при установке пассивного
	 * соединения.
	 *
	 * @access  private
	 * @var	 string
	 */
	var $REGEXP_PASV = '^.+ \\(?([0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]+,[0-9]+)\\)?.*';

	/**
	 * �?мя пользователя по умолчанию.
	 *
	 * @access  private
	 * @var	 string
	 */
	var $DEFAULT_USERNAME = 'anonymous';

	/**
	 * Пароль по умолчанию.
	 *
	 * @access  private
	 * @var	 string
	 */
	var $DEFAULT_PASSWORD = 'splitter@splitter.com';

	/**
	 * Допусимые режимы передачи данных.
	 *
	 * @access  private
	 * @var	 array
	 */
	var $TRANSFER_MODES = array(FTP_AUTOASCII, FTP_ASCII, FTP_BINARY);

	/**
	 * Расширения файлов, для которых по умолчанию используется текстовый режим
	 * передачи данных.
	 *
	 * @access  private
	 * @var	 array
	 */
	var $AUTO_ASCII_EXTENSIONS = array
	(
		'asp', 'bat', 'c', 'cpp', 'css', 'csv', 'h', 'htm', 'html', 'ini', 'js',
		'log', 'php', 'php3', 'pl', 'perl', 'sh', 'shtml', 'sql', 'txt'
	);

	var $_canRestore = false;

	/**
	 * Определяет, должен ли использоваться пассивный режим передачи данных.
	 *
	 * @access  private
	 * @var	 boolean
	 */
	var $_passive = true;

	/**
	 * Тип передачи данных (текстовый, двоичный или авто).
	 *
	 * @access  private
	 * @var	 integer
	 */
	var $_type = FTP_AUTOASCII;

	/**
	 * Открывает соединение с сервером.
	 *
	 * @access  public
	 * @param   string   $url
	 * @return  boolean
	 */
	function connect($url)
	{
		return $this->_connect($url);
	}

	/**
	 * Выполняет авторизацию на сервере.
	 *
	 * @access  public
	 * @param   string   $userName
	 * @param   string   $password
	 * @return  boolean
	 */
	function login($userName, $password)
	{
		if (!$this->_exec('USER ' . (strlen($userName) > 0
			? $userName: $this->DEFAULT_USERNAME)))
		{
			return false;
		}

		if (!$this->_checkStatus()) return false;

		$code = $this->getStatus();

		// если еще не пустили (230 - User logged in, proceed.)
		if (230 != $code)
		{
			if (!$this->_exec(((331 == $code) ? 'PASS ':'ACCT ')
				. (strlen($password) > 0 ? $password: $this->DEFAULT_PASSWORD)))
			{
				return false;
			}

			if (!$this->_checkStatus())
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Отправляет запрос восстановления передачи данных.
	 *
	 * @access  public
	 * @param   integer  $pos
	 * @return  boolean
	 */
	function restore($pos)
	{
		return $this->_exec('REST ' . $pos) && $this->_checkStatus();
	}

	/**
	 * Отправляет запрос закрытия соединения.
	 *
	 * @access  public
	 * @return  boolean
	 */
	function quit()
	{
		return $this->_exec('QUIT') && $this->_checkStatus() && $this->abort();
	}

	/**
	 * Отправляет запрос смены активной директории.
	 *
	 * @access  public
	 * @param   string   $dir
	 * @return  boolean
	 */
	function chdir($dir)
	{
		return $this->_exec('CWD ' . $dir) && $this->_checkStatus();
	}

	/**
	 * Отправляет запрос удаления директории.
	 *
	 * @access  public
	 * @param   string   $dir
	 * @return  boolean
	 */
	function rmdir($dir)
	{
		return $this->_exec('RMD ' . $dir) && $this->_checkStatus();
	}

	/**
	 * Отправляет запрос создания директории.
	 *
	 * @access  public
	 * @param   string   $dir
	 * @return  boolean
	 */
	function mkdir($dir)
	{
		return $this->_exec('MKD ' . $dir) && $this->_checkStatus();
	}

	/**
	 * Отправляет запрос на получение размера файла и возвращает результат.
	 *
	 * @access  public
	 * @param   string   $file
	 * @return  integer
	 */
	function filesize($file)
	{
		return ($this->_exec('SIZE ' . $file) && $this->_checkStatus())
			? (int)$this->getStatusText() : false;
	}

	/**
	 * Отправляет запрос на получение типа операционной системы сервера файла
	 * и возвращает результат.
	 *
	 * @access  public
	 * @return  string
	 */
	function system()
	{
		// выполняем запрос и проверяем статус ответа
		return ($this->_exec('SYST') && $this->_checkStatus())
			// достаем из ответа первое слово - оно и есть тип системы
			? ereg_replace('^([a-zA-Z]+) .*', "\\1", $this->getStatusText())
		   : false;
	}

	/**
	 * Устанавливает тип передачи данных и выполняет соответствующий запрос.
	 *
	 * @access  public
	 * @param   integer  $mode
	 * @return  boolean
	 */
	function setType($mode = FTP_AUTOASCII)
	{
		// проверяем корректность переданного аргумента
		if (in_array($mode, $this->TRANSFER_MODES))
		{
			// устанавливаем переменную объекта
			$this->_type = $mode;

			// открываем сокет для получения данных в указанном режиме
			$this->_openDataSocket($mode);

			return true;
		}

		return false;
	}

	/**
	 * Выполняет запрос на получение файла.
	 *
	 * @access  public
	 * @param   string  $fileName
	 * @return  mixed
	 */
	function retrieve($fileName, $pos = 0)
	{
		$mode = ($this->_type == FTP_ASCII
			|| ($this->_type == FTP_AUTOASCII
				&& in_array(strtolower(pathinfo($fileName, PATHINFO_EXTENSION)), $this->AUTO_ASCII_EXTENSIONS)
			)) ? FTP_ASCII : FTP_BINARY;

		if (!$this->_openDataSocket($mode))
		{
			return false;
		}

		// определяем, поддерживает ли сервер докачку
		if (!$this->restore($pos))
		{
			$pos = 0;
		}

		if (!$this->_exec('RETR ' . $fileName)
			|| !$this->_checkStatus())
		{
			$this->_closeDataSocket();
			return false;
		}

		return $pos;
	}

	/**
	 * Пытается прочитать приветствие сервера.
	 *
	 * @access  protected
	 * @return  boolean
	 */
	function _onAfterConnect()
	{
		return $this->_readmsg() && parent::_onAfterConnect();
	}

	/**
	 * Выполняет запрос на выполнение указанной команды.
	 *
	 * @access  private
	 * @param   string  $fileName
	 * @return  boolean
	 */
	function _exec($cmd)
	{
		// отправляем сообщение в лог
		$this->_trace($cmd, 'request');

		// пишем данные в управляющий сокет
		return $this->_writeln($cmd) && $this->_readmsg();
	}

	/**
	 * Производит разбор ответа сервера на код и текстовое сообщение.
	 *
	 * @access  private
	 * @return  boolean
	 */
	function _readmsg()
	{
		$message = '';

		// пока не закончились данные в сокете
		while (!$this->_controlSocket->eof())
		{
			// построчно читаем данные из ответа
			$message .= $this->_controlSocket->gets();

			// определяем, не закончилось ли сообщение
			if (preg_match($this->REGEXP_RESPONSE, $message, $matches))
			{
				// определяем код ответа
				$this->_status = (int)$matches[1];

				// определяем текстовое сообщение (обрезаем слева пробел
				// и справа перевод строки)
				$this->_statusText = rtrim(ltrim($matches[2], ' '), PHP_EOL);

				// выдаем сообщение в лог
				$this->_trace(rtrim($message, PHP_EOL), 'response');

				return true;
			}
		}

		return false;
	}

	/**
	 * Проверяет статус ответа сервера.
	 *
	 * @access  private
	 * @return  boolean
	 */
	function _checkStatus()
	{
		$code = $this->getStatus();

		return $code < 400 && $code > 0;
	}

	/**
	 * Возвращает сообщение о типе передачи данных для отправки в запрос.
	 *
	 * @access  private
	 * @param   integer  $mode
	 * @return  string
	 */
	function _getTypeMessage($mode)
	{
		switch ($mode)
		{
			case FTP_BINARY:
				$msg = 'TYPE I';
				break;

			case FTP_ASCII:
			case FTP_AUTOASCII:
				$msg = 'TYPE A';
				break;

			default:
				//:TODO: morozov 19022006: сделать обработку ошибок.
				break;
		}

		return  $msg;
	}

	/**
	 * Создает сокет для передачи данных с указанным режимом.
	 *
	 * @access  private
	 * @param   integer  $mode
	 * @return  boolean
	 */
	function _openDataSocket($mode)
	{
		// отправляем сообщение о типе передачи
		if(!$this->_exec($this->_getTypeMessage($mode))) return false;

		if($this->_passive)
		{
			if(!$this->_exec('PASV', 'pasv')) {
				return false;
			}

			if(!$this->_checkStatus()) {
				return false;
			}

			if (false !== ($hostnport = $this->_parsePasvResponse()))
			{
				list($host, $port) = $hostnport;

				$this->_dataSocket =& $this->_createSocket($host, $port);
			}
			else
			{
				trigger_error('Неверный ответ сервера на команду PASV');
				return false;
			}
		}
		else
		{
			$this->SendMSG('Клиент поддерживает только пассивные соединения.');
			return false;
		}
		return TRUE;
	}

	/**
	 * Закрывает сокет передачи данных.
	 *
	 * @access  private
	 * @return  boolean
	 */
	function _closeDataSocket()
	{
		return $this->_dataSocket->close();
	}

	/**
	 * Разбирает ответ сервера при установке пассивного соединения.
	 *
	 * @access  private
	 * @return  array
	 */
	function _parsePasvResponse()
	{
		// получаем IP-адрес и порт из ответа
		$string = ereg_replace($this->REGEXP_PASV, "\\1", $this->getStatusText());

		// если пришли корректные данные
		if (6 == count($list = explode(',', $string)))
		{
			// определяем IP-адрес
			$host = implode('.', array_slice($list, 0, 4));

			// определяем порт
			$port = ((int)$list[4] << 8) + (int)$list[5];

			// заворачиваем всё в массив
			return array($host, $port);
		}

		return false;
	}
}
