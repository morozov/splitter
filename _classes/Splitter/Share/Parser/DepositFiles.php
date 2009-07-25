<?php

/**
 * Выполняет разбор содержимого указанного ресурса.
 *
 * @version $Id$
 */
class Splitter_Share_Parser_DepositFiles extends Splitter_Share_Parser_Abstract
{
	/**
	 * Возвращает дополнительные параметры ресурса
	 *
	 * @return array
	 */
	function _getRequestParams()
	{
		return array_merge(parent::_getRequestParams(), array
		(
			'method' => 'post',
			'content-type' => 'application/x-www-form-urlencoded',
			'post-data' => 'gateway_result=1',
		));
	}

	/**
	 * Выполняет разбор содержимого страницы.
	 *
	 * @param Lib_Url $url
	 * @param string $contents
	 * @return array
	 */
	function _parse(&$url, $contents)
	{
		$success = true;

		if (!is_string($icid = $this->_getICID($contents)))
		{
			$this->_throw('Невозможно определить icid', $contents);
			return false;
		}

		return array(
			'icid' => $icid,
			'captcha' => $this->_getFullUrl($url, '/get_download_img_code.php?icid=' . $icid),
		);
	}

	function _getICID($contents) {
		return preg_match('/var img_code_icid = \'([^\']+)\'/', $contents, $matches)
			? $matches[1] : false;
	}
}
