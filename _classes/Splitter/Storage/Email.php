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
	 * Означает, что при закрытии была выполнена попытка отправить файл.
	 * Используется как мера безопасности для избежания двойной отправки
	 * сообщения в случае, если клиент выполнит close() более одного раза для
	 * одного файла.
	 *
	 * @access private
	 * @var boolean
	 */
	var $_sent = false;

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
	 * Открывает хранилище.
	 *
	 * @access public
	 * @param integer $size
	 * @return boolean
	 */
	function open($size)
	{
		// сбрасываем флаг попытки отправки сообщения
		$this->_sent = false;

		return parent::open($size);
	}

	/**
	 * Завершает сохранение данных. �?нициирует отправку почтового сообщения.
	 *
	 * @access protected
	 * @return boolean
	 */
	function _close()
	{
		$result = false;

		switch (false)
		{
			case parent::_close():
				break;

			case $this->_send():
				trigger_error('Невозможно отправить почтовое сообщение');
				break;

			default:
				$result = true;
				break;
		}

		return $result;
	}

	/**
	 * Возвращает сообщение об успешном сохранении данных.
	 *
	 * @access protected
	 * @return mixed
	 */
	function _getSucessMessage()
	{
		return 'Файл "' . $this->filename . '" успешно отправлен на <a href="mailto:'
			. $this->to . '" target="_blank">' . $this->to . '</a>';
	}

	/**
	 * Отправляет скачанные данные по e-mail.
	 *
	 * @access private
	 * @return boolean
	 */
	function _send()
	{
		// если сообщение с текущим файлом уже было отправлено
		if ($this->_sent)
		{
			return true;
		}

		// для текущего файла эта операция больше не должна быть выполнена
		$this->_sent = true;

		// отключаем ограничение используемой памяти
		ini_set('memory_limit', -1);

		// создаем почтовое сообщение
		$mail = new Zend_Mail('utf-8');
		$mail->setFrom($this->_getFrom())
			->addTo($this->to)
			->setSubject($this->_getSubject())
			->setBodyText($this->_getText());
		$at = $mail->createAttachment($this->getContents());
		$at->filename = $this->filename;
		return $mail->send();
	}

	/**
	 * Возвращает тему сообщения.
	 *
	 * @access private
	 * @return string
	 */
	function _getSubject()
	{
		return sprintf('Файл "%s"', $this->filename);
	}

	/**
	 * Возвращает e-mail отправителя сообщения.
	 *
	 * @access private
	 * @return string
	 */
	function _getFrom()
	{
		return 'Splitter <splitter@forspammersonly.org>';
	}

	/**
	 * Возвращает текст сообщения.
	 *
	 * @access private
	 * @return string
	 */
	function _getText()
	{
		return 'Здравствуйте, Вам письмо от Сплиттера.';
	}
}
