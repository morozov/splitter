<?php

/**
 * @package	 Splitter
 * @subpackage  storage
 * @version	 $Id$
 */
/**
 * ���������� ���������� ������ �� FTP (� �������������� wrappers).
 *
 * @package	 Splitter
 * @subpackage  storage.file
 * @see		 abstract_Object
 * @abstract
 */
class Splitter_Storage_File_Ftp extends Splitter_Storage_File_Abstract {

	/**
	 * ����� �������� ������ ��� ������. ���� ����� � ���� � ������ ������, �.�.
	 * �������, ������������� � PHP �� ��������� ������ "a".
	 *
	 * @var string
	 */
	protected $fopen_mode = 'wb';
}
