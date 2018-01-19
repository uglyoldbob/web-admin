<?php
namespace UnitTestFiles\Test;
use PHPUnit\Framework\TestCase;

class FirstTest extends TestCase
{
    public function testTrueCheck()
	{
		$condition = true;
		$this->assertTrue($condition);
	}
}
?>