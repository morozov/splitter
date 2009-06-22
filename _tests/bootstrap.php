<?php

$dir = dirname(__FILE__);

set_include_path(get_include_path()
	. PATH_SEPARATOR . $dir . '/../_lib'
	. PATH_SEPARATOR . $dir . '/../_classes'
);

require_once 'Zend/Loader.php';

Zend_Loader::registerAutoload();
