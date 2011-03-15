<?php

if (!class_exists ('Mail_Provider_Abstract'))
{
    include dirname (__FILE__) . '/Abstract.php';
}

class Mail_Provider_Mimemail extends Mail_Provider_Abstract
{
	/**
	 * 
	 * @var string
	 */
	const MIME_MAIL_PATH 	 = 'PHPMailer/class.phpmailer.php';
	
	public function send ($mails, $message, $config)
	{
		Loader::requireOnce (self::MIME_MAIL_PATH, 'includes');
		
		if (class_exists ('PHPMailer'))
		{
			$mail = new PHPMailer ();
			
			foreach ((array) $mails as $email)
			{
				$mail->addAddress ($email, '');
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