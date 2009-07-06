<?php

/**
 * Класс отправки скачанного файла по электронной почте.
 *
 * @version $Id$
 */
class Splitter_Storage_Email extends Splitter_Storage_Ram {

	/**
	 * Адрес, на который нужно отправить сообщение.
	 *
	 * @var string
	 */
	protected $to;

	/**
	 * Конструктор.
	 *
	 * @param string $to
	 */
	public function __construct($to) {
		$validator = new Zend_Validate_EmailAddress();
		if (!$validator->isValid($to)) {
			$messages = $validator->getMessages();
			throw new Splitter_Storage_Exception(current($messages));
		}
		$this->to = $to;
	}

	/**
	 * Фиксирует данные в хранилище.
	 *
	 * @throws Splitter_Storage_Exception
	 */
	public function commit() {
		$mail = new Zend_Mail('utf-8');
		$mail->setFrom($this->_getFrom())
			->addTo($this->to)
			->setSubject($this->_getSubject())
			->setBodyText($this->_getText());
		$attachment = $mail->createAttachment($this->getContents());
		$attachment->filename = $this->filename;
		try {
			$mail->send();
		} catch (Zend_Mail_Transport_Exception $e) {
			throw new Splitter_Storage_Exception($e->getMessage());
		}
	}

	/**
	 * Возвращает тему сообщения.
	 *
	 * @return string
	 */
	protected function _getSubject() {
		return sprintf('Файл "%s"', $this->filename);
	}

	/**
	 * Возвращает e-mail отправителя сообщения.
	 *
	 * @return string
	 */
	protected function _getFrom() {
		return 'Splitter <splitter@forspammersonly.org>';
	}

	/**
	 * Возвращает текст сообщения.
	 *
	 * @return string
	 */
	protected function _getText() {
		return 'Здравствуйте, Вам письмо от Сплиттера.';
	}
}
