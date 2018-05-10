<?php
namespace UnitTestFiles\Test;
use PHPUnit\Framework\TestCase;

require_once 'UnitTests/ModifiedTestCase.php';

class contactsTest extends ModifiedTestCase
{	
	private $test_user = "testuser";
	private static $test_pw = "";
	private $test_email = "test@testing.com";

	public static function setUpBeforeClass()
	{
		//generate a password for testing
		//not actually cryptographically secure or sufficiently random
		contactsTest::$test_pw = substr(md5(rand()), 0, 16);
	}

	public function testPage1()
	{
		ob_start();
		require_once("contacts.php");
		$results = ob_get_contents();
		ob_end_clean();
		$this->assertNoErrors();
	}
}
?>