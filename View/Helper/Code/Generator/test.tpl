<?php

/**
 * Test class for {$name}.
 * Generated by PHPUnit on {$date}.
 * @author {$author}
 */
class {$name}Test extends {if $with_selenium}PHPUnit_Extensions_Selenium2TestCase{else}PHPUnit_Framework_TestCase{/if}

{
	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp()
	{
		{if $with_selenium}$this->setBrowser ('{$browser}');
		$this->setBrowserUrl ('http://' . Request::uri ());{/if}

	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{

	}

	{foreach from=$methods item="method"}
/**
	 * @todo Implement test{$method}()
	 */
	public function test{$method} ()
	{
		{if $with_helper}Helper_Test::test ($this, __METHOD__);{/if}

	}

	{/foreach}

}