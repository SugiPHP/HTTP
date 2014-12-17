<?php
/**
 * Response Class
 *
 * @package SugiPHP.HTTP
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\HTTP;

class Response implements ResponseInterface
{
	/**
	 * List of HTTP status codes.
	 *
	 * @var array
	 */
	public static $statusCodes = [
		200 => "OK",
		201 => "Created",
		202 => "Accepted",
		203 => "Non-Authoritative",
		204 => "No Content",
		205 => "Reset Content",
		206 => "Partial Content",
		300 => "Multiple Choices",
		301 => "Moved Permanently",
		302 => "Found",
		303 => "See Other",
		304 => "Not Modified",
		305 => "Use Proxy",
		307 => "Temporary Redirect",
		400 => "Bad Request",
		401 => "Unauthorized",
		402 => "Payment Required",
		403 => "Forbidden",
		404 => "Not Found",
		405 => "Method Not Allowed",
		406 => "Not Acceptable",
		407 => "Proxy Authentication Required",
		408 => "Request Time-out",
		409 => "Conflict",
		410 => "Gone",
		411 => "Length Required",
		412 => "Precondition Failed",
		413 => "Request Entity Too Large",
		414 => "Request-URI Too Long",
		415 => "Unsupported Media Type",
		416 => "Requested range unsatisfiable",
		417 => "Expectation failed",
		500 => "Internal Server Error",
		501 => "Not Implemented",
		502 => "Bad Gateway or Proxy Error",
		503 => "Service Unavailable",
		504 => "Gateway Time-out",
		505 => "HTTP Version not supported",
	];

	/**
	 * HTTP status code to send.
	 *
	 * @var integer
	 */
	protected $statusCode = 200;

	/**
	 * Status text that is send along with the status code.
	 *
	 * @var string
	 */
	protected $statusText = "OK";

	/**
	 * HTTP headers to send.
	 *
	 * @var array
	 */
	protected $headers = array();

	/**
	 * The content that will be send in response.
	 *
	 * @var string
	 */
	protected $content = "";

	/**
	 * Protocol version
	 *
	 * @var string 1.0 or 1.1
	 */
	protected $version = "1.0";

	public function __toString()
	{
		$headers = "";
		foreach ($this->headers as $header => $value) {
			$headers .= "$header: $value\r\n";
		}

		return "HTTP/{$this->getVersion()} {$this->getStatusCode()} {$this->getStatusText()}\r\n".
			$headers."\r\n".
			$this->content;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setContent($content)
	{
		$this->content = (string) $content;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setStatusCode($statusCode, $text = null)
	{
		$this->statusCode = (int) $statusCode;
		$this->statusText = is_null($text) ? (isset(static::$statusCodes[$statusCode]) ? static::$statusCodes[$statusCode] : "") : $text;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getStatusCode()
	{
		return $this->statusCode;
	}

	/**
	 * Returns status text corresponding to the status code of the response.
	 *
	 * @return string
	 */
	public function getStatusText()
	{
		return $this->statusText;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setHeader($header, $value)
	{
		$this->headers[$header] = $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setHeaders(array $headers)
	{
		$this->headers = $headers;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeader($header)
	{
		return empty($this->headers[$header]) ? null : $this->headers[$header];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeaders()
	{
		return $this->headers;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return Response
	 */
	public function send()
	{
		$this->sendHeaders();
		$this->sendContent();

		return $this;
	}

	/**
	 * Sends all headers.
	 *
	 * @return Response
	 */
	public function sendHeaders()
	{
		if (headers_sent()) {
			return $this;
		}

		// Remove previously set headers with header() function
		// header_remove();

		// Send protocol version and status code
		header("HTTP/{$this->getVersion()} {$this->getStatusCode()} {$this->getStatusText()}", true, $this->getStatusCode());

		foreach ($this->headers as $header => $value) {
			header($header . ": " . $value);
		}

		// $this->sendCookies();
		return $this;
	}

	/**
	 * Sends content of the response.
	 *
	 * @return Response
	 */
	public function sendContent()
	{
		echo $this->getContent();

		return $this;
	}

	/**
	 * Protocol version that is sent in the first header along with status code.
	 *
	 * @return string 1.0 or 1.1
	 */
	public function getVersion()
	{
		return $this->version;
	}

	/**
	 * Sets protocol version.
	 *
	 * @param string $version 1.0 or 1.1
	 *
	 * @return Response
	 */
	public function setVersion($version)
	{
		$this->version = $version;

		return $this;
	}
}
