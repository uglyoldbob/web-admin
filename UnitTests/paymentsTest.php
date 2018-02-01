<?php
namespace UnitTestFiles\Test;
use PHPUnit\Framework\TestCase;

class paymentsTest extends TestCase
{
	private $config_name = "config.ini";
	private $session_file = "./session_id.txt";
	
	private $test_user = "testuser";
	private static $test_pw = "";
	private $test_email = "test@testing.com";

	private $session_id;
	
	public static function setUpBeforeClass()
	{
		//generate a password for testing
		//not actually cryptographically secure or sufficiently random
		paymentsTest::$test_pw = substr(md5(rand()), 0, 16);
	}

	protected function setUp()
	{
		chdir("./web");
		$this->errors = array();
		set_error_handler(array($this, "errorHandler"));
		if ($this->session_id = @file_get_contents($this->session_file))
		{
			session_id($this->session_id);
		}
		else
		{
			file_put_contents($this->session_file, session_id());
		}
	}
	
	protected function tearDown()
	{
		//handle cases where session_id is regenerated
		file_put_contents($this->session_file, session_id());
		session_write_close();
		session_unset();
	}
	
	public function errorHandler($errno, $errstr, $errfile, $errline, $errcontext)
	{
		$this->errors[] = compact("errno", "errstr", "errfile", "errline");
	}
	
	public function assertError($errstr, $errno)
	{
		$state = "ERRORZ:\n";
		foreach ($this->errors as $error)
		{
			$state .= $err['errno'] . ", " . $err['errstr'] . ", " . $err['errfile'] . ", " . $err['errline'] . "\n";
			if ($error["errstr"] === $errstr && $error["errno"] === $errno)
			{
				$this->assertTrue(true);
				return;
			}
		}
		$this->fail("Error with level " . $errno . " and message '" . $errstr . "' not found in:\n" . $state);
	}
	
	public function assertNoErrors()
	{
		if (count($this->errors) > 0)
		{
			$state = "ERRORZ:\n";
			foreach ($this->errors as $err)
			{
				$state .= $err['errno'] . ", " . $err['errstr'] . ", " . $err['errfile'] . ", " . $err['errline'] . "\n";
			}

			$this->fail("Errors were found where none were expected:\n" . $state);
		}
		$this->assertTrue(true);
	}

	public function testAssertErrors1()
	{
		trigger_error("Triggered error");
		$this->assertError("Triggered error", E_USER_NOTICE);
	}
	
	public function testAssertErrors3()
	{
		$this->assertNoErrors();
	}
	
	public function testPage1()
	{
		require_once("payments.php");
		$this->assertNoErrors();
	}
}
?>