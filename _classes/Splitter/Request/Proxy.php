<?php

/**
 * Объект запроса для режима прокси.
 *
 * @version $Id$
 */
class Splitter_Request_Proxy extends Splitter_Request_Web
{
	/**
	 * Инициализирует массив параметров запроса.
	 *
	 */
	function _initParams()
	{
		parent::_initParams();

		$this->_params['storage'] = 'proxy';
		$this->_params['method'] = 'get';

		$encoding = $this->getParam('encoding');

		if ($encoding)
		{
			$util = new Splitter_Utils_Encode($encoding);

			$this->_params['url'] = $util->decode($this->getParam('url'));
			$this->_params['filename'] = $util->decode($this->getParam('filename'));
		}
	}

	/**
	 * Возвращает параметры в исходном виде.
	 *
	 * @return array
	 */
	function _getRawParams()
	{
		return $_GET;
	}
}
