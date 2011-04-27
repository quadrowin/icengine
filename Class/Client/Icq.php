<?php

Loader::load ('Client_Abstract');

/**
 * 
 * @desc Клиент для Icq
 * @author Илья
 * @package IcEngine
 */
class Client_Icq extends Client_Abstract
{
	/**
	 * 
	 * @desc Экзмепляр класса ICQClient
	 * @var ICQClient
	 */
	private $_instance;
	
	/**
	 * 
	 * Если класс не проинициализирован, то проинициализивать
	 * @param null|Config_Array $config
	 * Конфиг для подключения к Icq
	 * @throws Zend_Exception
	 */
	public function __construct ($config = null)
	{
		Loader::requireOnce ('ICQclient.php', 'includes');
		if (!class_exists ('WebIcqPro'))
		{
			Loader::load ('Zend_Exception');
			throw new Zend_Exception ('Class "ICQClient" not exists');
		}
		if (!($config instanceof Config_Array))
		{
			$config = Config_Manager::load (
				'Client',
				$this->name ()
			);
		}
		if (!$config)
		{
			Loader::load ('Zend_Exception');
			throw new Zend_Exception ('Empty config');
		}
		if (!isset ($config->login) || !isset ($config->password))
		{
			Loader::load ('Zend_Exception');
			throw new Zend_Exception ('Unexists login or password');
		}
		$this->_provider = new WebIcqPro (
			$config->login, 
			$config->password
		);
		$this->connect ($config);
	}
	
	/**
	 * 
	 * @desc Законектится
	 * @param Config_Array $config
	 * @return boolean
	 */
	public function connect ($config)
	{
		return $this->_provider
			->connect (
				$config->login,
				$config->password
			);
	}
	
	/**
	 * 
	 * @desc Был ли коннект
	 * @return boolean
	 */
	public function connected ()
	{
		return $this->_provider->isConnected ();
	}
	
	/**
	 * 
	 * @desc Получить провайдера icq
	 * @return ICQClient
	 */
	public function provider ()
	{
		return $this->_provider;
	}
	
	
	/**
	 * 
	 * @desc Отравить сообщение
	 * @param Client_Icq_Reciever $reciever
	 * @param string $message
	 * @return boolean
	 */
	public function send (Client_Icq_Reciever $reciever, $message)
	{
		return $this->_provider
			->sendMessage (
				$reciever->icq,
				$message
			);
	}
}