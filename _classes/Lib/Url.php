<?php

/**
 * Объектная обертка для parse_url.
 *
 * @version $Id$
 */
class Lib_Url
{
	/**
	 * Шаблон регулярного выражения для разбора строки в объект.
	 *
	 * @var string
	 */
	//							 /	2	\	  /  4  \   / 6\	 /	7	\   /  9  \   /   11   \   / 13 \	 /15\
	var $REGEXP_PARSE_STRING = '|^(([a-z0-9]+)://)?(([^:]*)(\:(.*))?@)?([\w\-\.]+)(\:([^/]*))?((/[^\?#]*)(\?([^#]*))?(#(.*))?)?$|i';

	/**
	 * Шаблон регулярного выражения определения полного URL.
	 *
	 * @var string
	 */
	var $REGEXP_URL_FULL = '|^[a-z0-9]+://|i';

	/**
	 * Шаблон регулярного выражения определения абсолютного URL.
	 *
	 * @var string
	 */
	var $REGEXP_URL_ABSOLUTE = '|^/|i';

	/**
	 * Схема.
	 *
	 * @var string
	 */
	var $_scheme;

	/**
	 * Хост.
	 *
	 * @var string
	 */
	var $_host;

	/**
	 * Порт.
	 *
	 * @var integer
	 */
	var $_port;

	/**
	 * Имя пользователя.
	 *
	 * @var string
	 */
	var $_userName;

	/**
	 * Пароль.
	 *
	 * @var string
	 */
	var $_password;

	/**
	 * Путь.
	 *
	 * @var string
	 */
	var $_path;

	/**
	 * Строка запроса.
	 *
	 * @var string
	 */
	var $_query;

	/**
	 * Фрагмент (якорь).
	 *
	 * @var string
	 */
	var $_fragment;

	/**
	 * Конструктор. инициализирует разбор строки, переданной в качестве
	 * аргумента.
	 *
	 * @param string $string
	 * @return Url
	 */
	function Lib_Url($string = '')
	{
		$this->fromString($string);
	}

	/**
	 * Возвращает схему. В случае, если URL не содержит схемы, возвращает
	 * указанное значение по умолчанию.
	 *
	 * @param string $default
	 * @return string
	 */
	function getScheme($default = null)
	{
		return empty($this->_scheme) ? $default : $this->_scheme;
	}

	/**
	 * Устанавливает схему.
	 *
	 * @param string   $scheme
	 */
	function setScheme($scheme)
	{
		$this->_scheme = $scheme;
	}

	/**
	 * Возвращает имя пользователя.
	 *
	 * @return string
	 */
	function getUserName()
	{
		return $this->_userName;
	}

	/**
	 * Устанавливает имя пользователя.
	 *
	 * @param string   $userName
	 */
	function setUserName($userName)
	{
		$this->_userName = $userName;
	}

	/**
	 * Возвращает пароль.
	 *
	 * @return string
	 */
	function getPassword()
	{
		return $this->_password;
	}

	/**
	 * Устанавливает пароль.
	 *
	 * @param string   $password
	 */
	function setPassword($password)
	{
		$this->_password = $password;
	}

	/**
	 * Возвращает хост.
	 *
	 * @return string
	 */
	function getHost()
	{
		return $this->_host;
	}

	/**
	 * Устанавливает хост.
	 *
	 * @param string   $host
	 */
	function setHost($host)
	{
		$this->_host = $host;
	}

	/**
	 * Возвращает порт. В случае, если URL не содержит порта, возвращает
	 * указанное значение по умолчанию.
	 *
	 * @param integer  $default
	 * @return string
	 */
	function getPort($default = null)
	{
		return is_null($this->_port) ? $default: $this->_port;
	}

	/**
	 * Устанавливает порт.
	 *
	 * @param integer  $port
	 */
	function setPort($port)
	{
		$this->_port = is_numeric($port) ? (integer)$port : null;
	}

	/**
	 * Возвращает путь.
	 *
	 * @return string
	 */
	function getPath()
	{
		return $this->_path;
	}

	/**
	 * Устанавливает путь.
	 *
	 * @param string  $path
	 */
	function setPath($path)
	{
		$this->_path = $path;
	}

	/**
	 * Возвращает строку запроса.
	 *
	 * @return string
	 */
	function getQuery()
	{
		return $this->_query;
	}

