<?php
/**
 * @package    SugiPHP
 * @subpackage HTTP
 * @author     Plamen Popov <tzappa@gmail.com>
 * @license    http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\HTTP;

class Request
{
	/**
	 * HTTP SERVER parameter container ($_SERVER).
	 * @var array
	 */
	public $server;

	/**
	 * HTTP GET parameters container ($_GET).
	 * @var array
	 */
	public $query;

	/**
	 * HTTP POST parameters container ($_POST).
	 * @var array
	 */
	public $post;

	/**
	 * Cookies container ($_COOKIE).
	 * @var array
	 */
	public $cookie;

	/**
	 * Constructor.
	 * It's protected for now. Instantiate it with static methods real() and custom()
	 * 
	 * @param array $server
	 * @param array $query
	 * @param array $post
	 * @param array $cookies
	 */
	protected function __construct(array $server, array $query, array $post, array $cookies)
	{
		$this->server = $server;
		$this->query  = $query;
		$this->post   = $post;
		$this->cookie = $cookies;
	}

	/**
	 * Creates Request instance with real HTTP request data.
	 * 
	 * @return SugiPHP\HTTP\Request
	 */
	public static function real()
	{
		return new self($_SERVER, $_GET, $_POST, $_COOKIE);
	}

	/**
	 * Creates Request instance with user defined data. Used for unit testing.
	 * 
	 * @param  string $uri
	 * @param  string $method
	 * @param  array  $params - custom parameters that will be injected in the request in POST, GET, etc. data
	 * @param  array  $cookies
	 * @return SugiPHP\HTTP\Request
	 */
	public static function custom($uri, $method = "GET", array $params = array(), array $cookies = array())
	{
		$method = strtoupper($method);

		// default values
		$server = array(
			"HTTP_HOST"             => "localhost",
			"SERVER_PORT"           => 80,
			"REMOTE_ADDR"           => "127.0.0.1",
			"REQUEST_METHOD"        => $method,
			"QUERY_STRING"          => "",
			"REQUEST_URI"           => "/",
			"PATH_INFO"             => "/",
			//
			// "REDIRECT_STATUS" => 200,
			// "HTTP_ACCEPT"           => "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
			// "HTTP_ACCEPT_LANGUAGE"  => "en-us,en;q=0.5",
			// "HTTP_ACCEPT_CHARSET"   => "utf-8;q=1.0",
			// "HTTP_ACCEPT_ENCODING" => "gzip, deflate"
			// "HTTP_DNT" => 1, // Do Not Track
			// "HTTP_CONNECTION" => "keep-alive",
			// "HTTP_CACHE_CONTROL" => "max-age=0",
			// "PATH" => "/usr/local/bin:/usr/bin:/bin",
			// "SERVER_NAME"           => "localhost",
			// "SERVER_ADDR" => "127.0.0.1",
			// "SERVER_SIGNATURE" => "Apache/2.2.22",
			// "SERVER_SOFTWARE" => "Apache/2.2.22",
			// "DOCUMENT_ROOT" => "/path/to/http/doc/root", 
			// "SERVER_ADMIN" => "[no address given]",
			// "REMOTE_PORT" => 12345,
			// "REDIRECT_QUERY_STRING" => http_build_query($params, "", "&"),
			// "REDIRECT_URL" => "/path",
			// "GATEWAY_INTERFACE" => "CGI/1.1",
			// "SERVER_PROTOCOL" => "HTTP/1.1",
			// "SCRIPT_NAME"           => "/index.php", 
			// "SCRIPT_FILENAME" => "/path/to/http/doc/root/index.php",
			// "PHP_SELF" => "/index.php/path",
			// "REQUEST_TIME_FLOAT" => 1361878419.594,
			// "REQUEST_TIME" => 1361878419
		);
	
		// content
		if ($method !== "GET") {
			$server["CONTENT_TYPE"] = "application/x-www-form-urlencoded";
		}

		// scheme://user:pass@host:port/path/script?query=value#fragment
		$parts = parse_url($uri);

		// scheme
		if (isset($parts["scheme"])) {
			if ($parts["scheme"] === "https") {
				$server["SERVER_PORT"] = 443;
				$server["HTTPS"] = "on";
			} else {
				$server["SERVER_PORT"] = 80;
			}
		}

		// port
		if (isset($parts["port"])) {
			$server["SERVER_PORT"] = $parts["port"];
		}

		// user
		if (isset($parts["user"])) {
			$server["PHP_AUTH_USER"] = $parts["user"];
		}

		// pass
		if (isset($parts["pass"])) {
			$server["PHP_AUTH_PW"] = $parts["pass"];
		}

		// host
		if (isset($parts["host"])) {
			// $server["SERVER_NAME"] = $parts["host"];
			// Not sure we should trim, or simply throw an exception
			$server["HTTP_HOST"] = trim($parts["host"], ".");
		}

		// path
		if (isset($parts["path"])) {
			// path ALWAYS begin with a slash and has no trailing slash
			$path = "/" . trim($parts["path"], "/");
			$server["PATH_INFO"] = $path;
			$server["REQUEST_URI"] = $path;
		}

		// query
		if ($method === "GET" and isset($parts["query"])) {
			parse_str(html_entity_decode($parts["query"]), $partsQ);
			// replacing query part from $uri to those set in array $query
			$query = array_merge($partsQ, $params);
		} elseif ($method === "GET") {
			$query = $params;
		} elseif (isset($parts["query"])) {
			parse_str(html_entity_decode($parts["query"]), $query);
		} else {
			$query = array();
		}
		$queryString = http_build_query($query, "", "&");
		if ($queryString) {
			$server["QUERY_STRING"] = $queryString;
			$server["REQUEST_URI"] .= "?".$queryString;
		}

		// post
		$post = ($method !== "GET") ? $params : array();

		if ($cookies) {
			// HTTP_COOKIE string look like this: "cs=alabalacookie; ci=1"
			$server["HTTP_COOKIE"] = http_build_query($cookies, "", "; ");
		}

		return new self($server, $query, $post, $cookies);
	}

	/**
	 * Returns request method used.
	 * 
	 * @return string
	 */
	public function getMethod()
	{
		return $this->server["REQUEST_METHOD"];
	}

	/**
	 * Sets custom request method.
	 *
	 * @param  string $method - GET, POST, etc.
	 * @return SugiPHP\HTTP\Request
	 */
	public function setMethod($method)
	{
		$this->server["REQUEST_METHOD"] = strtoupper($method);

		return $this;
	}

	/**
	 * Returns scheme: "http" or "https".
	 * 
	 * @return string
	 */
	public function getScheme()
	{
		return (!empty($this->server["HTTPS"]) AND filter_var($this->server["HTTPS"], FILTER_VALIDATE_BOOLEAN)) ? "https" : "http";
	}

	/**
	 * Sets custom scheme - "http" or "https".
	 * Additionally sets the port if not previously set to something non standard.
	 * 
	 * @param  string $scheme
 	 * @return SugiPHP\HTTP\Request
	 */
	public function setScheme($scheme)
	{
		if ($scheme === "https") {
			$this->server["HTTPS"] = "on";
			if (!isset($this->server["SERVER_PORT"]) or $this->server["SERVER_PORT"] == 80) {
				$this->server["SERVER_PORT"] = 443;
			}
		} else {
			$this->server["HTTPS"] = "off";
			if (!isset($this->server["SERVER_PORT"]) or $this->server["SERVER_PORT"] == 443) {
				$this->server["SERVER_PORT"] = 80;
			}
		}

		return $this;
	}

	/**
	 * Returns the server port on which the request is made.
	 * 
	 * @return integer
	 */
	public function getPort()
	{
		return $this->server["SERVER_PORT"];
	}

	/**
	 * Sets custom server port on which the request is made.
	 * 
	 * @param  integer $port
	 * @return SugiPHP\HTTP\Request
	 */
	public function setPort($port)
	{
		$this->server["SERVER_PORT"] = $port;

		return $this;
	}

	/**
	 * Returns the basic authentication user name.
	 * 
	 * @return string|null - Returns NULL if no user was specified in the request
	 */
	public function getUser()
	{
		return isset($this->server["PHP_AUTH_USER"]) ? $this->server["PHP_AUTH_USER"] : null;
	}	

	/**
	 * Sets the basic authentication user name.
	 * 
	 * @param  string|null $user - set to NULL for no user
	 * @return SugiPHP\HTTP\Request
	 */
	public function setUser($user)
	{
		$this->server["PHP_AUTH_USER"] = $user;

		return $this;
	}

	/**
	 * Returns the basic authentication password.
	 * 
	 * @return string|null - Returns NULL if no password was specified in the request
	 */
	public function getPassword()
	{
		return isset($this->server["PHP_AUTH_PW"]) ? $this->server["PHP_AUTH_PW"] : null;
	}

	/**
	 * Sets the basic authentication password.
	 * 
	 * @param  string|null $password - set to NULL for no password
	 * @return SugiPHP\HTTP\Request
	 */
	public function setPassword($password)
	{
		$this->server["PHP_AUTH_PW"] = $password;

		return $this;
	}

	/**
	 * Sets auth user password.
	 *
	 * @param  string|null $user - set to NULL for no password
	 * @param  string|null $password - set to NULL for no password
	 * @return SugiPHP\HTTP\Request
	 */
	public function setUserPass($user, $password)
	{
		$this->server["PHP_AUTH_USER"] = $user;
		$this->server["PHP_AUTH_PW"] = $password;

		return $this;
	}

	/**
	 * Returns the path extracted from the request. The path has no trailing slash and always have leading one.
	 * Examples: 
	 *  * /
	 *  * /home
	 *  * /index.php
	 *  * /users/login
	 *  * /users/login.php
	 * 
	 * @return string
	 */
	public function getPath()
	{
		return "/" . $this->uri();
	}

	/**
	 * Sets request path.
	 * 
	 * @param  string $path
	 * @return SugiPHP\HTTP\Request
	 */
	public function setPath($path)
	{
		// path ALWAYS begin with a slash and has no trailing slash
		$path = "/" . trim($path, "/");
		$this->server["PATH_INFO"] = $path;
		$this->server["REQUEST_URI"] = $path;

		return $this;
	}

	/**
	 * Returns host name like "subdomain.example.com".
	 * 
	 * @return string
	 */
	public function getHost()
	{
		return $this->server["HTTP_HOST"];
	}

	/**
	 * Sets the host
	 *
	 * @param  string $host
	 * @return SugiPHP\HTTP\Request
	 */
	public function setHost($host)
	{
		$this->server["HTTP_HOST"] = $host;

		return $this;
	}

	/**
	 * Returns request scheme://host
	 * 
	 * @return string
	 */
	public function getBase()
	{
		return $this->getScheme() . "://" .  $this->getHost();
	}

	/**
	 * Is the request AJAX or not
	 * 
	 * @return boolean
	 */
	public function isAjax()
	{
	 	return (isset($this->server["HTTP_X_REQUESTED_WITH"]) AND (strtolower($this->server["HTTP_X_REQUESTED_WITH"]) === "xmlhttprequest"));
	}

	/**
	 * Request from CLI
	 * 
	 * @return boolean
	 */
	public function isCLI()
	{
	 	return (PHP_SAPI === "cli");
	}

	/**
	 * Returns request (protocol+host+uri)
	 * @return string
	 */
	// public function current()
	// {
	// 	return $this->base() . $this->getPath();
	// }

	/**
	 * The part of the url which is after the ?
	 * @return string
	 */
	// public function queue()
	// {
	// 	return http_build_query($this->query, "", "&");
	// }

	/**
	 * Returns request scheme://host/uri?queue
	 * 
	 * @return string
	 * @todo: maybe shold place user/pass and port (if not default)
	 */
	// public function address()
	// {
	// 	if ($queue = $this->queue()) {
	// 		$queue = "?" . $queue;
	// 	}
	// 	return $this->base().$this->getPath().$queue;
	// }


	/**
	 * Client's IP
	 * @return string
	 */
	// public function ip()
	// {
	// 	if ($this->cli()) return "127.0.0.1"; // The request was started from the command line
	// 	if (isset($this->server["HTTP_X_FORWARDED_FOR"])) return $this->server["HTTP_X_FORWARDED_FOR"]; // If the server is behind proxy
	// 	if (isset($this->server["HTTP_CLIENT_IP"])) return $this->server["HTTP_CLIENT_IP"];
	// 	if (isset($this->server["REMOTE_ADDR"])) return $this->server["REMOTE_ADDR"];
	// 	return "0.0.0.0";
	// }

	/**
	 * Get the URI for the current request.
	 * @return string
	 */
	protected function uri()
	{
		// determine URI from Request
		$uri = isset($this->server["REQUEST_URI"]) ? $this->server["REQUEST_URI"] : 
			(isset($this->server["PATH_INFO"]) ? $this->server["PATH_INFO"] : 
				(isset($this->server["PHP_SELF"]) ? $this->server["PHP_SELF"] : 
					(isset($this->server["REDIRECT_URL"]) ? $this->server["REDIRECT_URL"] : "")));
		
		// remove unnecessarily slashes, like doubles and leading
		$uri = preg_replace("|//+|", "/", $uri);
		$uri = ltrim($uri, "/");
		// remove get params
		if (strpos($uri, "?") !== false) {
			$e = explode("?", $uri, 2);
			$uri = $e[0];
		}
		// $uri = trim($uri, '/');
		// add / only on empty URI - not good, because this will not work: 
		// 		Route::uri('(<controller>(/<action>(/<param>*)))', function ($params) {
		// since we have no "/", this is OK, but it's more complicated:
		//		Route::uri('(/)(<controller>(/<action>(/<param>*)))', function ($params) {
		//
		// if (!$uri) $uri = '/';

		return $uri;
	}


}
