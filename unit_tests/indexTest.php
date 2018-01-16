<?php
namespace UnitTestFiles\Test;
use PHPUnit\Framework\TestCase;

require_once("web/global.php");

class indexTest extends TestCase
{

    public function testLoadConfig()
	{
		$config = parse_ini_file("web/config.ini");
		$this->assertNotEquals($config, FALSE);
	}

	/**
	 * @depends testLoadConfig
	 */
	public function testDbConnect()
	{
		$this->assertTrue(true);
	}
}
?>