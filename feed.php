<?php

if ($_SERVER['argc'] != 3) {
	die("Usage: {$_SERVER['argv'][0]} [uri] [email]");
}

list(, $url, $email) = $_SERVER['argv'];

$split_size = 4194304; //4M
$count = 10;

chdir(dirname(__FILE__));

require_once '_classes/bootstrap.php';

start($url, $email, $count);

/**
 * Запускает процесс.
 *
 */
function start($url, $email, $count) {
	$log = get_log($email);

	$downloaded = get_downloaded($log);

	if (!$resource = fopen($log, 'a')) {
		die('Couldn’t open log file');
	}
	if (!flock($resource, LOCK_EX | LOCK_NB)) {
		exit('Log file is locked');
	}
	foreach (array_slice(array_diff(get_enclosures($url), $downloaded), 0, $count) as $enclosure) {
		if (download($enclosure, $email, $split_size)) {
			// добавляем запись в лог
			fwrite($resource, $enclosure . PHP_EOL);
		}
	}
	flock($resource, LOCK_UN);
	fclose($resource);
}

/**
 * Возвращает массив вложений в ленте по указанному URL.
 *
 * @param string $url
 * @return array
 */
function get_enclosures($url) {
	$tries = 3;
	$try = 0;

	do {
		try {
			$feed = Zend_Feed::import($url);
		} catch (Zend_Feed_Exception $e) {
			echo $e->getMessage();
			return array();
		} catch (Zend_Http_Client_Exception $e) {
			if (++$try < $tries) {
				sleep(10);
			} else {
				return array();
			}
		}
	} while (!isset($feed));

	$i = 0;
	$result = array();

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
 * @param string $log
 * @return array
 */
function get_downloaded($log) {
	return is_file($log) ? array_map('trim', file($log)) : array();
}

/**
 * Возвращает путь к файлу лога.
 *
 * @param string $email
 * @return string
 */
function get_log($email) {
	return '_logs/' . $email . '.log';
}

/**
 * Возвращает, является ли указанный mime-type медиа-типом, который нужно
 * скачивать.
 *
 * @param string $type
 * @return boolean
 */
function is_media($type) {
	return preg_match('/^(audio|video)/', $type);
}

/**
 * Скачивает файл по указанному URL.
 *
 * @param  string   $url
 * @return boolean
 */
function download($url, $email, $split_size) {
	echo 'Downloading ' . $url . '...';
	$success = run(array(
		'url' => $url,
		'to' => $email,
		'split-size' => $split_size,
	));
	echo ($success ? 'Done' : 'Failed') . "\n";
	return $success;
}

function run(array $params) {
	$cmd = 'php -f splitter.php';
	foreach (array_merge(array(
		'storage' => 'email',
	), $params) as $param => $value) {
		$cmd .= ' -' . $param . ' ' . escapeshellarg($value);
	}
	system($cmd, $exit_code);
	return 0 == $exit_code;
}
