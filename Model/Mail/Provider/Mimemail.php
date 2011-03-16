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
	 * (non-PHPdoc)
	 * @see Mail_Provider_Abstract::send()
	 */
	public function send ($addresses, $message, $config)
	{
		Loader::requireOnce (self::MIME_MAIL_PATH, 'includes');
		
		if (class_exists ('PHPMailer'))
		{
			$mail = new PHPMailer ();
			
			foreach ((array) $addresses as $address)
			{
				$mail->addAddress ($address, '');
			}
			
			$mail->From =
				!empty ($config ['From']['email'])
					? $config ['From']['email']
					: '';
					
			$mail->FromName =
				!empty ($config ['From']['name'])
					? $config ['From']['name']
					: '';
					
			$mail->IsHTML (true);
			
			$mail->Subject = 
				!empty ($config ['Subject'])
					? $config ['Subject']
					: '';
					
			$mail->Body = $message;
			
			return $mail->send ();
		}
	}
	
}