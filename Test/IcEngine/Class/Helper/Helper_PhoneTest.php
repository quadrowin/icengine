<?php

require_once dirname (__FILE__) . '/../../../../Class/Helper/Phone.php';

/**
 * Test class for Helper_Phone.
 * Generated by PHPUnit on 2011-07-06 at 03:41:45.
 */
class Helper_PhoneTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @var Helper_Phone
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp ()
	{
		$this->object = new Helper_Phone;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown ()
	{
		
	}

	/**
	 * @todo Implement testFormatMobile().
	 */
	public function testFormatMobile ()
	{
		$phones = array (
			'79134236328',
			'71231231232',
			'71231231232',
			'12312432442'
		);
		
		foreach ($phones as $phone)
		{
			$formated = Helper_Phone::formatMobile ($phone);
			$this->assertEquals (
				$phone,
				Helper_Phone::parseMobile ($formated)
			);
		}
	}

	/**
	 * @todo Implement testParseMobile().
	 */
	public function testParseMobile ()
	{
		$phones = array (
			''					=> false,
			'79134236328'		=> true,
			'+79134236328'		=> true,
			'84324234234'		=> true,
			'8 812 342 62 12'	=> true,
			'+7 9123 123-543'	=> true,
			'+7 8o0 000 000'	=> false
		);
		
		foreach ($phones as $phone => $result)
		{
			$mobile = Helper_Phone::parseMobile ($phone);
			$this->assertEquals ($result, (bool) $mobile);
		}
	}

}

?>
