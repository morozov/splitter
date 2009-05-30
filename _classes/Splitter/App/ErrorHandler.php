<?php

/**
 * @package	 Splitter
 * @subpackage  app
 * @version	 $Id$
 */
/**
 * Объект обработчика ошибок.
 *
 * @package	 Splitter
 * @subpackage  app
 * @see		 abstract_Object
 */
class Splitter_App_ErrorHandler
{
	/**
	 * Смещение, начиная с которого нужно выводить отладочные сообщения.
	 *
	 * @var	 int
	 */
	var $BACKTRACE_OFFSET = 2;

	/**
	 * Длина, до которой нужно обрезать значения строковых аргументов.
	 *
	 * @var	 int
	 */
	var $BACKTRACE_MAX_STRLEN = 32;

	/**
	 * Выполняет обработку ошибок.
	 *
	 * @param   integer  $errno
	 * @param   integer  $errstr
	 * @param   string   $errfile
	 * @param   string   $errline
	 */
	function handle($errno, $errstr, $errfile, $errline)
	{
		// если ошибка попадает под текущий уровень оповещения об ошибках
		if ($errno & error_reporting())
		{
			$isUserDefined = $this->_isUserDefined($errno);

			// составляем сообщение об ошибке
			$message = $isUserDefined
				? $errstr
				: $this->_getPrefix($errno) . $errstr . ' in ' . $errfile . ':' . $errline;

			// отправляем сообщение об ошибке в ответ
			$response =& Application::getResponse();
			$response->write($message, 'error');

			if (!$isUserDefined)
			{
				foreach ($this->_getBacktrace() as $message)
				{
					$response->write($message, 'error');
				}
			}

			if ($this->_isReasonable($errno))
			{
				// передаем ошибку в ответ
				$response->error($errstr);
			}

			if ($this->_isFatal($errno))
			{
				exit(0);
			}
		}
	}

	/**
	 * Возвращает текстовый префикс ошибки.
	 *
	 * @param   integer  $errno
	 * @return  string
	 */
	function _getPrefix($errno)
	{
		// определяем тип произошедшей ошибки
		switch ($errno)
		{
			case E_NOTICE:
				$prefix = 'Notice: ';
				break;

			case E_WARNING:
				$prefix = 'Warning: ';
				break;

			case E_ERROR:
				$prefix = 'Fatal error: ';
				break;

			default:
				$prefix = '';
				break;
		}

		return $prefix;
	}

	/**
	 * Определяет, является ли ошибка фатальной.
	 *
	 * @param   integer  $errno
	 * @return  boolean
	 */
	function _isFatal($errno)
	{
		return in_array($errno, array(E_ERROR, E_USER_ERROR));
	}

	/**
	 * Определяет, является ли ошибка причиной невозможности скачивания файла.
	 *
	 * @param   integer  $errno
	 * @return  boolean
	 */
	function _isReasonable($errno)
	{
		return in_array($errno, array(E_WARNING, E_USER_WARNING, E_ERROR, E_USER_ERROR));
	}

	/**
	 * Определяет, является ли ошибка пользовательской.
	 *
	 * @param   integer  $errno
	 * @return  boolean
	 */
	function _isUserDefined($errno)
	{
		return in_array($errno, array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE));
	}

	/**
	 * Возвращает стек вызовов до момента ошибки.
	 *
	 * @return  array
	 */
	function _getBacktrace()
	{
		$backtrace = array_slice(debug_backtrace(), $this->BACKTRACE_OFFSET);

		// смещение, с которого нужно выводить пути относительно корня
		// веб-сервера вместо корня файловой системы
		$offset = strlen(realpath($_SERVER['DOCUMENT_ROOT'])) + 1;

		$messages = array();

		$i = 0;

		foreach ($backtrace as $entry)
		{
			$message = ++$i . ') ';

			if (isset($entry['class']))
			{
				$message .= $entry['class'];
			}

			if (isset($entry['type']))
			{
				$message .= $entry['type'];
			}

			if (isset($entry['function']))
			{
				$message .= $entry['function'];
			}

			$message .= '(';

			if (!empty($entry['args']))
			{
				$arguments = array();

				foreach ($entry['args'] as $arg)
				{
					$arguments[] = $this->_format($arg);
				}

				$message .= implode(', ', $arguments);
			}
			else
			{
				$message .= ' ';
			}

			$message .= ') in ';

			$message .= isset($entry['file']) ? substr($entry['file'], $offset) : '[PHP Kernel]';

			if (isset($entry['line']))
			{
				$message .= ':' . sprintf('%03d', $entry['line']);
			}

			$messages[] = $message;
		}

		return $messages;
	}

	/**
	 * Форматирует величину в соттветствии с ее типом.
	 *
	 * @param   mixed   $value
	 * @return  string
	 */
	function _format($value)
	{
		switch (gettype($value))
		{
			case 'string':
				$string = '"' . htmlspecialchars(substr($value, 0, $this->BACKTRACE_MAX_STRLEN))
					. (strlen($value) > $this->BACKTRACE_MAX_STRLEN ? '...' : '') . '"';
				break;

			case 'object':
				$string = 'Object ' . get_class($value);
				break;

			case 'array':
				$string = 'Array of ' . count($value) . ' elements';
				break;

			case 'resource':
				$string = 'Resource(' . strstr($value, '#') . ')';
				break;

			case 'boolean':
				$string = $value ? 'TRUE' : 'FALSE';
				break;

			case 'NULL':
				$string = 'NULL';
				break;

			default:
				$string = $value;
				break;
		}

		return $string;
	}
}
