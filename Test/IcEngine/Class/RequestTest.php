<?php

require_once dirname(__FILE__) . '/../../../Class/Request.php';

/**
 * Test class for Request.
 * Generated by PHPUnit on 2011-07-07 at 07:22:40.
 */
class RequestTest extends PHPUnit_Framework_TestCase {

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
	
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {
		
	}

	/**
	 * @todo Implement testAltFilesFormat().
	 */
	public function testAltFilesFormat() {
		$this->assertFalse (Request::altFilesFormat());
		
		$_FILES ['a']['name'] = 1;
		
		$this->assertFalse (Request::altFilesFormat());
		
		$_FILES ['a']['name'] = array (1, 2);
		
		$this->assertTrue (Request::altFilesFormat());
	}

	/**
	 * @todo Implement testHost().
	 */
	public function testHost() {
		$this->assertNull (Request::host ());
		
		$_SERVER ['HTTP_HOST'] = 'localhost';
		
		$this->assertEquals (Request::host (), 'localhost');
	}

	/**
	 * @todo Implement testGet().
	 */
	public function testGet() {
		$this->assertFalse (Request::get ('a'));
		
		$this->assertNull (Request::get ('b', null));
		
		$this->assertEquals (Request::get ('c', 5), 5);
		
		$_GET ['e'] = 10;
		
		$this->assertEquals (Request::get ('e'), 10);
	}

	/**
	 * @todo Implement testIp().
	 */
	public function testIp() {
		$this->assertEquals (Request::ip (), Request::NONE_IP);
		
		$_SERVER ['REMOTE_ADDR'] = '127.0.0.1';
		
		$this->assertEquals (Request::ip (), '127.0.0.1');
	}

