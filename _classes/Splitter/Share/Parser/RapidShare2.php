<?php

/**
 * @package	 Splitter
 * @subpackage  utils
 * @version	 $Id$
 */
/**
 * Выполняет разбор содержимого указанного ресурса. Второй шаг.
 *
 * @package	 Splitter
 * @subpackage  utils
 * @see		 abstract_Object
 */
class Splitter_Share_Parser_Rapidshare2 extends Splitter_Share_Parser_Abstract
{
	/**
	 * Шаблон регулярного выражения поиска закодированного текста HTML.
	 *
	 * @var	 string
	 */
	var $REGEXP_ESCAPED_HTML = "/var tt = '([^']+)'/i";

	/**
	 * Возвращает дополнительные параметры ресурса
	 *
	 * @return  array
	 */
	function _getRequestParams()
	{
		return array
		(
			'method' => 'post',
			'content-type' => 'application/x-www-form-urlencoded',
		) + parent::_getRequestParams();
	}

	/**
	 * Выполняет разбор содержимого страницы.
	 *
	 * @param   Lib_Url $url
	 * @param   string $contents
	 * @return  array
	 */
	function _parse(&$url, $contents)
	{
		$success = true;

		$response =& Application::getResponse();

		$result = array();

		// пытаемся время, на которое запущен таймер
		if (!is_null($timer = $this->_getCounterTime($contents)))
		{
			$result['counter'] = $timer;
		}

		// пытаемся определить кусок HTML, который прописан в js
		if (!is_string($html = $this->_getResponseHtml($contents)))
		{
			$this->_throw('Невозможно определить закодированный HTML', $contents);
			return false;
		}
		// пытаемся определить action формы, по которому отправляется запрос
		elseif (is_string($action = $this->_getElementAttribute($html, 'form', 'action')))
		{
			$result['action'] = $action;

			return $result;
		}
		elseif ($this->_isTooManyUsers($html))
		{
			trigger_error('Слишко много халявщиков, просят зайти попозже', E_USER_WARNING);
			return false;
		}
		elseif ($this->_isFileDeleted($html))
		{
			trigger_error('Файл удален с сервера', E_USER_WARNING);
			return false;
		}
		else
		{
			$this->_throw('Невозможно определить картинку-captcha', $html);
			return false;
		}

		$this->_throw('Невозможно определить адрес отправки формы', $html);
		return false;
	}

	/**
	 * Пытается определить закодированный кусок HTML, который появляется после
	 * того, как отработает таймер.
	 *
	 * @param   string $text
	 * @return  string
	 */
	function _getResponseHtml($contents)
	{
		return preg_match($this->REGEXP_ESCAPED_HTML, $contents, $matches)
			? urldecode($matches[1]) : null;
	}

	/**
	 * Пытается определить время, на которое запущен javascript-счетчик на странице.
	 *
	 * @param   string $contents
	 * @return  integer
	 */
	function _getCounterTime($contents)
	{
		return preg_match('/var\s+c\s*=\s*(\d+)/i', $contents, $matches)
			? (int)$matches[1] : null;
	}

	/**
	 * Пытается прочитать сообщение о том, что файл удвлен с сервера.
	 *
	 * @param   string $contents
	 * @return  boolean
	 */
	function _isFileDeleted($contents)
	{
		return false !== strpos($contents, 'deleted');
	}

	/**
	 * Пытается прочитать сообщение о том, что файл скачивают слишком много
	 * пользователей.
	 *
	 * @param   string $contents
	 * @return  boolean
	 */
	function _isTooManyUsers($contents)
	{
		return false !== strpos($contents, 'Too many');
	}
}
