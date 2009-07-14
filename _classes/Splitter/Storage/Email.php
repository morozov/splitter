<?php

/**
 * Класс отправки скачанного файла по электронной почте.
 *
 * @version $Id$
 */
class Splitter_Storage_Email extends Splitter_Storage_Ram {

	/**
	 * Набор символов почтового сообщения.
	 */
	const CHARSET = 'utf-8';

	/**
	 * Опции хранилища.
	 *
	 * @var array
	 */
	protected $options = array(
		'from' => 'Splitter <splitter@forspammersonly.org>',
		'to' => null,
		'subject' => 'Файл "%filename%"',
		'text' => 'Здравствуйте, Вам письмо от Сплиттера.',
	);

	/**
	 * Конструктор.
	 *
	 * @param array $options
	 */
	public function __construct(array $options = array()) {
		if (!isset($options['to'])) {
			throw new Splitter_Storage_Exception('E-mail recepient is required to be set');
		}
		parent::__construct($options);
	}

	/**
	 * Устанавливает адресата почтового сообщения.
	 *
	 * @param string $to
	 */
	protected function setTo($to) {
		$validator = new Zend_Validate_EmailAddress();
		if (!$validator->isValid($to)) {
			$messages = $validator->getMessages();
			throw new Splitter_Storage_Exception(current($messages));
		}
		$this->options['to'] = $to;
	}

	/**
	 * Фиксирует данные в хранилище.
	 *
	 * @throws Splitter_Storage_Exception
	 */
	public function commit() {
		$mail = new Zend_Mail(self::CHARSET);
		$mail->setFrom($this->getOptionValue('from'))
			->addTo($this->options['to'])
			->setSubject(
				// :KLUDGE: morozov 14072009: вручную кодируем заголовок, т.к.
				// функция mail() на Валодькиной FreeBSD валится с Fatal error в
				// случае, если тема письма содержит переводы строк (указываем
				// очень большую макс. длину строки)
				Zend_Mime::encodeBase64Header(
					$this->getOptionValue('subject'), self::CHARSET, 1024)
			)->setBodyText($this->getOptionValue('text'));
		$attachment = $mail->createAttachment($this->getContents());
		$attachment->filename = Zend_Mime::encodeBase64Header($this->filename, self::CHARSET);
		try {
			$mail->send();
		} catch (Zend_Mail_Transport_Exception $e) {
			throw new Splitter_Storage_Exception($e->getMessage());
		}
	}

	/**
	 * Возвращает значение указанной опции с подстановкой параметров.
	 *
	 * @param string $option
	 * @return mixed
	 */
	protected function getOptionValue($name) {
		if (!array_key_exists($name, $this->options)) {
			throw new Splitter_Storage_Exception('Option "' . $option . '" doesn’t exist');
		}
		return str_replace('%filename%', $this->filename, $this->options[$name]);
	}
}