	/**
	 * @todo Implement testIsAjax().
	 */
	public function testIsAjax() {
		$this->assertFalse (Request::isAjax ());
		
		$_SERVER ['HTTP_X_REQUESTED_WITH'] = 'fake';
		
		$this->assertFalse (Request::isAjax ());
		
		$_SERVER ['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		
		$this->assertTrue (Request::isAjax ());
	}

	/**
	 * @todo Implement testIsConsole().
	 */
	public function testIsConsole() {
		$this->assertTrue (Request::isConsole ());
		
		unset ($_SERVER ['argc']);
		
		$this->assertFalse (Request::isConsole ());
		
		$_SERVER ['argc'] = 1;
		
		unset ($_SERVER ['argv']);
		
		$this->assertFalse (Request::isConsole ());
	}

	/**
	 * @todo Implement testIsFiles().
	 */
	public function testIsFiles() {
		unset ($_FILES);
		
		$this->assertFalse (Request::isFiles ());
		
		$_FILES ['file'] = 'fake';
		
		$this->assertTrue (Request::isFiles ());
	}

	/**
	 * @todo Implement testIsGet().
	 */
	public function testIsGet() {
		unset ($_GET);
		
		$this->assertFalse (Request::isGet ());
		
		$_GET ['arg'] = 'fake';
		
		$this->assertTrue (Request::isGet ());
	}

	/**
	 * @todo Implement testIsJsHttpRequest().
	 */
	public function testIsJsHttpRequest() {
		$this->assertFalse (Request::isJsHttpRequest ());
		
		$_SERVER ['REQUEST_METHOD'] = 'fake';
		
		$this->assertFalse (Request::isJsHttpRequest ());
		
		$_SERVER ['REQUEST_METHOD'] = 'POST';
		
		$this->assertFalse (Request::isJsHttpRequest ());
		
		global $JsHttpRequest_Active;
		
		$this->assertFalse (Request::isJsHttpRequest ());
		
		$JsHttpRequest_Active = 1;
		
		$this->assertTrue (Request::isJsHttpRequest ());
	}

	/**
	 * @todo Implement testIsPost().
	 */
	public function testIsPost() {
		unset ($_POST);
		
		$_SERVER ['REQUEST_METHOD'] = 'fake';
		
		$this->assertFalse (Request::isPost ());
		
		$_POST ['arg'] = 'fake';
		
		$this->assertFalse (Request::isPost ());
		
		$_SERVER ['REQUEST_METHOD'] = 'POST';
		
		$this->assertTrue (Request::isPost ());
	}

	/**
	 * @todo Implement testParam().
	 */
	public function testParam() {
		$this->assertNull (Request::param ('a'));
		
		Request::param ('a', 5);
		
		$this->assertEquals (Request::param ('a'), 5);
		
		Request::param ('a', null);
		
		$this->assertNull (Request::param ('a'));
	}

	/**
	 * @todo Implement testParams().
	 */
	public function testParams() {
		$this->assertType('array', Request::params ());
		
		Request::param ('a', 5);
		Request::param ('b', 10);
		Request::param ('c', null);
		
		$this->assertNotEmpty (Request::params ());
		
		$params = Request::params ();
		
		$this->assertEquals (count ($params), 3);
		
		$this->assertEquals ($params ['a'], 5);
		
		$this->assertEquals ($params ['b'], 10);
		
		$this->assertNull ($params ['c']);
	}

	/**
	 * @todo Implement testPost().
	 */
	public function testPost() {
		$this->assertFalse (Request::post ('a1'));
		
		$this->assertNull (Request::post ('b1', null));
		
		$this->assertEquals (Request::post ('c1', 5), 5);
		
		$_POST ['e1'] = 10;
		
		$this->assertEquals (Request::post ('e1'), 10);
		
		Request::$post_charset = 'Windows-1251';
		
		$_POST ['ee'] = iconv ('UTF-8', 'Windows-1251', 'Привет');
		
		$this->assertEquals (Request::post ('ee'), 'Привет');
	}

	/**
	 * @todo Implement testPostIds().
	 */
	public function testPostIds() {
		$this->assertEmpty (Request::postIds ());
		$this->assertType ('array', Request::postIds ());
		
		$_POST ['id'] = 'fake';
		
		$this->assertEmpty (Request::postIds ());
		
		$_POST ['id'] = '1';
		
		$this->assertEmpty (Request::postIds ());
		
		$_REQUEST ['id'] = '1';
		
		$this->assertEquals (Request::postIds (), array (1));
		
		$_REQUEST ['ids'] = 1;
		
		$this->assertEquals (Request::postIds (), array (1));
		
		$_REQUEST ['ids'] = array (1, 2);
		
		$this->assertNotEquals (Request::postIds (), array (1, 2));
		
		unset ($_REQUEST ['id']);
		
		$this->assertEquals (Request::postIds (), array (1, 2));
		
		$_REQUEST ['ids'] = '1,2,3';
		
		$this->assertEquals (Request::postIds (), array (1,2,3));
		
		$_REQUEST ['id'] = array (1, 2, 3, 4);
		
		$this->assertNotEquals (Request::postIds (), array (1,2,3,4));
	}

	/**
	 * @todo Implement testFile().
	 */
	public function testFile() {
		unset ($_FILES);
		
		$this->assertFalse (Request::file ('a'));
		
		$_FILES ['a'] = array (1);
		
		$this->assertFalse (Request::file ('a'));
		
		$_FILES ['a'] = array (
			'name'		=> 1,
			'type'		=> 2,
			'size'		=> 3,
			'tmp_name'	=> 4,
			'error'		=> 5
		);
		
		$this->assertType ('Request_File', Request::file ('a'));
	}

	/**
	 * @todo Implement testFileByIndex().
	 */
	public function testFileByIndex() {
		unset ($_FILES);
		
		$_FILES = array ();
		
		$this->assertNull (Request::fileByIndex (0));
		
		$this->assertNull (Request::fileByIndex (1));
		
		$_POST ['@file:0'] = 'test';
		
		$this->assertType ('Request_File_Test', Request::fileByIndex (0));
		
		unset ($_POST ['@file:0']);
		
		$_POST ['params']['@file:0'] = 'test';
		
		$this->assertType ('Request_File_Test', Request::fileByIndex (0));
		
		$_FILES ['a'] = array (
			'name'		=> 1,
			'type'		=> 2,
			'size'		=> 3,
			'tmp_name'	=> 4,
			'error'		=> 5
		);
		
		$this->assertType ('Request_File', Request::fileByIndex (0));
		
		$_FILES  ['a'] = array (
			'name'		=> 1,
			'type'		=> 2,
			'size'		=> 3,
			'tmp_name'	=> 4,
			'error'		=> 5
		);
		
		$this->assertType ('Request_File', Request::fileByIndex (0));
		
		$_FILES  [0]['a'] = array (
			'name'		=> 1,
			'type'		=> 2,
			'size'		=> 3,
			'tmp_name'	=> 4,
			'error'		=> 5
		);
		
		$this->assertType ('Request_File', Request::fileByIndex (0));
		
		$_FILES = array (0 => array ('name' => array (array (
			'name'		=> 1,
			'type'		=> 2,
			'size'		=> 3,
			'tmp_name'	=> 4,
			'error'		=> 5
		))));
		
		$this->assertType ('Request_File', Request::fileByIndex (0));
	}

	/**
	 * @todo Implement testFiles().
	 */
	public function testFiles() {
		unset ($_FILES);
		
		$_FILES ['a'] = array (
			'name'		=> 1,
			'type'		=> 2,
			'size'		=> 3,
			'tmp_name'	=> 4,
			'error'		=> 5
		);
		$_FILES ['b'] = array (
			'name'		=> 1,
			'type'		=> 2,
			'size'		=> 3,
			'tmp_name'	=> 4,
			'error'		=> 5
		);
		
		$_FILES ['c'] = array (
			'name'		=> 1,
			'type'		=> 2,
			'size'		=> 3,
			'tmp_name'	=> 4,
			'error'		=> 5
		);
		
		$files = Request::files ();
		
		$this->assertEquals (count ($files), 3);
		
		$this->assertType ('Request_File', $files ['a']);
		$this->assertType ('Request_File', $files ['b']);
		$this->assertType ('Request_File', $files ['c']);
		
	}

	/**
	 * @todo Implement testFilesCount().
	 */
	public function testFilesCount() {
		unset ($_FILES);
		
		$_FILES = array ();
		
		$this->assertEquals (Request::filesCount (), 0);
		
		$_FILES ['a'] = array (
			'name'		=> 1,
			'type'		=> 2,
			'size'		=> 3,
			'tmp_name'	=> 4,
			'error'		=> 5
		);
		$_FILES ['b'] = array (
			'name'		=> 1,
			'type'		=> 2,
			'size'		=> 3,
			'tmp_name'	=> 4,
			'error'		=> 5
		);
		
		$_FILES ['c'] = array (
			'name'		=> 1,
			'type'		=> 2,
			'size'		=> 3,
			'tmp_name'	=> 4,
			'error'		=> 5
		);
		
		$files = Request::files ();
		
		$this->assertEquals (Request::filesCount (), 3);
	}

	/**
	 * @todo Implement testUri().
	 */
	public function testUri() {
		$_SERVER ['REQUEST_URI'] = null;
		
		$this->assertEquals (Request::uri (), '/');
		
		$_SERVER ['REQUEST_URI'] = 'localhost';
		
		$this->assertEquals (Request::uri (), 'localhost');
		
		$_SERVER ['REQUEST_URI'] = 'localhost?arg=1';
		
		$this->assertEquals (Request::uri (), 'localhost');
		
		$_SERVER ['REQUEST_URI'] = 'localhost?arg=1';
		
		$this->assertEquals (Request::uri (false), 'localhost?arg=1');
	}

	/**
	 * @todo Implement testStringGet().
	 */
	public function testStringGet() {
		$_SERVER ['REQUEST_URI'] = null;
		
		$this->assertEquals (Request::stringGet (), '');
		
		$_SERVER ['REQUEST_URI'] = 'localhost';
		
		$this->assertEquals (Request::stringGet (), '');
		
		$_SERVER ['REQUEST_URI'] = 'localhost?arg=1';
		
		$this->assertEquals (Request::stringGet (), 'arg=1');
	}

	/**
	 * @todo Implement testReferer().
	 */
	public function testReferer() {
		$this->assertEquals (Request::referer (), '');
		
		$_SERVER ['HTTP_REFFERER'] = 'fake';
		
		$this->assertEquals (Request::referer (), 'fake');
	}

	/**
	 * @todo Implement testRequestMethod().
	 */
	public function testRequestMethod() {
		$this->assertEquals (Request::requestMethod (), 'GET');
		
		$_SERVER ['REQUEST_METHOD'] = 'POST';
		
		$this->assertEquals (Request::requestMethod (), 'POST');
	}

	/**
	 * @todo Implement testServer().
	 */
	public function testServer() {
		$this->assertEquals (Request::server (), '');
		
		$_SERVER ['SERVER_NAME'] = 'fake';
		
		$this->assertEquals (Request::server (), 'fake');
	}

	/**
	 * @todo Implement testSessionId().
	 */
	public function testSessionId() {	
		$this->assertNotEmpty (@Request::sessionId ());
		
		$_COOKIE ['PHPSESSID'] = 'sdsdsd';
		
		$this->assertNotEmpty (@Request::sessionId ());
		
		$_COOKIE ['PHPSESSID'] = null;
		
		$_GET ['PHPSESSID'] = 'sdsdsd';
		
		$this->assertNotEmpty (@Request::sessionId ());
		
		unset ($_SESSION);
		
		$this->assertNotEmpty (@Request::sessionId ());
	}

}

?>