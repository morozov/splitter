<?php

ini_set('output_buffering', 0);

error_reporting(E_ALL);

setlocale(LC_ALL, 'en_US.utf8');

set_time_limit(0);

ini_set('include_path', implode(PATH_SEPARATOR, array(
	'_classes', '_lib', get_include_path()
)));

require_once 'Zend/Loader.php';

Zend_Loader::registerAutoload();