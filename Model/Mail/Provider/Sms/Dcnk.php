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
		'nusoap_path'	=> '/sms/nusoap.php'
	);
	
	protected $_client;
	
	/**
	 * (non-PHPdoc)
	 * @see Model::_afterConstruct()
	 */
	public function _afterConstruct ()
	{
		Loader::requireOnce ($this->_config ['nusoap_path'], 'includes');
	}

	function DcSMS ()
	{
		$proxyhost = '';
		$proxyport = '';
		$proxyusername = '';
		$proxypassword = '';
		$this->client = new yakoon_soapclient (
			"http://www.dc-nk.ru/service/msggate/msgservice.php?WSDL",
			false, $proxyhost, $proxyport, $proxyusername, $proxypassword
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see Mail_Provider_Abstract::send()
	 */
	public function send ($addresses, $message, $config)
	//function ыendTextMessage($phone, $email, $date, $text )
	{
		$result = null;
		$date = empty ($config ['date']) ? date ("d.m.Y H:i") : $config ['date'];
		//echo $date . "<br>";
		
		$params = array (
			'msguser'		=> 'forguest',
			'password'		=> '123456',
			'text'			=> $message,
			'dtsend'		=> $date,
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
			
			$result = $this->client->call (
				'SendTextMessage', $params, 
				'http://www.dc-nk.ru/service/msggate/msgservice.php', 
				'SendTextMessage'
			);
			$err = $this->client->getError ();
		}

		return $result;
	}

	function getStatus ($messageID)
	{
		$params = array
		(
			'msguser' => 'forguest',
			'password' => '123456',
			'messageid'  => $messageID
		);
		//echo 'sending data';
		$result = $this->client->call('GetMessageState', $params, 'http://www.dc-nk.ru/service/msggate/msgservice.php', 'GetMessageState');
		return $result;
	}
	
}
