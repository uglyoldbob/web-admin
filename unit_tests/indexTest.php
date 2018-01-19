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
	}
}
?>