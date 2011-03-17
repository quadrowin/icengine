<?php
/**
 * 
 * @desc Провайдер сообщений, который отправляет сообщение по списку
 * провайдеров до первой успешной отправки.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Mail_Provider_First_Success extends Mail_Provider_Abstract
{
	
	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected $_config = array (
		// Наборы провайдеров
		'providers_set'	=> array (
			'sms'	=> array ('Sms_Dcnk')
		)
	);
	
	/**
	 * (non-PHPdoc)
	 * @see Mail_Provider_Abstract::send()
	 */
	public function send (Mail_Message $message, $config)
	{
		$this->logMessage ($message, self::MAIL_STATE_SENDING);
		
		$providers = isset ($config ['providers']) ? 
			(array) $config ['providers'] :
			null;
		
		if (!$providers)
		{
			$sets = $this->config ()->providers_set;
			$providers = (array) $sets [$config ['providers_set']];
		}
		
		foreach ($providers as $provider_name)
		{
			/**
			 * @desc Реальный провайдер
			 * @var Mail_Provider_Abstract $provider
			 */
			$provider = IcEngine::$modelManager->modelBy (
				'Mail_Provider',
				Query::instance ()
				->where ('name', $provider_name)
			);
			
			if ($provider && $provider->send ($message, $config))
			{
				$this->logMessage ($message, self::MAIL_STATE_SUCCESS);
				return true;
			}
		}
		
		$this->logMessage ($message, self::MAIL_STATE_FAIL);
		return false;
	}
	
}