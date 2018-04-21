<?php
namespace UnitTestFiles\Test;
use PHPUnit\Framework\TestCase;

require_once 'UnitTests/ModifiedTestCase.php';

class indexTest extends ModifiedTestCase
{
	private $test_user = "testuser";
	private static $test_pw = "";
	private $test_email = "test@testing.com";

	public static function setUpBeforeClass()
	{
		//generate a password for testing
		//not actually cryptographically secure or sufficiently random
		indexTest::$test_pw = substr(md5(rand()), 0, 16);
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
			$this->expectException('\webAdmin\ConfigurationMissingException');
		}
		else
		{
			$this->setExpectedException('\webAdmin\ConfigurationMissingException');
		}
		require_once("webAdmin/exceptions.php");
		require_once("webAdmin/global.php");
		\webAdmin\test_config($config);
	}
	
	/**
	 * @depends testConfig1
	 */
	public function testConfig4()
	{
		$config = parse_ini_file($this->config_name);
		$this->assertNotEquals($config, FALSE);
		require_once("webAdmin/exceptions.php");
		require_once("webAdmin/global.php");
		\webAdmin\test_config($config);
		$this->assertNoErrors();
	}

	/**
	 * @depends testConfig4
	 */
	public function testDbConnect1()
	{
		$config = parse_ini_file($this->config_name);
		$this->assertNotEquals($config, FALSE);
		require_once("webAdmin/exceptions.php");
		require_once("webAdmin/global.php");
		\webAdmin\openDatabase($config);
		$this->assertNoErrors();
	}
	
	/**
	 * @depends testConfig4
	 */
	public function testDbConnect2()
	{
		if (method_exists($this,'expectException'))
		{
			$this->expectException('\webAdmin\DatabaseConnectionFailedException');
		}
		else
		{
			$this->setExpectedException('\webAdmin\DatabaseConnectionFailedException');
		}
		$config = parse_ini_file($this->config_name);
		$this->assertNotEquals($config, FALSE);
		$config["database_username"] = "notRealUser";
		$config["database_password"] = "notActualPassword";
		require_once("webAdmin/exceptions.php");
		require_once("webAdmin/global.php");
		\webAdmin\openDatabase($config);
		$this->assertNoErrors();
	}
	
	public function testSessionStart1()
	{
		require_once("webAdmin/exceptions.php");
		require_once("webAdmin/session.php");
		require_once("webAdmin/global.php");
		$config = parse_ini_file($this->config_name);
		\webAdmin\test_config($config);
		$mysql_db = \webAdmin\openDatabase($config);
		$cust_session = new \webAdmin\session($config, $mysql_db, "sessions");
		\webAdmin\start_my_session();
		$this->assertNoErrors();
		$this->assertTrue($_SESSION['initiated']);
		$this->assertEquals($_SESSION['HTTP_USER_AGENT'], md5(""));
	}

	/**
	 * @depends testSessionStart1
	 */
	public function testSessionStart2()
	{
		$_SESSION['HTTP_USER_AGENT'] = 'NOT A VALID MD5!';
		$_SESSION['username'] = 'something';
		$_SESSION['password'] = 'something';
		$_SERVER['HTTP_USER_AGENT'] = "WHATEVER";
		require_once("webAdmin/exceptions.php");
		require_once("webAdmin/session.php");
		require_once("webAdmin/global.php");
		$config = parse_ini_file($this->config_name);
		\webAdmin\test_config($config);
		$mysql_db = \webAdmin\openDatabase($config);
		$cust_session = new \webAdmin\session($config, $mysql_db, "sessions");
		\webAdmin\start_my_session();
		$this->assertNoErrors();
		if (isset($_SESSION['username']))
		{
			$this->fail('Failed to unset $_SESSION[\'username\']');
		}
		if (isset($_SESSION['password']))
		{
			$this->fail('Failed to unset $_SESSION[\'password\']');
		}
		if (isset($_SESSION['HTTP_USER_AGENT']))
		{
			$this->fail('Failed to unset $_SESSION[\'HTTP_USER_AGENT\']');
		}
	}
	
	/**
	 * @depends testSessionStart2
	 */
	public function testPage1()
	{
		require_once("index.php");
		$this->assertNoErrors();
	}
	
	/**
	 * @depends testPage1
	 */
	public function testPage2()
	{
		$_POST["action"] = "register";
		require_once("index.php");
		$this->assertNoErrors();
	}

	/**
	 * @depends testPage2
	 */
	public function testPage3()
	{
		$_POST["action"] = "create_user";
		$_POST["username"] = $this->test_user;
		$_POST["pass2"] = indexTest::$test_pw;
		$_POST["pass3"] = indexTest::$test_pw;
		$_POST["email"] = $this->test_email;
		require_once("index.php");
		$this->assertNoErrors();
		$this->errors = array();	//clear errors
		$mysql_db = \webAdmin\openDatabase($config);
		$query = "select * from users;";
		$result = $mysql_db->query($query);
		$this->assertNoErrors();
		$this->assertEquals(1, $result->num_rows);
		$row = $result->fetch_row();
		$this->assertNoErrors();
		$this->assertEquals($this->test_user, $row[4]);
		$this->assertNotEquals(indexTest::$test_pw, $row[5]);
		$this->assertNotEquals('', $row[6]);
		$this->assertEquals(169000, $row[7]);
		$this->assertEquals($this->test_email, $row[16]);
	}
	
	/**
	 * @depends testPage3
	 */
	public function testPage4()
	{
		$mysql_db = \webAdmin\openDatabase($config);
		$query = "select * from users;";
		$result = $mysql_db->query($query);
		$this->assertNoErrors();
		$this->assertEquals(1, $result->num_rows);
		$row = $result->fetch_row();
		$this->assertNoErrors();
		$this->assertEquals($this->test_user, $row[4]);
		$this->assertNotEquals(indexTest::$test_pw, $row[5]);
		$this->assertNotEquals('', $row[6]);
		$this->assertEquals(169000, $row[7]);
		$this->assertEquals($this->test_email, $row[16]);
	}

	/**
	 * @depends testPage4
	 */
	public function testPage5()
	{
		$_POST["action"] = "login";
		$_POST["username"] = $this->test_user;
		$_POST["password"] = indexTest::$test_pw;
		require_once("index.php");
		$this->assertNoErrors();
	}

	/**
	 * @depends testPage5
	 */
	public function testPage6()
	{
		$_POST["action"] = "logout";
		require_once("index.php");
		$this->assertNoErrors();
	}
}
?>