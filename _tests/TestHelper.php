<?php

error_reporting(E_ALL | E_STRICT);

$root = dirname(dirname(__FILE__));

set_include_path(get_include_path()
	. PATH_SEPARATOR . $root . DIRECTORY_SEPARATOR . '_lib'
	. PATH_SEPARATOR . $root . DIRECTORY_SEPARATOR . '_classes'
	. PATH_SEPARATOR . $root . DIRECTORY_SEPARATOR . '_tests'
);

require_once 'Zend/Loader.php';

Zend_Loader::registerAutoload();
