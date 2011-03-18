<?php

require_once 'PHPUnit\Framework\TestCase.php';

/**
 * test case.
 */
class Test_Mail_Provider_Sms extends PHPUnit_Framework_TestCase
{

	/**
	 * @desc Провайдер сообщений
	 * @var Mail_Provider_Sms_Littlesms
	 */
	protected $_provider;
	
	/**
	 * @desc Номер
	 * @var array
	 */
	protected $_numbers = array ();
	
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp ()
	{
		parent::setUp ();
		
		$this->_numbers = array (
			'+79134236328',
			'+79133271039',
			'ahaha',
			''
		);
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown ()
	{
		// TODO Auto-generated Mail_Provider_Sms::tearDown()
		parent::tearDown ();
	}

	/**
	 * Constructs the test case.
	 */
	public function __construct ()
	{
		if (!class_exists ('IcEngine'))
		{
			date_default_timezone_set ('UTC');
			
			require dirname (__FILE__) . '/../../../../IcEngine.php';
			IcEngine::init ();
			Loader::load ('Loader_Auto');
			Loader_Auto::register ();
			
			Loader::addPath ('includes', IcEngine::root() . 'includes/');
			
			IcEngine::initApplication (
				'Icengine',
				IcEngine::path () . 'Class/Application/Behavior/Icengine.php'
			);
			IcEngine::run ();
			
			$this->_provider = IcEngine::$modelManager->get ('Mail_Provider', 1);
		}
	}
	
	/**
	 * @desc Тестирование отправки СМС сообщения.
	 */
	public function testSendSms ()
	{
		return;
		
		$result = $this->_provider->sendSms (
			'+79134236328',
			'sms test'
		);
		$this->assertNotNull ($result);
		return $result;
	}
	
	/**
	 * @desc Отправка сообщения
	 */
	public function testSend ()
	{
		return; 
		
		$message = new Mail_Message (array (
			'id'					=> 0,
			'Mail_Template__id'		=> 0,
			'toEmail'				=> $this->_number,
			'toName'				=> 'you',
			'subject'				=> '',
			'body'					=> Helper_Date::toUnix (),
			'time'					=> Helper_Date::toUnix (),
			'sended'				=> 0,
			'sendTime'				=> Helper_Date::toUnix (),
			'sendDay'				=> Helper_Date::eraDayNum (),
			'sendTries'				=> 0,
			'toUserId'				=> 0,
			'Mail_Provider__id'		=> 2,
			'params'				=> ''
		));
		
		$config = array ();
		
		$this->_provider = $message->Mail_Provider;
		
		$r = $this->_provider->send ($message, $config);
		
		$this->assertNotEquals (false, $r, 'Not sended.');
		
		return $this->_provider;
	}
	
	/**
	 * @desc Тест отправки нескольких сообщений
	 */
	function testSendMulti ()
	{
		foreach ($this->_numbers as $number)
		{
			$message = new Mail_Message (array (
				'id'					=> 0,
				'Mail_Template__id'		=> 0,
				'toEmail'				=> $number,
				'toName'				=> 'you',
				'subject'				=> '',
				'body'					=> Helper_Date::toUnix (),
				'time'					=> Helper_Date::toUnix (),
				'sended'				=> 0,
				'sendTime'				=> Helper_Date::toUnix (),
				'sendDay'				=> Helper_Date::eraDayNum (),
				'sendTries'				=> 0,
				'toUserId'				=> 0,
				'Mail_Provider__id'		=> 2,
				'params'				=> ''
			));
			
			$config = array ();
			
			$this->_provider = $message->Mail_Provider;
			
			$r = $this->_provider->send ($message, $config);
			
			$this->assertNotEquals (false, $r, 'Not sended.');
		}
	}
	
}
