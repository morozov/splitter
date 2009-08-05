<?php

class Splitter_Os_Windows {

	public static function getACPCharset() {
		return self::getCharset('ACP');
	}

	public static function getOEMCPCharset() {
		return self::getCharset('OEMCP');
	}

	/**
	 * Перекодирует строку из предположительно utf-8 в набор символов ACP для
	 * Windows.
	 *
	 * @param string $string
	 * @return string
	 */
	public static function toACPCharset($string) {
		return self::toCharset($string, 'ACP');
	}

	/**
	 * Перекодирует строку из предположительно utf-8 в набор символов OEM для
	 * Windows.
	 *
	 * @param string $string
	 * @return string
	 */
	public static function toOEMCPCharset($string) {
		return self::toCharset($string, 'OEMCP');
	}

	/**
	 *
	 * @param string $string
	 * @return string
	 */
	public static function fromACPCharset($string) {
		return self::fromCharset($string, 'ACP');
	}

	/**
	 *
	 * @param string $string
	 * @return string
	 */
	public static function fromOEMCPCharset($string) {
		return self::fromCharset($string, 'OEMCP');
	}

	protected static function getCharset($type) {
		static $cache = array();
		if (!array_key_exists($type, $cache)) {
			$cache[$type] = self::fetchCharset($type);
		}
		return $cache[$type];
	}

	protected static function fetchCharset($type) {
		$shell = new COM('WScript.Shell');

		try {
			$codepage = $shell->regRead('HKEY_LOCAL_MACHINE\\SYSTEM\\CurrentControlSet\\Control\\Nls\\CodePage\\' . $type);
		} catch (com_exception $e) {
			return null;
		}

		foreach (array('WebCharset', 'BodyCharset', 'HeaderCharset') as $param) {
			try {
				return $shell->regRead('HKEY_CLASSES_ROOT\\MIME\\Database\\Codepage\\' . $codepage . '\\' . $param);
			} catch (com_exception $e) { }
		}

		return null;
	}

	protected static function toCharset($string, $type) {
		if (Application::isWindows() && mb_check_encoding($string, 'utf-8')) {
			$string = mb_convert_encoding($string, self::getCharset($type), 'utf-8');
		}
		return $string;
	}

	protected static function fromCharset($string, $type) {
		if (Application::isWindows() && !mb_check_encoding($string, 'utf-8')) {
			$string = mb_convert_encoding($string, 'utf-8', self::getCharset($type));
		}
		return $string;
	}
}
