<?php

/**
 * Обработчик закачек с файлового сервера www.depositfiles.com
 *
 * @version $Id$
 */
class Splitter_Share_DepositFiles extends Splitter_Share_Abstract
{
	/**
	 * Наименование поля формы, в которое пользователь вводит текст на картинке.
	 *
	 * @var string
	 */
	var $CAPTCHA_FIELD = 'img_code';

	/**
	 * Массив парсеров, обрабатывающих закачку.
	 *
	 * @var array
	 */
	var $PARSERS = array
	(
		'Splitter_Share_Parser_DepositFiles',
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
			&& 'depositfiles.com' == $this->_getHostName($url);
	}

	/**
	 * Выводит результат разбора в форму.
	 *
	 * @param array $params
	 */
	function _output($params)
	{
		$response = Application::getResponse();
		$response->captcha($this->CAPTCHA_FIELD, $params['captcha']);
		$response->param('post-data', 'file_password=&gateway_result=1&icid=' . $params['icid'] . '&go=1');
		$response->param('method', 'post');
		$response->counter(100);
	}
}
