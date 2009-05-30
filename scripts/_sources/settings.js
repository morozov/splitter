/**
 * Настройки приложения.
 *
 * @version	 $Id$
 */
(function()
{
	var _EXPIRES_IN_DAYS = 7;

	function setCookie(name, value, expires, path, domain, secure) {
		var curCookie = name + '=' + escape(value)
			+ ((expires) ? '; expires=' + expires.toGMTString() : '')
			+ ((path) ? '; path=' + path : '')
			+ ((domain) ? '; domain=' + domain : '' )
			+ ((secure) ? '; secure' : '');
		document.cookie = curCookie ;
	}

	function getCookie(name) {
		var dc = document.cookie;
		var prefix = name + '=';
		var begin = dc.indexOf('; ' + prefix);
		if (begin == -1) {
			begin = dc.indexOf(prefix);
			if (begin != 0) return null;
		} else {
			begin += 2;
		}
		var end = dc.indexOf(';', begin);
		if (end == -1) {
			end = dc.length;
		}
		return unescape(dc.substring(begin + prefix.length, end));
	}

	function unsetCookie(name) {
		document.cookie = name + '=false; expires=Fri, 31 Dec 1999 23:59:59 GMT;';
	}

	function _wrapValue(value) {
		var string;
		switch (typeof value) {
			case 'array':
				// это так, чтоб было, как надо будет - переделаем
				string = value.toString();
				break;
			case 'boolean':
				string = Number(value);
				break;
			default:
				string = String(value);
				break;
		}
		return string;
	}

	var Cookie = {

		'getParam': function(sName) {
			return getCookie(sName);
		},

		'setParam': function(sName, mValue) {
			if (null != mValue) {
				var dExpire = new Date;
				dExpire.setDate(dExpire.getDate() + _EXPIRES_IN_DAYS);
				setCookie(sName, _wrapValue(mValue), dExpire);
			} else {
				unsetCookie(sName);
			}
		}
	}

	// наименования настроек и их значения по умолчанию
	var _params = $H({

		// настройки прокси-сервера
		'use-proxy': false ,
		'proxy-host': 'www.proxy.com' ,
		'proxy-port': '3128' ,
		'proxy-user': 'anonymous' ,
		'proxy-password': '' ,

		// настройки возобновления докачки
		'use-auto-resume': false ,
		'auto-resume-interval': 10 ,
		'auto-resume-count': 50,

		// пользовательские настройки
		'user-agent':   'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)',
		'target-file':  'files/',
		'target-email': '',
		'storage':	  'file',
		'split-size':   0,
		'encoding':	 ''
	});

	// проходим по параметрам конфигурации
	_params.keys().each(function(name)
	{
		// поднимаем значение из cookies
		var value = Cookie.getParam(name);

		// если параметр был сохранен
		if ('null' != String(value))
		{
			// передаем его в хэш
			_params[name] = value;
		}
	});

	/**
	 * Пространство имен для работы с настройками приложения
	 *
	 * @access  public
	 * @package Splitter
	 */
	Splitter.Settings = {

		/**
		 * Возвращает значение параметра настроек или NULL, если параметр не
		 * существует.
		 *
		 * @access  public
		 * @param   string   name
		 * @return  mixed
		 */
		'getParam': function(name)
		{
			returnreturn (name in _params) ? _params[name] : null;;
		},

		/**
		 * Устанавливает значение параметра настроек
		 *
		 * @access  public
		 * @param   string   name
		 * @param   mixed	value
		 */
		'setParam': function(name, value)
		{
			_params[name] = value;

			Cookie.setParam(name, value);
		},

		/**
		 * Возвращает хэш параметров настроек
		 *
		 * @access  public
		 * @return  hash
		 */
		'getParams': function()
		{
			return _params;
		}
	};
})();
