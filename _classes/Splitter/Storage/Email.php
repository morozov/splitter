<?php

/**
 * @package	 Splitter
 * @subpackage  storage
 * @version	 $Id$
 */
/**
 * Класс отправки скачанного файла по электронной почте.
 *
 * @access	  public
 * @package	 Splitter
 * @subpackage  storage
 * @see		 Splitter_Storage_File
 */
class Splitter_Storage_Email extends Splitter_Storage_Ram {

	/**
	 * Адрес, на который нужно отправить сообщение.
	 *
	 * @access  private
	 * @var	 string
	 */
	var $_to;

	/**
	 * Означает, что при закрытии была выполнена попытка отправить файл.
	 * Используется как мера безопасности для избежания двойной отправки
	 * сообщения в случае, если клиент выполнит close() более одного раза для
	 * одного файла.
	 *
	 * @access  private
	 * @var	 boolean
	 */
	var $_sent = false;

	/**
	 * Конструктор.
	 *
	 * @access  public
	 * @return  Splitter_Storage_Email
	 */
	public function __construct($target) {

		if (empty($target)) {
			throw new Splitter_Storage_Exception('Не указан адрес email для отправки');
		}

		$validator = new Zend_Validate_EmailAddress();
		if (!$validator->isValid($target)) {
			$messages = $validator->getMessages();
			throw new Splitter_Storage_Exception(current($messages));
		}

		// интерпретируем цель как адрес, на который нужно отправить письмо
		$this->_to = $target;

		parent::__construct();
	}

	/**
	 * Открывает хранилище.
	 *
	 * @access  public
	 * @param   integer $size
	 * @return  boolean
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
	 * @access  protected
	 * @return  boolean
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
	 * @access  protected
	 * @return  mixed
	 */
	function _getSucessMessage()
	{
		return 'Файл "' . $this->filename . '" успешно отправлен на <a href="mailto:'
			. $this->_to . '" target="_blank">' . $this->_to . '</a>';
	}

	/**
	 * Отправляет скачанные данные по e-mail.
	 *
	 * @access  private
	 * @return  boolean
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
			->addTo($this->_to)
			->setSubject($this->_getSubject())
			->setBodyText($this->_getText());
		$at = $mail->createAttachment($this->getContents());
		$at->filename = $this->filename;
		return $mail->send();
	}

	/**
	 * Возвращает тему сообщения.
	 *
	 * @access  private
	 * @return  string
	 */
	function _getSubject()
	{
		return sprintf('Файл "%s"', $this->filename);
	}

	/**
	 * Возвращает e-mail отправителя сообщения.
	 *
	 * @access  private
	 * @return  string
	 */
	function _getFrom()
	{
		return 'Splitter <splitter@forspammersonly.org>';
	}

	/**
	 * Возвращает текст сообщения.
	 *
	 * @access  private
	 * @return  string
	 */
	function _getText()
	{
		return 'Здравствуйте, Вам письмо от Сплиттера.';
	}
}