	/**
	 * Устанавливает путь.
	 *
	 * @param string  $query
	 */
	function setQuery($query)
	{
		if (is_array($query))
		{
			$pairs = array();

			foreach ($query as $param => $value)
			{
				// не кодируем параметры. они будут закодированы на выходе
				$pairs[] = $param . '=' . $value;
			}

			$query = implode('&', $pairs);
		}

		$this->_query = $query;
	}

	/**
	 * Возвращает URI.
	 *
	 * @return string
	 */
	function getUri()
	{
		// получаем значение пути
		$uri = $this->_path;

		// если указана строка запроса
		if (strlen($this->_query) > 0)
		{
			// добавляем ее
			$uri .= '?' . $this->_query;
		}

		return $uri;
	}

	/**
	 * Устанавливает URI.
	 *
	 * @param string  $uri
	 */
	function setUri($uri)
	{
		// разбиваем URI на путь и строку запроса
		list($path, $query) = $this->_splitUri($uri);

		// устанавливаем каждый элемент по отдельности
		$this->setPath($path);
		$this->setQuery($query);
	}

	/**
	 * Возвращает значение фрагмента URL.
	 *
	 * @return string
	 */
	function getFragment()
	{
		return $this->_fragment;
	}

	/**
	 * Устанавливает значение фрагмента URL.
	 *
	 * @param string  $fragment
	 */
	function setFragment($fragment)
	{
		$this->_fragment = $fragment;
	}

	/**
	 * Возвращает имя файла.
	 *
	 * @return string
	 */
	function getFileName()
	{
		return substr($this->_path, (strrpos($this->_path, '/') + 1));
	}

	/**
	 * Применяет перенаправление.
	 *
	 * @param string	$string
	 */
	function applyRedirect($location)
	{
		// удаляем элементы URL'а, которые точно изменятся
		$this->_query	= null;
		$this->_fragment = null;

		switch (true)
		{
			// если указанное перенаправление содержит полный урл
			case preg_match($this->REGEXP_URL_FULL, $location):

				// просто пересоздаем объект из нового урла
				$this->fromString($location);
				break;

			// если указано перенаправление по абсолютному пути
			case preg_match($this->REGEXP_URL_ABSOLUTE, $location):

				$this->_applyAbsoluteRedirect($location);
				break;

			default:
				// иначе принимаем перенаправление как по относительному пути
				$this->_applyRelativeRedirect($location);
				break;
		}
	}

	/**
	 * Анализирует строку, преобразовывает в свойства объекта URL.
	 *
	 * @param string	$string
	 */
	function fromString($string)
	{
		if (preg_match($this->REGEXP_PARSE_STRING, trim($string), $matches))
		{
			$this->_scheme   = $matches[2];
			$this->_userName = $matches[4];
			$this->_password = $matches[6];
			$this->_host	 = $matches[7];
			$this->_port	 = isset($matches[9]) && is_numeric($matches[9]) ? $matches[9]: null;
			$this->_path	 = isset($matches[11]) ? $matches[11]: '/';
			$this->_query	= isset($matches[13]) ? $matches[13]: null;
			$this->_fragment = isset($matches[15]) ? $matches[15]: null;
		}
		else
		{
			// :TODO: morozov 12022006: эта ситуация должна быть невозможна
			// в целях отладки можно бросить сообщение об ошибке:
			// :KLUDGE: morozov 23092007: возможна, и запросто. поклацай
			// пальцами по клавиатуре! :)
			trigger_error('Невозможно разобрать URL: ' . $string, E_USER_WARNING);
		}
	}

	/**
	 * Возвращает строковое представление объекта URL.
	 *
	 * @return string
	 */
	function toString()
	{
		$string = '';

		// добавляем схему
		if (strlen($this->_scheme) > 0)
		{
			$string .= $this->_scheme . '://';
		}

		// добавляем имя пользователя
		if (strlen($this->_userName) > 0)
		{
			$string .= $this->_userName;

			// добавляем пароль
			if (strlen($this->_password) > 0)
			{
				$string .= ':' . $this->_password;
			}

			$string .= '@';
		}

		$string .= $this->_host;

		// добавляем порт
		if (strlen($this->_port) > 0)
		{
			$string .= ':' . $this->_port;
		}

		// кодируем путь в соответствии с RFC 1738
		$string .= $this->_encodePath($this->_path);

		// добавляем QUERY_STRING
		if (strlen($this->_query) > 0)
		{
			$string .= '?' . $this->_encodeQuery($this->_query);
		}

		// добавляем fragment
		if (strlen($this->_fragment) > 0)
		{
			$string .= '#' . $this->_fragment;
		}

		return $string;
	}

