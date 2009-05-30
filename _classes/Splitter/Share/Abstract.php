<?php

/**
 * @package	 Splitter
 * @subpackage  share
 * @version	 $Id$
 */
/**
 * Базовый класс компонентов, скачивающих файлы с файловых хостингов.
 *
 * @access	  public
 * @package	 Splitter
 * @subpackage  share
 * @see		 abstract_Object
 * @abstract
 */
abstract class Splitter_Share_Abstract
{
	/**
	 * Наименование поля формы, в которое пользователь вводит текст на картинке.
	 *
	 * @access  protected
	 * @var	 string
	 */
	var $CAPTCHA_FIELD;

	/**
	 * Массив парсеров, обрабатывающих закачку.
	 *
	 * @access  protected
	 * @var	 array
	 */
	var $PARSERS = array();

	/**
	 * Возвращает, могут ли указанные URL и метод запроса быть обработаны
	 * с помощью данного компонента.
	 *
	 * @access  public
	 * @param   Lib_Url $url
	 * @param   string $method
	 * @return  boolean
	 */
	function canProcess(&$url, $method)
	{
		return in_array($url->getScheme(), array('http', null)) && 'get' == $method;
	}

	/**
	 * Обрабатывает указанный URL.
	 *
	 * @access  public
	 * @param   Lib_Url $url
	 */
	function process(&$url, $params = array())
	{
		if (is_array($params = $this->_getParams($url, $params)))
		{
			$this->_output($params);
		}
	}

	/**
	 * Выводит результат разбора в форму.
	 *
	 * @access  public
	 * @param   array $params
	 */
	abstract function _output($params);

	/**
	 * Возвращает содержимое ресурса с указанными параметрами.
	 *
	 * @access  public
	 * @param   Lib_Url $url
	 * @return  string
	 */
	function _getParams(&$url, $params = array(), $name = null)
	{
		$parser =& $this->_getParser($name);

		return $parser->parse($url, $params);
	}

	/**
	 * Возвращает персер содержимого страницы с указанным наименованием или
	 * основной по умолчанию.
	 *
	 * @access  protected
	 * @param   string $name
	 * @return  Splitter_Share_Parser_Abstract
	 */
	function _getParser($name = null)
	{
		$class = is_null($name)
			? reset($this->PARSERS) : $this->PARSERS[$name];
		return new $class;
	}

	/**
	 * Возвращает имя хоста URL без "www".
	 *
	 * @access  protected
	 * @param   Lib_Url $url
	 * @return  string
	 */
	function _getHostName($url)
	{
		return preg_replace('|^www\.|', '', $url->getHost());
	}
}
