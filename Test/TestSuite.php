<?php

require_once 'PHPUnit\Framework\TestSuite.php';

/**
 * Static test suite.
 */
class TestSuite extends PHPUnit_Framework_TestSuite {
	
	/**
	 * Constructs the test suite handler.
	 */
	public function __construct() {
		$this->setName ( 'TestSuite' );
		
		$this->addTestSuite ( 'Data_Provider_MongoTest' );
	
	}
	
	/**
	 * Creates the suite.
	 */
	public static function suite() {
		return new self ();
	}
}

