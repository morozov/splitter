<?php

/**
 * Утилита создания ссылки для скачивания через прокси.
 *
 * @version $Id$
 */
class Splitter_Utils_ProxyLink
{
	/**
	 * Создает ссылку.
	 *
	 * @return string
	 */
	function generate($url, $splitSize, $encoding)
	{
		$obj = new Lib_Url('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

		if ($encoding)
		{
			$util = new Splitter_Utils_Encode($encoding);

			$params = array(
				'encoding' => $encoding,
				'url' => $util->encode($url),
			);
		}
		else
		{
			$params = array(
				'url' => $url,
			);
		}

		$parts = 1;

		$result = array();

		for ($i = 1; $i <= $parts; $i++)
		{
			if ($parts > 1)
			{
				$params['part'] = $i;
			}

			$obj->setQuery($params);

			$result[] = $obj->toString();
		}

		return $result;
	}
}
