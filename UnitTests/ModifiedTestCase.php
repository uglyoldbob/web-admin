<?php
namespace UnitTestFiles\Test;
use PHPUnit\Framework\TestCase;

class ModifiedTestCase extends TestCase
{
	protected $config_name = "config.ini";
	protected $session_file = "./session_id.txt";
	
	protected $session_id;
	protected $errors;
	
	protected function setUp()
	{
		chdir("./web");
		$this->clearErrors();
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

	function startCookies($name)
	{
		$cookie_file = "./" . $name . ".txt";
		if (file_exists($cookie_file))
		{
			$_COOKIE['PHPSESSION'] = file_get_contents($cookie_file);
		}
	}

	function endCookies($name)
	{
		$cookie_file = "./" . $name . ".txt";
		if (array_key_exists('PHPSESSION', $_COOKIE))
		{
			file_put_contents($cookie_file, $_COOKIE['PHPSESSION']);
		}
		else if (file_exists($cookie_file))
		{
			unlink($cookie_file);
		}
	}
	
	public function errorHandler($errno, $errstr, $errfile, $errline, $errcontext)
	{
		$this->errors[] = compact("errno", "errstr", "errfile", "errline");
	}
	
	public function clearErrors()
	{
		$this->errors = array();
	}
	
	public function assertError($errstr, $errno)
	{
		$state = "ERRORS:\n";
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

	public function assertNoContain($nothere, $search)
	{
		if (strpos($search, $nothere))
		{
			$this->fail("Failed asserting that '" . $nothere . "' was NOT in the results\n");
		}
		else
		{
			$this->assertTrue(true);
		}
	}
	
	public function assertNoErrors()
	{
		if (count($this->errors) > 0)
		{
			$state = "ERRORS:\n";
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
}
?>