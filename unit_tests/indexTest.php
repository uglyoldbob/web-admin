<?php
namespace UnitTestFiles\Test;
use PHPUnit\Framework\TestCase;

require_once("web/global.php");

class indexTest extends TestCase
{
	private $config_name = "web/config.ini";
	
	private $ex1;
	private $ex2;
	private $ex3;
	
	public function __construct()
	{
		parent::__construct();
		$this->ex1 = new \ConfigurationMissingException("bla");
		$this->ex2 = new \PermissionDeniedException("bla");
		$this->ex3 = new \DatabaseConnectionFailedException("bla");
	}
	
	protected function setUp()
	{
		$this->errors = array();
		set_error_handler(array($this, "errorHandler"));
	}
	
	public function errorHandler($errno, $errstr, $errfile, $errline, $errcontext)
	{
		$this->errors[] = compact("errno", "errstr", "errfile", "errline", "errcontext");
	}
	
	public function assertError($errstr, $errno)
	{
		foreach ($this->errors as $error)
		{
			if ($error["errstr"] === $errstr && $error["errno"] === $errno)
			{
				return;
			}
		}
		$this->fail("Error with level " . $errno . " and message '" . $errstr . "' not found in ",
			var_export($this->errors, TRUE));
	}
	
	public function assertNoErrors()
	{
		if (count($this->errors) > 0)
		{
			$this->fail("Errors were found where none were expected: ",
				var_export($this->errors, TRUE));
		}
	}
	
	public function testNoErrors1()
	{
		trigger_error("Triggered error");
		$this->assertNoErrors();
	}
	
	public function testNoErrors2()
	{
		include_once("idontexist.php");
		$this->assertNoErrors();
	}
	
    public function testConfig1()
	{
		$config = parse_ini_file($this->config_name);
		$this->assertNotEquals($config, FALSE);
	}
	
	/**
	 * @depends testConfig1
	 */
	public function testConfig2()
	{
		$config = FALSE;
		if (method_exists($this,'expectException'))
		{
			$this->expectException(get_class($this->ex1));
		}
		else
		{
			$this->setExpectedException(get_class($this->ex1));
		}
		test_config($config);
	}
	
	/**
	 * @depends testConfig1
	 */
	public function testConfig4()
	{
		$config = parse_ini_file($this->config_name);
		$this->assertNotEquals($config, FALSE);
		test_config($config);
	}

	/**
	 * @depends testConfig4
	 */
	public function testDbConnect1()
	{
		$config = parse_ini_file($this->config_name);
		$this->assertNotEquals($config, FALSE);
		openDatabase($config);
	}
	
	/**
	 * @depends testConfig4
	 */
	public function testDbConnect2()
	{
		if (method_exists($this,'expectException'))
		{
			$this->expectException(get_class($this->ex3));
		}
		else
		{
			$this->setExpectedException(get_class($this->ex3));
		}
		$config = parse_ini_file($this->config_name);
		$this->assertNotEquals($config, FALSE);
		$config["database_username"] = "notRealUser";
		$config["database_password"] = "notActualPassword";
		openDatabase($config);
		$this->assertNoErrors();
	}
}
?>