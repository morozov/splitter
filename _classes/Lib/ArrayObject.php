<?php

/**
 * @package	 Splitter
 * @subpackage  Lib
 * @version	 $Id$
 */
/**
 * Объектная обертка для массивов.
 *
 * @package	 Splitter
 * @subpackage  Lib
 * @see		 abstract_Object
 */
class Lib_ArrayObject
{
	/**
	 * Массив элементов массива.
	 *
	 * @var	 array
	 */
	var $_array = array();

	/**
	 * Конструктор.
	 *
	 * @param   array   $array
	 */
	function Lib_ArrayObject($array = array())
	{
		$this->_array = $array;
	}

	/**
	 * Добавляет элемент в массив по значению.
	 *
	 * @param   mixed   $value
	 */
	function append($value)
	{
		$this->_array[] = $value;
	}

	/**
	 * Возвращает количество элементов массива.
	 *
	 * @return  integer
	 */
	function count()
	{
		return count($this->_array);
	}

	/**
	 * Возвращает существует ли в массиве элемент с указанным индексом.
	 *
	 * @param   mixed	$index
	 * @return  boolean
	 */
	function offsetExists($index)
	{
		return array_key_exists($index, $this->_array);
	}

	/**
	 * Возвращает значение элемента массива с указанным индексом или FALSE,
	 * если элемент не существует.
	 *
	 * @param   mixed	$index
	 * @return  mixed
	 */
	function offsetGet($index)
	{
		return $this->offsetExists($index)
			? $this->_array[$index]: false;
	}

	/**
	 * Устанавливает значение элемента массива с указанным индексом.
	 *
	 * @param   mixed	$index
	 * @param   mixed	$value
	 */
	function offsetSet($index, $value)
	{
		$this->_array[$index] = $value;
	}

	/**
	 * Удаляет элемента массива с указанным индексом.
	 *
	 * @param   mixed	$index
	 */
	function offsetUnset($index)
	{
		unset($this->_array[$index]);
	}
}
