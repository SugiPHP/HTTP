<?php
/**
 * Response Interface
 *
 * @package SugiPHP.HTTP
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

namespace SugiPHP\HTTP;

interface ResponseInterface
{
	/**
	 * Sends the response headers and the content (body)
	 *
	 * @return string
	 */
	public function send();

	/**
	 * Sets the response content.
	 *
	 * @param string $content
	 */
	public function setContent($content);

	/**
	 * Returns current content that will be send in response.
	 *
	 * @return string
	 */
	public function getContent();

	/**
	 * Sets the status code of the response.
	 * If the $text is null then the default status text will be send.
	 *
	 * @param integer $statusCode
	 * @param string $text
	 */
	public function setStatusCode($statusCode, $text = null);

	/**
	 * Returns the status code of the response.
	 *
	 * @return integer
	 */
	public function getStatusCode();

	/**
	 * Includes custom header to the response.
	 *
	 * @param string $header
	 * @param string $value
	 */
	public function setHeader($header, $value);

	/**
	 * Replaces all headers with the given
	 *
	 * @param array $headers
	 */
	public function setHeaders(array $headers);

	/**
	 * Returns a previously set (or default) header
	 *
	 * @param string $header
	 *
	 * @return string|null Returns NULL if the header is not set.
	 */
	public function getHeader($header);

	/**
	 * Returns all headers.
	 *
	 * @return array
	 */
	public function getHeaders();
}
