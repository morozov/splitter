<?php

/**
 * @package	 Splitter
 * @subpackage  share
 * @version	 $Id$
 */
/**
 * Обработчик закачек с файлового сервера www.ifolder.ru
 *
 * @access	  public
 * @package	 Splitter
 * @subpackage  share
 * @see		 Splitter_Share_Abstract
 */
class Splitter_Share_Ifolder extends Splitter_Share_Abstract
{
	/**
	 * Наименование поля формы, в которое пользователь вводит текст на картинке.
	 *
	 * @access  protected
	 * @var	 string
	 */
	var $CAPTCHA_FIELD = 'confirmed_number';

	/**
	 * Массив парсеров, обрабатывающих закачку.
	 *
	 * @access  protected
	 * @var	 array
	 */
	var $PARSERS = array
	(
		'Splitter_Share_Parser_Ifolder',
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

			// проверяем наименование хоста
			&& preg_match('|ifolder\.ru$|', $this->_getHostName($url))

			// проверяем путь файла
			&& preg_match('|^/\d+|', $url->getUri());
	}

	/**
	 * Выводит результат разбора в форму.
	 *
	 * @access  protected
	 * @param   array $params
	 */
	function _output($params)
	{
		$response =& Application::getResponse();
		$response->call('captcha', $this->CAPTCHA_FIELD, $params['captcha']);
		$response->call('param', 'post-data', 'session=' . $params['session-id'] . '&file_id=' . $params['file-id'] . 'action=1');
		$response->call('param', 'method', 'post');
	}
}

