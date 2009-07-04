<?php

/**
 * Базовый класс сервисов.
 *
 * @version $Id$
 */
abstract class Splitter_Service_Abstract
{
	/**
	 * Массив параметров запуска сервиса. Устанавливается через self::run().
	 *
	 * @var string
	 */
	var $_params = array();

	/**
	 * Запускает сервис.
	 *
	 * @param array   $params   Параметры запуска
	 * @param array   $reset	Указывает, нужно ли сбрасывать значения
	 *							параметров с предыдущего запуска (используется
	 *							при внутреннем перезапуске сервиса)
	 * @return ArrayObject
	 */
	function run($params, $reset = true)
	{
		$this->_params = $reset
			? $params
			: array_merge($this->_params, $params);

		// создаем пустой объект-массив в качестве ответа
		$result = new Lib_ArrayObject();

		return $result;
	}

	/**
	 * Генерирует событие - сообщение, которое последовательно обрабатывают
	 * зарегистрированные обозреватели.
	 *
	 */
	function fireEvent()
	{
		$response =& Application::getResponse();

		$args = func_get_args();

		call_user_func_array(array($response, 'call'), $args);
	}

	/**
	 * Возвращает значение параметра сервиса.
	 *
	 * @param string   $name	  Наименование параметра
	 * @param mixed	$default   Значение по умолчанию
	 * @return mixed
	 */
	function _getParam($name, $default = null)
	{
		return $this->_hasParam($name) ? $this->_params[$name] : $default;
	}

	/**
	 * Устанавливает параметр сервиса в указанное.
	 *
	 * @param string   $name   Наименование параметра
	 * @param mixed	$value  Значение параметра
	 */
	function _setParam($name, $value)
	{
		return $this->_params[$name] = $value;
	}

	/**
	 * Определяет, указан ли параметр с данным наименованием в параметрах
	 * запуска сервиса.
	 * Проверка производится с преобразованием к строковому типу (кроме объектов).
	 *
	 * @param string   $name
	 * @return boolean
	 */
	function _hasParam($name)
	{
		return array_key_exists($name, $this->_params)
			&& (is_object($this->_params[$name])
			|| strlen($this->_params[$name]) > 0);
	}
}
