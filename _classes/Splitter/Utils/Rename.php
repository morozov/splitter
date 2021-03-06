<?php

/**
 * Утилита переименования файла по маске или регулярному выражению.
 *
 * @version $Id$
 */
class Splitter_Utils_Rename
{
	/**
	 * Шаблон поиска
	 *
	 * @var string
	 */
	var $_search;

	/**
	 * Строка для замены
	 *
	 * @var string
	 */
	var $_replace;

	/**
	 * Флаг исспользования регулярных выражений
	 *
	 * @var boolean
	 */
	var $_useRegExp = false;

	/**
	 * Конструктор.
	 *
	 * @param string $search
	 * @param string $replace
	 * @param string $useRegExp
	 * @return Splitter_Utils_Rename
	 */
	function Splitter_Utils_Rename($search, $replace, $useRegExp)
	{
		$this->_replace = $replace;

		$this->_useRegExp = $useRegExp;

		if ($this->_useRegExp)
		{
			// собираем шаблон регулярного выражения поиска
			$pattern = '/' . str_replace('/', '\/', $search) . '/i';

			// если проверка прошла
			if (is_null($error = $this->_getPatternError($pattern)))
			{
				// сохраняем шаблон поиска
				$this->_search = $pattern;
			}
			else
			{
				trigger_error($error);
			}
		}
		else
		{
			$this->_search = $search;
		}
	}

	/**
	 * Возвращает содержимое ресурса.
	 *
	 * @param array   $params   Параметры запуска
	 * @return string
	 */
	function rename($fileName)
	{
		if (strlen($this->_search) > 0)
		{
			if ($this->_useRegExp)
			{
				$result = preg_replace($this->_search, $this->_replace, $fileName);
			}
			else
			{
				// str_ireplace реализуется в PHP_Compat
				$result = str_ireplace($this->_search, $this->_replace, $fileName);
			}
		}
		else
		{
			$result = $fileName;
		}

		return $result;
	}

	/**
	 * Выполняет проверку шаблона регулярного выражения и возвращает ошибку.
	 *
	 * @param string $pattern
	 * @return mixed
	 */
	function _getPatternError($pattern)
	{
		$php_errormsg = null;

		// выключаем отображение и включаем отслеживание ошибок
		ini_set('error_reporting', '0');
		ini_set('track_errors', '1');

		// пытаемся использовать шаблон
		preg_match($pattern, 'dummy');

		$error = $php_errormsg;

		// восстанавливаем конфигурацию
		ini_restore('track_errors');
		ini_restore('error_reporting');

		return $error;
	}
}
