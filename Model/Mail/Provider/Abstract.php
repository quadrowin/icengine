<?php
/**
 * 
 * @desc Провайдер для отправки сообщений пользователя.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Mail_Provider_Abstract extends Model_Factory_Delegate
{
    
    /**
	 * @desc Отправка сообщений.
	 * @param array $mails Адреса.
	 * @param string $message Сообщение.
	 * @param array $config Настройки.
	 * @return boolean
	 */
	public function send ($addresses, $message, $config)
	{
		return false;
	}
	
}