<?php

class Header
{
	
	const E403 = 403;
	const E404 = 404;
	
	const CONTENT_ENCODING_GZIP = 'gzip';
	
	public static $statuses = array(
		self::E403 => array (
			"HTTP/1.0 403 Permission Denied",
			"Status: 403 Permission Denied"
		),
		self::E404 => array(
			"HTTP/1.0 404 Not Found",
			"Status: 404 Not Found"
		)
	);
	
	public static function setStatus ($status)
	{
		if (headers_sent ())
		{
			return;
		}
		if (isset(self::$statuses[$status]))
		{
			foreach (self::$statuses[$status] as $text)
			{
				header($text);
			}
		}
	}
	
	/**
	 * 
	 * @param string $uri
	 */
	public static function redirect ($uri)
	{
		if (substr ($uri, 0, 7) != 'http://')
		{
			$uri = 'http://' . $_SERVER ['HTTP_HOST'] . $uri;
		}
		
		if (!headers_sent ())
		{
			header ("Location: $uri");
		}
		else
		{	
			echo '<script>window.location.href="'.$uri.'"</script>';
		}
	}
	
}