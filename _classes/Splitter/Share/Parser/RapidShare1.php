<?php

/**
 * Выполняет разбор содержимого указанного ресурса. Первый шаг.
 *
 * @version $Id$
 */
class Splitter_Share_Parser_Rapidshare1 extends Splitter_Share_Parser_Abstract
{
	/**
	 * Выполняет разбор содержимого страницы.
	 *
	 * @param Lib_Url $url
	 * @param string $contents
	 * @return array
	 */
	function _parse(&$url, $contents)
	{
		return array
		(
			'action' => $this->_getElementAttribute($contents, 'form', 'action')
		);
	}
}

