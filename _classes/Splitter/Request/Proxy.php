<?php

/**
 * @package	 Splitter
 * @subpackage  request
 * @version	 $Id$
 */
/**
 * Объект запроса для режима прокси.
 *
 * @package	 Splitter
 * @subpackage  request
 * @see		 Splitter_abstract_Request
 */
class Splitter_Request_Proxy extends Splitter_Request_Web
{
	/**
	 * �?нициализирует массив параметров запроса.
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
	 * @return  array
	 */
	function _getRawParams()
	{
		return $_GET;
	}
}