	/**
	 * Применяет перенаправление по абсолютному URL.
	 *
	 */
	function _applyAbsoluteRedirect($uri)
	{
		// на данный момент мы уверены, что $uri начинается на /, поэтому
		// просто удаляем первый символ
		$uri = substr($uri, 1);

		// очищаем текущий путь, т.к. он должен быть полностью обновлен
		$this->_path = '/';

		// применяем редирект относительно корня сервера
		$this->_applyRelativeRedirect($uri);
	}

	/**
	 * Применяет перенаправление по относительному URL.
	 * TODO: осмыслить, как это на самом деле происходит
	 *
	 */
	function _applyRelativeRedirect($uri)
	{
		if (0 == strlen($uri))
		{
			return;
		}

		// разбиваем URI на путь и строку запроса, строку запроса сразу
		// отправляем в соответствующий атрибут объекта
		list($redirectPath, $this->_query) = $this->_splitUri($uri);

		// разбиваем текущий путь на секции
		$currentPathArr  = $this->_splitPath($this->_path);

		// разбиваем путь перенаправления на секции
		$redirectPathArr = $this->_splitPath($redirectPath);

		// :KLUDGE: morozov 06012008: не помню, зачем это, но вроде так работает
		array_pop($currentPathArr);

		// берем по одной секции с начала пути перенаправления
		while (!is_null($section = array_shift($redirectPathArr)))
		{
			switch ($section)
			{
				// точку пропускаем (текущая директория)
				case '.':
					break;

				// удаляем одну секцию с конца текущего пути (на директорию выше)
				case '..':
					array_pop($currentPathArr);
					break;

				// приписываем секцию в конец текущего пути
				default:
					array_push($currentPathArr, $section);
			}
		}

		// объединяем секции в путь
		$this->_path = implode('/', $currentPathArr);
	}

	/**
	 * Кодирует путь в соответствии с RFC 1738.
	 *
	 * @param string	$path
	 * @return string
	 */
	function _encodePath($path)
	{
		$pathArr = array();

		// проходим по массиву секций - частей path
		foreach (explode('/', $path) as $section)
		{
			$pathArr[] = $this->_encode($section);
		}

		// склеиваем полученный массив в строку
		return implode('/', $pathArr);
	}

	/**
	 * Кодирует строку запроса в соответствии с RFC 1738.
	 *
	 * @param string	$query
	 * @return string
	 */
	function _encodeQuery($query)
	{
		$params = array();

		// проходим по парам "параметр=значение" (или просто "параметр")
		foreach (explode('&',  $query) as $pair)
		{
			// определяем наименование параметра и значение
			$pairArr = explode('=', $pair, 2);

			// добавляем в массив параметров наименование
			$params[] = $pairArr[0]

				// и, если есть и значение,
				. (count($pairArr) > 1

					// то значение, приведенное в соответствие со стандартом
					? '=' . $this->_encode($pairArr[1]) : '');
		}

		// склеиваем полученные параметры в строку
		return implode('&', $params);
	}

	/**
	 * Разбивает путь URL'а на элементы (типа файлы/директории) и возвращает
	 * в виде массива.
	 *
	 * @param string  $path
	 * @return array
	 */
	function _splitPath($path)
	{
		return explode('/', $path);
	}

	/**
	 * Разбивает URI URL'а на путь и QUERY_STRING.
	 *
	 * @param string  $uri
	 * @return array
	 */
	function _splitUri($uri)
	{
		return array_pad(explode('?', $uri, 2), 2, null);
	}

	/**
	 * Кодирует строку в соответствии с RFC 1738.
	 * :KLUDGE: morozov 14062006: тут возможна небольшая лажа, если параметры
	 * уже были закодированы через urlencode - тогда "+"'ы будут замемены на
	 * "%20", т.е. фактически, **уже стандартный** url будет искажен. Еще фигня
	 * возможна, если незакодированные символы "&" и "=" передаются не как
	 * разделители параметров, а как составляющие некоего значения, а,
	 * следовательно, тоже должны быть закодированы. На такие случаи забьём.
	 *
	 * @param string  $string
	 * @return $string
	 */
	function _encode($string)
	{
		// декодируем строку (чтобы не закодировать дважды, если она уже
		// закодирована) и кодируем обратно
		return rawurlencode(rawurldecode($string));
	}
}
