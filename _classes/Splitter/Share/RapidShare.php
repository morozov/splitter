<?php

/**
 * Обработчик закачек с файловых серверов www.rapidshare.com и www.rapidshare.de
 *
 * @version $Id$
 */
class Splitter_Share_RapidShare extends Splitter_Share_Abstract
{
	/**
	 * Наименование поля формы, в которое пользователь вводит текст на картинке.
	 *
	 * @var string
	 */
	var $CAPTCHA_FIELD = 'captcha';

	/**
	 * Массив парсеров, обрабатывающих закачку.
	 *
	 * @var array
	 */
	var $PARSERS = array
	(
		1 => 'Splitter_Share_Parser_RapidShare1',
		2 => 'Splitter_Share_Parser_RapidShare2',
	);

	/**
	 * Возвращает, могут ли указанные URL и метод запроса быть обработаны
	 * с помощью данного компонента.
	 *
	 * @param Lib_Url $url
	 * @param string $method
	 * @return boolean
	 */
	function canProcess(&$url, $method)
	{
		return parent::canProcess($url, $method)

			// проверяем навименование хоста
			&& in_array($this->_getHostName($url), array('rapidshare.de', 'rapidshare.com'))

			// проверяем путь файла
			&& preg_match('|^/files/\d+/|', $url->getUri());
	}

	/**
	 * Обрабатывает указанный URL.
	 *
	 * @param Lib_Url $url
	 */
	function process(&$url, $params = array())
	{
		if (preg_match('/\.com$/', $url->getHost()))
		{
			$params = $this->_getParams($url, $params, 1);

			$this->CAPTCHA_FIELD = 'accesscode';

			$action = $params['action'];
		}
		else
		{
			$action = 'http://' . $url->getHost() . '/';
		}

		$action = new Lib_Url($action);

		$params = $this->_getParams($action, array
		(
			'post-data' => 'uri=' . rawurlencode($url->getUri()) . '&dl.start=Free',
			'referer' => $url->toString(),
		), 2);

		if (is_array($params))
		{
			$this->_output($params);
		}
	}

	/**
	 * Выводит результат разбора в форму.
	 *
	 * @param array $params
	 */
	function _output($params)
	{
		$response = Application::getResponse();
		if (isset($params['counter'])) {
			$response->call('counter', $params['counter']);
		}
		if (isset($params['captcha'])) {
			$response->call('captcha', $this->CAPTCHA_FIELD, $params['captcha']);
		}
		$response->call('param', 'url', $params['action']);
	}
}
