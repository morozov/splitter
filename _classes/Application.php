<?php

/**
 * Вообще-то это всего лишь автоматически настраивающийся реестр
 * объектов-синглтонов, плюс пространство имён кое-каких функций.
 * Стоит подумать о расформировании.
 *
 * @version $Id$
 */
class Application
{
	/**
	 * Контекст приложения.
	 *
	 * @var AppContext
	 */
	var $_context;

	/**
	 * Возвращает объект пользовательского запроса.
	 *
	 * @return Splitter_Request_Abstract
	 * @static */
	function getRequest()
	{
		$context = Application::_getContext();
		$request = $context->getObject('request');
		return $request;
	}

	/**
	 * Возвращает объект ответа пользователю.
	 *
	 * @return Splitter_Response_Abstract
	 * @static */
	function getResponse()
	{
		$context = Application::_getContext();
		$response = $context->getObject('response');
		return $response;
	}

	/**
	 * Возвращает объект настроек.
	 *
	 * @return Settings
	 * @static */
	function getSettings()
	{
		$context = Application::_getContext();
		$settings = $context->getObject('settings');
		return $settings;
	}

	/**
	 * Возвращает, запущено ли приложение под Windows®.
	 *
	 * @return boolean
	 * @static */
	public static function isWindows() {
		return 'WIN' == substr(PHP_OS, 0, 3);
	}

	/**
	 * Конструктор. Создает контекст приложения.
	 *
	 * @return Application
	 */
	function Application()
	{
		trigger_error('Application is an abstract class.', E_USER_ERROR);
	}

	/**
	 * Возвращает, запущено ли приложение из интерфейса командной строки.
	 *
	 * @return boolean
	 * @static */
	function _isCli()
	{
		return !isset($_SERVER['SERVER_PROTOCOL']);
	}

	/**
	 * Возвращает контекст приложения.
	 *
	 * @return Context Контекст приложения
	 */
	function _getContext()
	{
		$CONTEXT_OBJECTS = array
		(
			'web' => array
			(
				'request'  => 'Splitter_Request_Web',
				'response' => 'Splitter_Response_Web',
				'settings' => 'Splitter_App_Settings',
			),
			'cli' => array
			(
				'request'  => 'Splitter_Request_Cli',
				'response' => 'Splitter_Response_Cli',
				'settings' => 'Splitter_App_Settings',
			),
			'proxy' => array
			(
				'request'  => 'Splitter_Request_Proxy',
				'response' => 'Splitter_Response_Proxy',
				'settings' => 'Splitter_App_Settings',
			),
		);

		// в PHP4 статичесим переменным нельзя присваивать по ссылке
		static $instance = null;

		if (!is_array($instance))
		{
			$context = new Splitter_App_Context($CONTEXT_OBJECTS[Application::_getInterfaceName()]);

			$instance = array($context);

			set_error_handler(array(new Splitter_App_ErrorHandler(), 'handle'));
		}

		return $instance[0];
	}

	/**
	 * Возвращает наименование интерфейса, через который запущено приложение.
	 *
	 * @return string
	 */
	function _getInterfaceName()
	{
		return Application::_isCli() ? 'cli' : ('POST' == $_SERVER['REQUEST_METHOD'] ? 'web' : 'proxy');
	}

	/**
	 * Преобразует строку из UTF-8 в Windows-1251, если есть такая возможность.
	 *
	 * @param string $string
	 * @return string
	 */
	public static function utf2win($string) {

		$encoding_from = 'utf-8';
		$encoding_to = 'windows-1251';

		if (mb_check_encoding($string, $encoding_from)) {
			$string = mb_convert_encoding($string, $encoding_to, $encoding_from);
		}

		return $string;
	}
}
