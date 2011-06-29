<?php
require_once 'IcEngine\Class\Registry.php';
require_once 'PHPUnit\Framework\TestCase.php';

/**
 * Registry test case.
 */
class Test_Registry extends PHPUnit_Framework_TestCase
{

	/**
	 * @var Registry
	 */
	private $Registry;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp ()
	{
		parent::setUp ();
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown ()
	{
		// TODO Auto-generated Test_Registry::tearDown()
		$this->Registry = null;
		parent::tearDown ();
	}

	/**
	 * Constructs the test case.
	 */
	public function __construct ()
	{
		// TODO Auto-generated constructor
	}

	/**
	 * Tests Registry::defined()
	 */
	public function testDefined ()
	{
		// TODO Auto-generated Test_Registry::testDefined()
		$this->markTestIncomplete ("defined test not implemented");
		Registry::defined(/* parameters */);
	}

	/**
	 * Tests Registry::get()
	 */
	public function testGet ()
	{
		// TODO Auto-generated Test_Registry::testGet()
		$this->markTestIncomplete ("get test not implemented");
		Registry::get(/* parameters */);
	}

	/**
	 * Tests Registry::set()
	 */
	public function testSet ()
	{
		// TODO Auto-generated Test_Registry::testSet()
		$this->markTestIncomplete ("set test not implemented");
		Registry::set(/* parameters */);
	}

	/**
	 * Tests Registry::sget()
	 */
	public function testSget ()
	{
		// TODO Auto-generated Test_Registry::testSget()
		$this->markTestIncomplete ("sget test not implemented");
		Registry::sget(/* parameters */);
	}

	/**
	 * Tests Registry::rget()
	 */
	public function testRget ()
	{
		// TODO Auto-generated Test_Registry::testRget()
		$this->markTestIncomplete ("rget test not implemented");
		Registry::rget(/* parameters */);
	}

	/**
	 * Tests Registry::rset()
	 */
	public function testRset ()
	{
		// TODO Auto-generated Test_Registry::testRset()
		$this->markTestIncomplete ("rset test not implemented");
		Registry::rset(/* parameters */);
	}
}

