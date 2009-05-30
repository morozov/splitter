<?php

/**
 * @package	 Splitter
 * @subpackage  response
 * @version	 $Id$
 */
/**
 * Класс ответа приложения для режима командной строки. Временно подменяем на
 * вариант для веб-интерфейса, чтобы хоть что-то выводил. Если надо будет
 * сделать красиво — сделаем. Однако с учетом того, что приложение запускается в
 * виде демона, и вывод в основном будет использоваться только для отладки,
 * можно предположить, что на этом и остановимся :)
 *
 * @package	 Splitter
 * @subpackage  response
 * @see		 Splitter_Response_Abstract
 */
class Splitter_Response_Cli extends Splitter_Response_Web
{
}
