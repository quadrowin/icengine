<?php

require_once dirname (__FILE__) . '/../../../../Class/Helper/Activation.php';

/**
 * Test class for Helper_Activation.
 * Generated by PHPUnit on 2011-07-06 at 03:14:11.
 */
class Helper_ActivationTest extends PHPUnit_Framework_TestCase
{

	/**
	 * @var Helper_Activation
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp ()
	{
		$this->object = new Helper_Activation;
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown ()
	{
		
	}

	/**
	 * @todo Implement testGenerateNumeric().
	 */
	public function testGenerateNumeric ()
	{
		$codes = array ();
		$min_length = 999;
		$max_length = 0;
		
		$start_time = microtime (true);
		for ($i = 0; $i < 100; ++$i)
		{
			$code = Helper_Activation::generateNumeric ();
			$min_length = min ($min_length, strlen ($code));
			$max_length = max ($max_length, strlen ($code));
			$codes [] = $code;
		}
		$delta_time = microtime (true) - $start_time;
		
		var_dump (array (
			'time'			=> $delta_time,
			'min_length'	=> $min_length,
			'max_length'	=> $max_length
		));
		var_dump ($codes);
	}

	/**
	 * @todo Implement testNewShortCode().
	 */
	public function testNewShortCode ()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete (
				'This test has not been implemented yet.'
		);
	}

	/**
	 * @todo Implement testByShortCode().
	 */
	public function testByShortCode ()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete (
				'This test has not been implemented yet.'
		);
	}

}

?>