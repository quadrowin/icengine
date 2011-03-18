<?php

require_once 'PHPUnit\Framework\TestCase.php';

/**
 * Mail_Provider_Mimemail test case.
 */
class Test_Mail_Provider_Mimemail extends PHPUnit_Framework_TestCase
{

	/**
	 * @var Mail_Provider_Mimemail
	 */
	private $Mail_Provider_Mimemail;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp ()
	{
		parent::setUp ();
		// TODO Auto-generated Test_Mail_Provider_Mimemail::setUp()
		$this->Mail_Provider_Mimemail = IcEngine::$modelManager->get (
			'Mail_Provider',
			1
		);
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown ()
	{
		// TODO Auto-generated Test_Mail_Provider_Mimemail::tearDown()
		$this->Mail_Provider_Mimemail = null;
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
			
			require dirname (__FILE__) . '/../../../IcEngine.php';
			IcEngine::init ();
			Loader::load ('Loader_Auto');
			Loader_Auto::register ();
			
			Loader::addPath ('includes', IcEngine::root() . 'includes/');
			
			IcEngine::initApplication (
				'Icengine',
				IcEngine::path () . 'Class/Application/Behavior/Icengine.php'
			);
			IcEngine::run ();
		}
	}

	/**
	 * Tests Mail_Provider_Mimemail->send()
	 */
	public function testSend ()
	{
		$message = new Mail_Message (array (
			'id'					=> 0,
			'Mail_Template__id'		=> 0,
			'toEmail'				=> 'goorus@list.grs',
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
			
		$r = $this->Mail_Provider_Mimemail->send (
			$message,
			array ()
		);
		
		$this->assertNotEquals (false, $r, 'Not sended');
	}

	/**
	 * Tests Mail_Provider_Mimemail->sendEx()
	 */
	public function testSendEx ()
	{
		$r = $this->Mail_Provider_Mimemail->sendEx (
			'goorus@list.ru',
			'subject',
			'body',
			array ()
		);
		
		$this->assertNotEquals (false, $r, 'Not sended');
	}
}

