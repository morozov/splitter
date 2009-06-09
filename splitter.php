<?php

error_reporting(E_ALL);

setlocale(LC_ALL, 'en_US.utf8');

set_time_limit(0);

ini_set('include_path', implode(PATH_SEPARATOR, array(
	'_classes', '_lib', get_include_path()
)));

require_once 'System/Loader.php';

function __autoload($class) {
	return System_Loader::loadClass($class);
}

$controller = new Splitter_Controller();
exit($controller->process() ? 0 : 1);
