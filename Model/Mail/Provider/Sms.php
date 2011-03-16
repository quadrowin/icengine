<?php
/**
 * 
 * @desc Провайдер СМС сообщений.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */

if (!class_exists ('Mail_Provider_Abstract'))
{
    include dirname (__FILE__) . '/Abstract.php';
}

class Mail_Provider_Sms extends Mail_Provider_Abstract
{
	
	/**
	 * @desc Конфиг
	 * @var array
	 */
	public $config = array (
		
	);
	
	/**
	 * (non-PHPdoc)
	 * @see Mail_Provider_Sms::send($addresses, $message, $config)
	 */
	public function send ($addresses, $message, $config)
	{
		
	}
	
}