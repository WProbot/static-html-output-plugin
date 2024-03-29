<?php
/**
 * @package WP Static HTML Output
 *
 * Copyright (c) 2011 Leon Stafford
 */

/**
 * Url request class
 */
class StaticHtmlOutput_UrlRequest
{
	/**
	 * The URI resource
	 * @var string
	 */
	protected $_url;
	
	/**
	 * The raw response from the HTTP request
	 * @var string
	 */
	protected $_response;

	/**
	 * Constructor
	 * @param string $url URI resource
	 */
	public function __construct($url)
	{
		$this->_url = filter_var($url, FILTER_VALIDATE_URL);
		$response = wp_remote_get($this->_url,array('timeout'=>300)); //set a long time out
		$this->_response = (is_wp_error($response) ? '' : $response);
	}
	
	/**
	 * Returns the sanitized url
	 * @return string
	 */
	public function getUrl()
	{
		return $this->_url;
	}
	
	/**
	 * Allows to override the HTTP response body
	 * @param string $newBody
	 * @return void
	 */
	public function setResponseBody($newBody)
	{
		if (is_array($this->_response))
		{
			$this->_response['body'] = $newBody;
		}
	}
	
	/**
	 * Returns the HTTP response body
	 * @return string
	 */
	public function getResponseBody()
	{
		return isset($this->_response['body']) ? $this->_response['body'] : '';
	}
	
	/**
	 * Returns the content type
	 * @return string
	 */
	public function getContentType()
	{
		return isset($this->_response['headers']['content-type']) ? $this->_response['headers']['content-type'] : null;
	}
	
	/**
	 * Checks if content type is html
	 * @return bool
	 */
	public function isHtml()
	{
		return stripos($this->getContentType(), 'html') !== false;
	}
	
	/**
	 * Extracts the list of unique urls
	 * @param string $baseUrl Base url of site. Used to extract urls that relate only to the current site.
	 * @return array
	 */
	public function extractAllUrls($baseUrl)
	{
		$allUrls = array();
		
		if ($this->isHtml() && preg_match_all('/' . str_replace('/', '\/', $baseUrl) . '[^"\'#\? ]+/i', $this->_response['body'], $matches))
		{
			$allUrls = array_unique($matches[0]);
		}
		
		return $allUrls;
	}
	
	/**
	 * Replaces base url
	 * @param string $oldBaseUrl
	 * @param string $newBaseUrl
	 * @return void
	 */
	public function replaceBaseUlr($oldBaseUrl, $newBaseUrl)
	{
		if ($this->isHtml())
		{
			$responseBody = str_replace($oldBaseUrl, $newBaseUrl, $this->getResponseBody());
			$responseBody = str_replace('<head>', "<head>\n<base href=\"" . esc_attr($newBaseUrl) . "\" />\n", $responseBody);
			$this->setResponseBody($responseBody);
		}
	}
}
