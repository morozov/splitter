<?php

require_once '_classes/bootstrap.php';

$controller = new Splitter_Controller();
exit($controller->main() ? 0 : 1);
