<?php

@ob_end_flush();
ini_set('implicit_flush', 1);

error_reporting(E_ALL);

setlocale(LC_ALL, 'en_US.utf8');

set_time_limit(0);

ini_set('include_path', implode(PATH_SEPARATOR, array(
	'_classes', '_lib', get_include_path()
)));

require_once 'Zend/Loader.php';

Zend_Loader::registerAutoload();