<?php

$url = 'http://pipes.yahoo.com/pipes/pipe.run?_id=vBQP5PX83RGPa7Vx_w6H4A&_render=rss';
$email = 'log67@tut.by';
$split_size = 4194304; //4M
$count = 10;

chdir(dirname(__FILE__));

set_include_path('_lib');

require_once 'Zend/Loader.php';

Zend_Loader::registerAutoload();

start();

/**
 * Запускает процесс.
 *
 */
function start() {
	global $url, $count;
	foreach (array_slice(array_diff(get_enclosures($url), get_downloaded()), 0, $count) as $enclosure) {
		if (download($enclosure)) {
			// добавляем запись в лог
			file_put_contents(get_log(), $enclosure . PHP_EOL, FILE_APPEND);
		}
	}
}

/**
 * Возвращает массив вложений в ленте по указанному URL.
 *
 * @param string $url
 * @return array
 */
function get_enclosures($url) {
	$result = array();

	try {
		$feed = Zend_Feed::
	} catch (Zend_Feed_Exception $e) {
		echo $e->getMessage();
		return array();
	}

	$i = 0;

	foreach ($feed as $item) {
		foreach ($item->getDOM()->getElementsByTagName('enclosure') as $enclosure) {
			if (is_media($enclosure->getAttribute('type'))) {

				$e_url = $enclosure->getAttribute('url');

				// отрезаем левую QUERY_STRING из адресов на rpod.ru
				if (0 === strpos($e_url, 'http://rpod.ru/')) {
					$e_url = substr($e_url, 0, strpos($e_url, '?'));
				}

				$date = strtotime($item->pubDate());

				$result[sprintf('%010d', $date) . '-' . sprintf('%03d', ++$i)] = $e_url;
			}
		}
	}

	// сортируем записи по дате вручную
	krsort($result);

	return array_values($result);
}

/**
 * Возвращает массив вложений, отправленных на текущий e-mail.
 *
 * @return array
 */
function get_downloaded() {
	$log = get_log();
	return is_file($log) ? array_map('trim', file($log)) : array();
}

/**
 * Возвращает путь к файлу лога.
 *
 * @return string
 */
function get_log() {
	global $email;
	return '_logs/' . $email;
}

/**
 * Возвращает, является ли указанный mime-type медиа-типом, который нужно
 * скачивать.
 *
 * @param string $type
 * @return boolean
 */
function is_media($type) {
	foreach (array('audio', 'video') as $prefix) {
		if (0 === strpos($type, $prefix)) {
			return true;
		}
	}
	return false;
}

/**
 * Скачивает файл по указанному URL.
 *
 * @param  string   $url
 * @return boolean
 */
function download($url) {
	global $email, $split_size;
	echo 'Downloading ' . $url . '...';
	system('php splitter.php -url ' . escapeshellarg($url) . ' -storage email -target-email ' . $email . ' -split-size ' . $split_size, $exit_code);
	$success = 0 == $exit_code;
	echo ($success ? 'Done' : 'Failed') . "\n";
	return $success;
}