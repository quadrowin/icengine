<?php
/**
 * 
 * @desc Провайдер для отправки сообщений по почте.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */

if (!class_exists ('Mail_Provider_Abstract'))
{
    include dirname (__FILE__) . '/Abstract.php';
}

class Mail_Provider_Mimemail extends Mail_Provider_Abstract
{
	/**
	 * @desc Путь до PHPMailer
	 * @var string
	 */
	const MIME_MAIL_PATH 	 = 'PHPMailer/class.phpmailer.php';
	
	/**
	 * 
	 * Enter description here ...
	 * @var PHPMailer
	 */
	protected $_mailer;
	
	/**
	 * @return PHPMailer
	 */
	protected function _mailer ()
	{
		if (!$this->_mailer)
		{
			$this->_mailer = new PHPMailer ();
		}
		return $this->_mailer;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Mail_Provider_Abstract::send()
	 */
	public function send (Mail_Message $message, $config)
	{
		$to_name = $message->toName ? $message->toName : 0;
		
		$this->logMessage ($message, self::MAIL_STATE_SENDING);
		
		$result = $this->sendEx (
			array (
				$to_name	=> $message->toEmail
			),
			$message->subject,
			$message->body,
			$config
		);
		
		if ($result)
		{
			$this->logMessage (
				$message,
				self::MAIL_STATE_SUCCESS
			);
		}
		else
		{
			$this->logMessage (
				$message,
				self::MAIL_STATE_FAIL,
				$this->_mailer->ErrorInfo
			);
		}
		
		return $result;
	}
	
	/**
	 * @desc Отправка сообщения на емейл
	 * @param array|string $addresses
	 * @param string $subject
	 * @param string $body
	 * @param array $config
	 */
	public function sendEx ($addresses, $subject, $body, $config)
	{
		Loader::requireOnce (self::MIME_MAIL_PATH, 'includes');
		
		$mail = $this->_mailer ();
		
		foreach ((array) $addresses as $to_name => $address)
		{
			$mail->addAddress (
				$address,
				is_numeric ($to_name) ? '' : $to_name
			);
		}
		
		$mail->From =
			!empty ($config ['From']['email']) ? 
				$config ['From']['email'] : 
				'';
				
		$mail->FromName =
			!empty ($config ['From']['name']) ? 
				$config ['From']['name'] : 
				'';
				
		$mail->IsHTML (true);
		$mail->Subject = $subject;
		$mail->Body = $body;
		
		return $mail->send ();
	}
	
}