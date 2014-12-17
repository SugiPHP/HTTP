<?php
/**
 * PHP Unit tests for SugiPHP Response Class
 *
 * @package SugiPHP.HTTP
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\HTTP;

use PHPUnit_Framework_TestCase;

class ResponseTest extends PHPUnit_Framework_TestCase
{
	public function testResponseCreation()
	{
		$resp = new Response();
		$this->assertTrue($resp instanceof ResponseInterface);
	}

	public function testStatusCode()
	{
		$resp = new Response();
		// default is 200 OK
		$this->assertSame(200, $resp->getStatusCode());
		$this->assertSame("OK", $resp->getStatusText());
		$this->assertTrue(strpos($resp->__toString(), "HTTP/1.0 200 OK\r\n") === 0);
		$resp->setStatusCode(301);
		$this->assertSame(301, $resp->getStatusCode());
		$this->assertSame($resp::$statusCodes[301], $resp->getStatusText());
		$this->assertTrue(strpos($resp->__toString(), "HTTP/1.0 301") === 0);
		$resp->setStatusCode(301, "foo");
		$this->assertSame(301, $resp->getStatusCode());
		$this->assertSame("foo", $resp->getStatusText());
		$this->assertTrue(strpos($resp->__toString(), "HTTP/1.0 301 foo\r\n") === 0);
		$resp->setStatusCode(999, "bar");
		$this->assertSame(999, $resp->getStatusCode());
		$this->assertSame("bar", $resp->getStatusText());
		$this->assertTrue(strpos($resp->__toString(), "HTTP/1.0 999 bar\r\n") === 0);
		$resp->setStatusCode(200, "foo bar");
		$this->assertSame(200, $resp->getStatusCode());
		$this->assertSame("foo bar", $resp->getStatusText());
		$this->assertTrue(strpos($resp->__toString(), "HTTP/1.0 200 foo bar\r\n") === 0);
		$resp->setStatusCode(200);
		$this->assertSame(200, $resp->getStatusCode());
		$this->assertSame("OK", $resp->getStatusText());
		$resp->setStatusCode(200, "foo");
		$this->assertTrue(strpos($resp->__toString(), "HTTP/1.0 200 foo\r\n") === 0);
		$this->assertSame(200, $resp->getStatusCode());
		$this->assertSame("foo", $resp->getStatusText());
		$this->assertTrue(strpos($resp->__toString(), "HTTP/1.0 200 foo\r\n") === 0);
		// no such code - empty status text will be expected
		$resp->setStatusCode(999);
		$this->assertSame(999, $resp->getStatusCode());
		$this->assertSame("", $resp->getStatusText());
		$this->assertTrue(strpos($resp->__toString(), "HTTP/1.0 999 \r\n") === 0);
	}

	public function testEmptyContent()
	{
		$resp = new Response();

		$this->assertEmpty($resp->getContent());
		$this->assertSame("", $resp->getContent());
	}

	public function testProtocolVersion()
	{
		$resp = new Response();
		// default version is 1.0
		$this->assertSame("1.0", $resp->getVersion());
		$this->assertTrue(strpos($resp->__toString(), "HTTP/1.0 200 OK\r\n") === 0);
		$resp->setVersion("1.1");
		$this->assertSame("1.1", $resp->getVersion());
		$this->assertTrue(strpos($resp->__toString(), "HTTP/1.1 200 OK\r\n") === 0);
		$resp->setVersion("1.0");
		$this->assertSame("1.0", $resp->getVersion());
		$this->assertTrue(strpos($resp->__toString(), "HTTP/1.0 200 OK\r\n") === 0);
	}
}
