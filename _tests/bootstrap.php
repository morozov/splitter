<?php

set_include_path(get_include_path()
	. PATH_SEPARATOR . '../_lib'
	. PATH_SEPARATOR . '../_classes'
);

require_once 'Zend/Loader.php';

Zend_Loader::registerAutoload();
