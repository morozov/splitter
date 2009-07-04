<?php

/**
 * Реализация сохранения данных на FTP (с использованием wrappers).
 *
 * @version $Id$
 */
abstract class Splitter_Storage_File_Ftp extends Splitter_Storage_File_Abstract {

	/**
	 * Режим открытия файлов для записи. Пока пишем в файл с самого начала, т.к.
	 * обертка, реализованная в PHP не реализует режима "a".
	 *
	 * @var string
	 */
	protected $fopen_mode = 'wb';
}
