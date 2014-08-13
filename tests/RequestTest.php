<?php
/**
 * @package    SugiPHP
 * @subpackage HTTP
 * @category   tests
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\HTTP;

use PHPUnit_Framework_TestCase;

class RequestTest extends PHPUnit_Framework_TestCase
{
	public function testCustomCreation()
	{
		$req = Request::custom("http://example.com/path/to/file.php?arg1=one&arg2=two");
		// default request method is GET
		$this->assertEquals("GET", $req->server["REQUEST_METHOD"]);
		// method()
		$this->assertEquals("GET", $req->getMethod());
		// domain
		$this->assertEquals("example.com", $req->server["HTTP_HOST"]);
		// host()
		$this->assertEquals("example.com", $req->getHost());
		// scheme
		$this->assertEquals("http", $req->getScheme());
		// port
		$this->assertEquals(80, $req->server["SERVER_PORT"]);
		$this->assertEquals(80, $req->getPort());
		// getBbase()
		$this->assertEquals("http://example.com", $req->getBase());
		// PATH_INFO
		$this->assertEquals("/path/to/file.php", $req->server["PATH_INFO"]);
		// path()
		$this->assertEquals("/path/to/file.php", $req->getPath());
		// current()
		// $this->assertEquals("http://example.com/path/to/file.php", $req->current());
		// QUERY_STRING
		$this->assertEquals("arg1=one&arg2=two", $req->server["QUERY_STRING"]);
		// queue() arguments
		// $this->assertEquals("arg1=one&arg2=two", $req->queue());
		// arguments as array
		$this->assertInternalType("array", $req->query);
		// arg1
		$this->assertEquals("one", $req->query["arg1"]);
		// arg2
		$this->assertEquals("two", $req->query["arg2"]);
		// REQUEST_URI
		$this->assertEquals("/path/to/file.php?arg1=one&arg2=two", $req->server["REQUEST_URI"]);
		// address()
		// $this->assertEquals("http://example.com/path/to/file.php?arg1=one&arg2=two", $req->address());
		// ajax()
		$this->assertSame(false, $req->isAjax());
		// isCli()
		$this->assertSame(true, $req->isCli());
		// REMOTE_ADDR
		// $this->assertEquals("127.0.0.1", $req->server["REMOTE_ADDR"]);
		// ip()
		// $this->assertEquals("127.0.0.1", $req->ip());
	}

	public function testSeters()
	{
		$req = Request::custom("http://user:pass@example.com/path/to/file.php?arg1=one&arg2=two");
		// default request method is GET
		$this->assertEquals("GET", $req->server["REQUEST_METHOD"]);
		// method()
		$this->assertEquals("GET", $req->getMethod());

		// set method
		$this->assertInstanceOf("\SugiPHP\HTTP\Request", $req->setMethod("POST"));
		$this->assertEquals("POST", $req->server["REQUEST_METHOD"]);
		$this->assertEquals("POST", $req->getMethod());
		// restoring
		$req->setMethod("GET");
		$this->assertEquals("GET", $req->server["REQUEST_METHOD"]);
		$this->assertEquals("GET", $req->getMethod());

		// set scheme
		$this->assertInstanceOf("\SugiPHP\HTTP\Request", $req->setScheme("https"));
		$this->assertEquals("on", $req->server["HTTPS"]);
		$this->assertEquals("https", $req->getScheme());
		$this->assertEquals(443, $req->getPort());
		// restore
		$req->setScheme("http");
		$this->assertEquals("off", $req->server["HTTPS"]);
		$this->assertEquals("http", $req->getScheme());
		$this->assertEquals(80, $req->getPort());

		// set port
		$this->assertInstanceOf("\SugiPHP\HTTP\Request", $req->setPort(81));
		$this->assertEquals(81, $req->getPort());
		// restore
		$req->setPort(80);
		$this->assertEquals(80, $req->getPort());

		// set user and pass
		$this->assertInstanceOf("\SugiPHP\HTTP\Request", $req->setUser("user2"));
		$this->assertEquals("user2", $req->getUser());
		$this->assertInstanceOf("\SugiPHP\HTTP\Request", $req->setPassword("pass2"));
		$this->assertEquals("pass2", $req->getPassword());
		// set null
		$req->setUser(null);
		$this->assertSame(null, $req->getUser());
		$req->setPassword(null);
		$this->assertSame(null, $req->getPassword());
		// set user and pass at one
		$this->assertInstanceOf("\SugiPHP\HTTP\Request", $req->setUserPass("user", "pass"));
		$this->assertEquals("user", $req->getUser());
		$this->assertEquals("pass", $req->getPassword());

		// set path
		$this->assertInstanceOf("\SugiPHP\HTTP\Request", $req->setPath("/new/path"));
		$this->assertEquals("/new/path", $req->getPath());
		// restore
		$req->setPath("path/to/file.php");
		$this->assertEquals("/path/to/file.php", $req->getPath());

		// set host
		$this->assertInstanceOf("\SugiPHP\HTTP\Request", $req->setHost("sub.foobar.info"));
		$this->assertEquals("sub.foobar.info", $req->getHost());
		// restore
		$req->setHost("example.com");
		$this->assertEquals("example.com", $req->getHost());
	}

	public function testHosts()
	{
		$req = Request::custom("http://example");
		$this->assertEquals("example", $req->getHost());
		// this probably should throw an exception
		$req = Request::custom("http://example.");
		$this->assertEquals("example", $req->getHost());
		$req = Request::custom("http://.example.");
		$this->assertEquals("example", $req->getHost());
	}

	public function testCustomPaths()
	{
		$req = Request::custom("http://example.com/path");
		$this->assertEquals("/path", $req->getPath());
		$req = Request::custom("http://example.com/path/");
		$this->assertEquals("/path", $req->getPath());
		$req = Request::custom("/path");
		$this->assertEquals("/path", $req->getPath());
		$req = Request::custom("/path/");
		$this->assertEquals("/path", $req->getPath());
		$req = Request::custom("path");
		$this->assertEquals("/path", $req->getPath());
		$req = Request::custom("/path/to");
		$this->assertEquals("/path/to", $req->getPath());
		$req = Request::custom("/path/to/");
		$this->assertEquals("/path/to", $req->getPath());
		$req = Request::custom("path/to");
		$this->assertEquals("/path/to", $req->getPath());
	}

	public function testCustomPathFileStyle()
	{
		$req = Request::custom("http://example.com/path/index.html");
		$this->assertEquals("/path/index.html", $req->getPath());
		$req = Request::custom("http://example.com/path/index.html/");
		$this->assertEquals("/path/index.html", $req->getPath());
	}

	public function testCustomHttpsCreation()
	{
		$req = Request::custom("https://example.com/path/to/file.php?arg1=one&arg2=two");
		// scheme
		$this->assertEquals("https", $req->getScheme());
		// port
		$this->assertSame(443, $req->getPort());
		// https is on
		$this->assertEquals("on", $req->server["HTTPS"]);
		// base()
		// $this->assertEquals("https://example.com", $req->base());
	}

	public function testCustomPortUserPass()
	{
		$req = Request::custom("http://user1:pass1@example.com:8080/path/to/file.php?arg1=one&arg2=two");
		// scheme()
		$this->assertEquals("http", $req->getScheme());
		// SERVER_PORT
		$this->assertSame(8080, $req->getPort());
		// user
		$this->assertEquals("user1", $req->getUser());
		// pass
		$this->assertEquals("pass1", $req->getPassword());
		// REQUEST_URI
		$this->assertEquals("/path/to/file.php?arg1=one&arg2=two", $req->server["REQUEST_URI"]);
		// address()
		// $this->assertEquals("http://example.com/path/to/file.php?arg1=one&arg2=two", $req->address());
		// HTTP_HOST
		$this->assertEquals("example.com", $req->server["HTTP_HOST"]);
		// host()
		$this->assertEquals("example.com", $req->getHost());
	}

	public function testMoreGetParams()
	{
		$req = Request::custom("http://example.com/path/to/file.php?arg1=one&arg2=two", "get", array("arg1" => "edno", "foo" => "bar"));
		// default request method is GET
		$this->assertEquals("GET", $req->server["REQUEST_METHOD"]);
		// method()
		$this->assertEquals("GET", $req->getMethod());
		// QUERY_STRING
		$this->assertEquals("arg1=edno&arg2=two&foo=bar", $req->server["QUERY_STRING"]);
		// queue() arguments
		// $this->assertEquals("arg1=edno&arg2=two&foo=bar", $req->queue());
		// REQUEST_URI
		$this->assertEquals("/path/to/file.php?arg1=edno&arg2=two&foo=bar", $req->server["REQUEST_URI"]);
		// address()
		// $this->assertEquals("http://example.com/path/to/file.php?arg1=edno&arg2=two&foo=bar", $req->address());
	}

	public function testPostParams()
	{
		$req = Request::custom("http://example.com/path/to/file.php?arg1=one&arg2=two", "post", array("arg1" => "edno", "foo" => "bar"));
		// default request method is GET
		$this->assertEquals("POST", $req->server["REQUEST_METHOD"]);
		// method()
		$this->assertEquals("POST", $req->getMethod());
		// QUERY_STRING
		$this->assertEquals("arg1=one&arg2=two", $req->server["QUERY_STRING"]);
		// queue() arguments
		// $this->assertEquals("arg1=one&arg2=two", $req->queue());
		// arguments as array
		$this->assertInternalType("array", $req->post);
		// GET arg1
		$this->assertEquals("one", $req->query["arg1"]);
		// GET arg2
		$this->assertEquals("two", $req->query["arg2"]);
		// POST arg1
		$this->assertEquals("edno", $req->post["arg1"]);
		// POST foo
		$this->assertEquals("bar", $req->post["foo"]);
	}

	public function testPostParamsWithCustomMethod()
	{
		$req = Request::custom("http://example.com/path/to/file.php?arg1=one&arg2=two", "DELETE", array("arg1" => "edno", "foo" => "bar"));
		// default request method is GET
		$this->assertEquals("DELETE", $req->server["REQUEST_METHOD"]);
		// method()
		$this->assertEquals("DELETE", $req->getMethod());
		// QUERY_STRING
		$this->assertEquals("arg1=one&arg2=two", $req->server["QUERY_STRING"]);
		// queue() arguments
		// $this->assertEquals("arg1=one&arg2=two", $req->queue());
		// arguments as array
		$this->assertInternalType("array", $req->post);
		// GET arg1
		$this->assertEquals("one", $req->query["arg1"]);
		// GET arg2
		$this->assertEquals("two", $req->query["arg2"]);
		// POST arg1
		$this->assertEquals("edno", $req->post["arg1"]);
		// POST foo
		$this->assertEquals("bar", $req->post["foo"]);
	}

	public function testCookies()
	{
		$req = Request::custom("", "GET", array(), array("cookiename" => "cookievalue", "foo" => "bar"));
		// cookies array
		$this->assertInternalType("array", $req->cookie);
		// cookie
		$this->assertEquals("cookievalue", $req->cookie["cookiename"]);
		// cookie2
		$this->assertEquals("bar", $req->cookie["foo"]);
	}

	public function testClientIp()
	{
		$req = Request::custom();
		$this->assertSame("127.0.0.1", $req->getClientIp());
	}

	public function testClientIpFromCli()
	{
		$req = Request::real();
		$this->assertSame("", $req->getClientIp());
	}

	public function testClientIpFromCliGetFromUntrustedProxy()
	{
		$_SERVER["HTTP_X_FORWARDED_FOR"] = "5.6.7.8";
		$req = Request::real();
		$this->assertSame("", $req->getClientIp());
	}

	public function testClientIpFromUntrustedProxy()
	{
		$_SERVER["REMOTE_ADDR"] = "1.2.3.4";
		$_SERVER["HTTP_X_FORWARDED_FOR"] = "5.6.7.8";
		$req = Request::real();
		$this->assertSame("1.2.3.4", $req->getClientIp());
	}

	public function testClientIpFromTrustedProxy()
	{
		$_SERVER["REMOTE_ADDR"] = "1.2.3.4";
		$_SERVER["HTTP_X_FORWARDED_FOR"] = "5.6.7.8";
		$req = Request::real();
		$req->setTrustedProxies(array("1.2.3.4"));
		$this->assertSame("5.6.7.8", $req->getClientIp());
	}
}
