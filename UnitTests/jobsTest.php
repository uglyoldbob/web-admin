<?php
namespace UnitTestFiles\Test;
use PHPUnit\Framework\TestCase;

require 'UnitTests/ModifiedTestCase.php';

class jobsTest extends ModifiedTestCase
{	
	private $test_user = "testuser";
	private static $test_pw = "";
	private $test_email = "test@testing.com";

	public static function setUpBeforeClass()
	{
		//generate a password for testing
		//not actually cryptographically secure or sufficiently random
		jobsTest::$test_pw = substr(md5(rand()), 0, 16);
	}

	public function testPage1()
	{
		require_once("jobs.php");
		$this->assertNoErrors();
	}
}
?>