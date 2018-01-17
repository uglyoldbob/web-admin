<?php
namespace UnitTestFiles\Test;
use PHPUnit\Framework\TestCase;

require_once("web/global.php");

class indexTest extends TestCase
{
	private $config_name = "web/config.ini";
	
	private $ex1;
	private $ex2;
	
	public function __construct()
	{
		parent::__construct();
		$ex1 = new ConfigurationMissingException();
		$ex2 = new PermissionDeniedException();
	}
	
    public function testConfig1()
	{
		$config = parse_ini_file($this->$config_name);
		$this->assertNotEquals($config, FALSE);
	}
	
	/**
	 * @depends testConfig1
	 */
	public function testConfig2()
	{
		$config = FALSE;
		$this->expectException(get_class($this->ex1));
		test_config($config);
	}
	
	/**
	 * @depends testConfig1
	 */
	public function testConfig3()
	{
		unset($config);
		$this->expectException(get_class($this->ex2));
		test_config($config);
	}
	
	/**
	 * @depends testConfig1
	 */
	public function testConfig4()
	{
		$config = parse_ini_file($this->$config_name);
		$this->assertNotEquals($config, FALSE);
		test_config($config);
	}

	/**
	 * @depends testConfig4
	 */
	public function testDbConnect()
	{
		$config = parse_ini_file($this->$config_name);
		$this->assertNotEquals($config, FALSE);
		openDatabase($config);
	}
}
?>