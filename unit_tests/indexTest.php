<?php
namespace UnitTestFiles\Test;
use PHPUnit\Framework\TestCase;

require_once("web/global.php");
require_once("web/include/exceptions.php");

class indexTest extends TestCase
{
	private $config_name = "web/config.ini";
    public function testConfig1()
	{
		$config = parse_ini_file($config_name);
		$this->assertNotEquals($config, FALSE);
	}
	
	/**
	 * @depends testConfig1
	 */
	public function testConfig2()
	{
		$config = FALSE;
		$this->expectException(ConfigurationMissingException::class);
		test_config($config);
	}
	
	/**
	 * @depends testConfig1
	 */
	public function testConfig3()
	{
		unset($config);
		$this->expectException(PermissionDeniedException::class);
		test_config($config);
	}
	
	/**
	 * @depends testConfig1
	 */
	public function testConfig4()
	{
		$config = parse_ini_file($config_name);
		$this->assertNotEquals($config, FALSE);
		test_config($config);
	}

	/**
	 * @depends testConfig4
	 */
	public function testDbConnect()
	{
		$config = parse_ini_file($config_name);
		$this->assertNotEquals($config, FALSE);
		openDatabase($config);
	}
}
?>