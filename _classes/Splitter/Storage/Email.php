<?php

require_once 'mail/htmlMimeMail.php';

require_once 'mail/RFC822.php';

/**
 * @package	 Splitter
 * @subpackage  storage
 * @version	 $Id$
 */
/**
 * Класс отправки скачанного файла по электронной почте.
 *
 * @package	 Splitter
 * @subpackage  storage
 * @see		 Splitter_Storage_File
 */
class Splitter_Storage_Email extends Splitter_Storage_Ram
{
	/**
	 * Mime-type файла-вложения. Поскольку файлы скачиваются не только по HTTP,
	 * то не всегда можно определить тип первоисточника. Да и не особенно вроде
	 * надо. Поэтому оставим пока в общем случае.
	 *
	 * @var	 string
	 */
	var $ATTACHMENT_TYPE = 'application/octet-stream';

	/**
	 * Адрес, на который нужно отправить сообщение.
	 *
	 * @var	 string
	 */
	var $_to;

	/**
	 * Имя вложения, которым будут отправлены данные.
	 *
	 * @var	 string
	 */
	var $_attachmentName;

	/**
	 * Означает, что при закрытии была выполнена попытка отправить файл.
	 * Используется как мера безопасности для избежания двойной отправки
	 * сообщения в случае, если клиент выполнит close() более одного раза для
	 * одного файла.
	 *
	 * @var	 boolean
	 */
	var $_sent = false;

	/**
	 * Конструктор.
	 *
	 * @return  Splitter_Storage_Email
	 */
	function Splitter_Storage_Email($target)
	{
		// интерпретируем цель как адрес, на который нужно отправить письмо
		$this->_to = $target;

		parent::Splitter_Storage_Ram($target);
	}

	/**
	 * Возвращает имя файла, в котором будут сохранены данные.
	 *
	 * @return  string
	 */
	function getFileName()
	{
		return $this->_attachmentName;
	}

	/**
	 * Устанавливает имя файла, в котором будут сохранены данные.
	 *
	 * @return  string
	 */
	function setFileName($fileName)
	{
		$this->_attachmentName = $fileName;
	}

	/**
	 * Открывает хранилище.
	 *
	 * @param   integer $size
	 * @return  boolean
	 */
	function open($size)
	{
		// сбрасываем флаг попытки отправки сообщения
		$this->_sent = false;

		return parent::open($size) && $this->_validateEmail();
	}

	/**
	 * Завершает сохранение данных. Инициирует отправку почтового сообщения.
	 *
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
	 * @return  mixed
	 */
	function _getSucessMessage()
	{
		return 'Файл "' . $this->_attachmentName . '" успешно отправлен на <a href="mailto:'
			. $this->_to . '" target="_blank">' . $this->_to . '</a>';
	}

	/**
	 * Проверяет правильность адреса для отправки файла.
	 *
	 * @return  boolean
	 */
	function _validateEmail()
	{
		$result = false;

		switch (true)
		{
			case empty($this->_to):
				trigger_error('Не указан адрес email для отправки', E_USER_WARNING);
				break;

			case false === Mail_RFC822::isValidInetAddress($this->_to):
				trigger_error('Неверный адрес email', E_USER_WARNING);
				break;

			default:
				$result = true;
				break;
		}

		return $result;
	}

	/**
	 * Отправляет скачанные данные по e-mail.
	 *
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
		$mail =& new htmlMimeMail();

		$mail->setHeadCharset('utf-8');
		$mail->setTextCharset('utf-8');

		// добавляем текстовую часть
		$mail->setSubject($this->_getSubject());

		// добавляем текстовую часть
		$mail->setText($this->_getText());

		// присоединяем файл
		$mail->addAttachment($this->getContents(), $this->_attachmentName, $this->ATTACHMENT_TYPE);

		// подписываемся
		$mail->setFrom($this->_getFrom());

		// класс htmlMimeMail напрямую использует имя сервера, поэтому при
		// запуске в режиме командной строки приходится ему "подыгрывать"
		if (!isset($_SERVER['SERVER_NAME'])) {
			$_SERVER['SERVER_NAME'] = 'splitter';
		}

		// отправляем письмо по указанному адресу
		return $mail->send(array($this->_to));
	}

	/**
	 * Возвращает тему сообщения.
	 *
	 * @return  string
	 */
	function _getSubject()
	{
		return sprintf('Файл "%s"', $this->_attachmentName);
	}

	/**
	 * Возвращает e-mail отправителя сообщения.
	 *
	 * @return  string
	 */
	function _getFrom()
	{
		return 'Splitter <splitter@forspammersonly.org>';
	}

	/**
	 * Возвращает текст сообщения.
	 *
	 * @return  string
	 */
	function _getText()
	{
		return 'Здравствуйте, Вам письмо от Сплиттера.';
	}
}
