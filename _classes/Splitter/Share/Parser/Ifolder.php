<?php

/**
 * @package	 Splitter
 * @subpackage  utils
 * @version	 $Id$
 */
/**
 * Выполняет разбор содержимого указанного ресурса.
 *
 * @package	 Splitter
 * @subpackage  utils
 * @see		 abstract_Object
 */
class Splitter_Share_Parser_Ifolder extends Splitter_Share_Parser_Abstract
{
	/**
	 * Выполняет разбор содержимого страницы.
	 *
	 * @param   Lib_Url $url
	 * @param   string $contents
	 * @return  array
	 */
	function _parse(&$url, $contents)
	{
		$success = false;

		// пытаемся определить идентификатор сессии
		if (is_string($sid = $this->_getElementAttribute($contents, 'input', 'value', create_function('$attributes', 'return "session" == @$attributes["name"];'))))
		{
			// пытаемся определить идентификатор файла
			if (is_string($file = $this->_getElementAttribute($contents, 'input', 'value', create_function('$attributes', 'return "file_id" == @$attributes["name"];'))))
			{
				// определяем адрес картинки captcha
				$captcha = $this->_getFullUrl($url, '/random/images/?session=' . $sid . '&mem');

				$success = true;
			}
			else
			{
				$this->_throw('Невозможно определить идентификатор файла', $contents);
			}
		}
		else
		{
			$this->_throw('Невозможно определить идентификатор сессии', $contents);
		}

		return $success ? array
		(
			'captcha' => $captcha,
			'file-id' => $file,
			'session-id' => $sid,
		) : false;
	}
}
