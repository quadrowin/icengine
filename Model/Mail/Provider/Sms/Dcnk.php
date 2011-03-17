<?php
/**
 * 
 * @desc Отправка sms через dc-nk.ru
 * @author Юрий Шведов
 * @package Ice_Vipgeo
 *
 */
class Mail_Provider_Sms_Dcnk extends Mail_Provider_Abstract
{
	
	/**
	 * @desc Конфиг
	 * @var array|Objective
	 */
	protected $_config = array (
		'nusoap_path'	=> '/sms/nusoap.php',
		'msguser'		=> '',
		'password'		=> '',
		'msg_gate_url'	=> 'http://www.dc-nk.ru/service/msggate/msgservice.php'
	);
	
	/**
	 * @desc SOAP клиент 
	 * @var yakoon_soapclient
	 */
	protected $_client;
	
	/**
	 * (non-PHPdoc)
	 * @see Model::_afterConstruct()
	 */
	public function _afterConstruct ()
	{
		Loader::requireOnce ($this->config ()->nusoap_path, 'includes');
	}

	function DcSMS ()
	{
		$proxyhost = '';
		$proxyport = '';
		$proxyusername = '';
		$proxypassword = '';
		$this->_client = new yakoon_soapclient (
			"http://www.dc-nk.ru/service/msggate/msgservice.php?WSDL",
			false, $proxyhost, $proxyport, $proxyusername, $proxypassword
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see Mail_Provider_Abstract::send()
	 */
	public function send ($addresses, $message, $config)
	//function sendTextMessage($phone, $email, $date, $text )
	{
		$this->logMessage ($message, self::MAIL_STATE_SENDING);
		
		$result = null;
		//echo $date . "<br>";
		
		$params = array (
			'msguser'		=> $this->_config ['msguser'],
			'password'		=> $this->_config ['password'],
			'text'			=> $message,
			'dtsend'		=> 
				!empty ($config ['date']) ? 
				$config ['date'] : 
				date ("d.m.Y H:i"),
			'grpid'			=> '0',
			'abid'			=> '0',
			'phone'			=> '',
			'email'			=> '',
			'istranslit'	=> true, 
			'isdelivery'	=> true
		);
		
		foreach ($addresses as $address)
		{
			$params ['email'] = 
				isset ($address ['email']) ? $address ['email'] : '';
			
			$params ['phone'] = 
				isset ($address ['phone']) ? $address ['phone'] : '';
			
			$result = $this->_client->call (
				'SendTextMessage',
				$params,
				$this->_config ['msg_gate_url'],
				'SendTextMessage'
			);
			$err = $this->_client->getError ();
		}
		
		$this->logMessage (
			$message,
			self::MAIL_STATE_SENDING,
			var_export ($result, true)
		);

		return (bool) $result;
	}
	
	/**
	 * @desc Получает и возвращает состояние сообщения
	 * @param string $message_id Id сообщения.
	 * @return string Состояние
	 */
	function getStatus ($message_id)
	{
		$params = array
		(
			'msguser'		=> $this->_config ['msguser'],
			'password'		=> $this->_config ['password'],
			'messageid'		=> $message_id
		);
		//echo 'sending data';
		$result = $this->_client->call (
			'GetMessageState',
			$params,
			$this->_config ['msg_gate_url'],
			'GetMessageState'
		);
		return $result;
	}
	
}
