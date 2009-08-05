<?php

class Splitter_Os_Windows {

	public static function getACPCharset() {
		static $charset = false;
		if (false === $charset) {
			$charset =  self::getCharset('ACP');
		}
		return $charset;
	}

	public static function getOEMCPCharset() {
		static $charset = false;
		if (false === $charset) {
			$charset =  self::getCharset('OEMCP');
		}
		return $charset;
	}

	/**
	 * Перекодирует строку из предположительно utf-8 в набор символов ACP для
	 * Windows.
	 *
	 * @param string $string
	 * @return string
	 */
	public static function toACPCharset($string) {
		if (Application::isWindows()) {
			$acp_charset = self::getACPCharset();
			$src_charset = 'utf-8';
			if (mb_check_encoding($string, $src_charset)) {
				$string = mb_convert_encoding($string, $acp_charset, $src_charset);
			}
		}
		return $string;
	}

	private static function getCharset($type) {
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
}
