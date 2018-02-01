<?php
namespace UnitTestFiles\Test;
use PHPUnit\Framework\TestCase;

require 'UnitTests/ModifiedTestCase.php';

class imageTest extends ModifiedTestCase
{	
	private $test_user = "testuser";
	private static $test_pw = "";
	private $test_email = "test@testing.com";

	public static function setUpBeforeClass()
	{
		//generate a password for testing
		//not actually cryptographically secure or sufficiently random
		imageTest::$test_pw = substr(md5(rand()), 0, 16);
	}
	
	protected function setUp()
	{
		parent::setUp();
		chdir("./uploads");
	}

	public function testPage1()
	{
		require_once("image.php");
		$this->assertNoErrors();
	}
}
?>