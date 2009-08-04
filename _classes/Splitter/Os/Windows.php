<?php

class Splitter_Os_Windows {

	public static function getACPCharset() {
		return self::getCharset('ACP');
	}

	public static function getOEMCPCharset() {
		return self::getCharset('OEMCP');
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
