<?php

require_once 'html/htmlparser.inc.php';

/**
 * Выполняет разбор содержимого указанного ресурса.
 *
 * @version $Id$
 */
abstract class Splitter_Share_Parser_Abstract
{
	/**
	 * Возвращает параметры страницы, соответствующей указанному ресурсу.
	 *
	 * @param array   $params   Параметры запуска
	 * @return string
	 */
	function parse(&$url, $params = array())
	{
		$util = new Splitter_Utils_GetContents();

		$contents = $util->getContents($url, array_merge($params, $this->_getRequestParams()));

		return (false !== $contents) ? $this->_parse($url, $contents) : false;
	}

	/**
	 * Возвращает значение атрибута указанного элемента.
	 *





	 * @return string
	 */
	function _getElementAttribute($contents, $tagName, $attribute, $callback = null)
	{
		$parser =& new HtmlParser($contents);

		// парсер идет по документу
		while ($parser->parse())
		{
			// если это элемент с нужным нам именем
			if ($parser->iNodeName == $tagName && $parser->iNodeType == NODE_TYPE_ELEMENT)
			{
				// если указана функция дополнительной проверки, и элемент ее не прошел
				if (!is_null($callback) && !call_user_func($callback, $parser->iNodeAttributes))
				{
					// пропускаем его
					continue;
				}

				return $parser->iNodeAttributes[$attribute];
			}
		}

		return null;
	}

	/**
	 * Формирует полный адрес из ссылающегося и относительного.
	 *
	 * @param Lib_Url $referer
	 * @param string $relative
	 * @return string
	 */
	function _getFullUrl(&$referer, $relative)
	{
		// создаем копию URL
		$url = clone($referer);

		// перенаправляем со ссылающуйся страницы по относительной ссылке
		$url->applyRedirect($relative);

		return $url->toString();
	}

	/**
	 * Возвращает параметры запроса по умолчанию.
	 *
	 * @return array
	 */
	function _getRequestParams()
	{
		return array('method' => 'get');
	}

	/**
	 * Выполняет разбор содержимого страницы.
	 *
	 * @param Lib_Url $url
	 * @param string $contents
	 * @return array
	 */
	abstract function _parse(&$url, $contents);

	/**
	 * Генерирует ошибку разбора.
	 *
	 * @param string $error
	 * @param string $contents
	 */
	function _throw($error, $contents)
	{
		trigger_error($error, E_USER_WARNING);

		$this->_debug($contents);
	}

	/**
	 * Записывает отладочную информацию в файл.
	 *
	 * @param string $string
	 */
	function _debug($string)
	{
		$fp = fopen('files/debug.txt', 'w');
		fwrite($fp, $string);
		fclose($fp);
	}
}
