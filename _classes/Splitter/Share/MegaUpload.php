<?php

/**
 * @package	 Splitter
 * @subpackage  share
 * @version	 $Id$
 */
/**
 * Обработчик закачек с файлового сервера www.megaupload.com
 *
 * @access	  public
 * @package	 Splitter
 * @subpackage  share
 * @see		 Splitter_Share_Abstract
 */
class Splitter_Share_MegaUpload extends Splitter_Share_Abstract
{
	/**
	 * Наименование поля формы, в которое пользователь вводит текст на картинке.
	 *
	 * @access  protected
	 * @var	 string
	 */
	var $CAPTCHA_FIELD = 'captcha';

	/**
	 * Массив парсеров, обрабатывающих закачку.
	 *
	 * @access  protected
	 * @var	 array
	 */
	var $PARSERS = array
	(
		1 => 'Splitter_Share_Parser_MegaUpload1',
		2 => 'Splitter_Share_Parser_MegaUpload2',
	);

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
		return parent::canProcess($url, $method)

			// проверяем навименование хоста
			&& 'megaupload.com' ==  $this->_getHostName($url)

			// проверяем путь файла
			&& preg_match('|^/\?d=[A-Z0-9]+|', $url->getUri());
	}

	/**
	 * Обрабатывает указанный URL.
	 *
	 * @access  public
	 * @param   Lib_Url $url
	 */
	function process(&$url, $params = array())
	{
	}

	/**
	 * Выводит результат разбора в форму.
	 *
	 * @access  public
	 * @param   array $params
	 */
	function _output($params)
	{
	}
}
