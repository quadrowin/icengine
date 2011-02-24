<?php

require_once 'PHPUnit\Framework\TestCase.php';

/**
 * Data_Provider_Mongo test case.
 */
class Data_Provider_MongoTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * @var Data_Provider_Mongo
	 */
	private $Data_Provider_Mongo;
	
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		parent::setUp ();
		
		// TODO Auto-generated Data_Provider_MongoTest::setUp()
		
		include 'D:\Work\htdocs\vipgeo\config\mongo.php';
		include 'D:\Work\htdocs\vipgeo\IcEngine\Class\Data\Provider\Mongo.php';
		
		$this->Data_Provider_Mongo = new Data_Provider_Mongo($config);
	
	}
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		// TODO Auto-generated Data_Provider_MongoTest::tearDown()
		

		$this->Data_Provider_Mongo = null;
		
		parent::tearDown ();
	}
	
	/**
	 * Constructs the test case.
	 */
	public function __construct() {
		// TODO Auto-generated constructor
	}
	
	/**
	 * Tests Data_Provider_Mongo->__construct()
	 */
	public function test__construct() {
		// TODO Auto-generated Data_Provider_MongoTest->test__construct()
		$this->markTestIncomplete ( "__construct test not implemented" );
		
		include 'D:\Work\htdocs\vipgeo\config\mongo.php';
		$this->Data_Provider_Mongo->__construct($config);
	
	}
	
	/**
	 * Tests Data_Provider_Mongo->__destruct()
	 */
	public function test__destruct() {
		// TODO Auto-generated Data_Provider_MongoTest->test__destruct()
		$this->markTestIncomplete ( "__destruct test not implemented" );
		
		$this->Data_Provider_Mongo->__destruct(/* parameters */);
	
	}
	
	/**
	 * Tests Data_Provider_Mongo->add()
	 */
	public function testAdd() {
		// TODO Auto-generated Data_Provider_MongoTest->testAdd()
		$this->markTestIncomplete ( "add test not implemented" );
		
		$this->Data_Provider_Mongo->add(/* parameters */);
	
	}
	
	/**
	 * Tests Data_Provider_Mongo->flush()
	 */
	public function testFlush() {
		// TODO Auto-generated Data_Provider_MongoTest->testFlush()
		$this->markTestIncomplete ( "flush test not implemented" );
		
		$this->Data_Provider_Mongo->flush(/* parameters */);
	
	}
	
	/**
	 * Tests Data_Provider_Mongo->get()
	 */
	public function testGet() {
		// TODO Auto-generated Data_Provider_MongoTest->testGet()
		$this->markTestIncomplete ( "get test not implemented" );
		
		$this->Data_Provider_Mongo->get(/* parameters */);
	
	}
	
	/**
	 * Tests Data_Provider_Mongo->getMulti()
	 */
	public function testGetMulti() {
		// TODO Auto-generated Data_Provider_MongoTest->testGetMulti()
		$this->markTestIncomplete ( "getMulti test not implemented" );
		
		$this->Data_Provider_Mongo->getMulti(/* parameters */);
	
	}
	
	/**
	 * Tests Data_Provider_Mongo->keys()
	 */
	public function testKeys() {
		// TODO Auto-generated Data_Provider_MongoTest->testKeys()
		$this->markTestIncomplete ( "keys test not implemented" );
		
		$this->Data_Provider_Mongo->keys(/* parameters */);
	
	}
	
	/**
	 * Tests Data_Provider_Mongo->set()
	 */
	public function testSet() {
		// TODO Auto-generated Data_Provider_MongoTest->testSet()
		$this->markTestIncomplete ( "set test not implemented" );
		
		$this->Data_Provider_Mongo->set(/* parameters */);
	
	}

}

