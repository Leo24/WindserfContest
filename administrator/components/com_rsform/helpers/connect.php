<?php
/**
* @version 1.4.0
* @package RSform!Pro 1.4.0
* @copyright (C) 2007-2011 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

function RSFormProConnect($url, $data, $params=array())
{
	$url_info 	= parse_url($url);
	$useragent 	= _RSFORM_PRODUCT.'/'._RSFORM_VERSION.'R'._RSFORM_REVISION;
	$timeout	= isset($params['timeout']) ? (int) $params['timeout'] : 10;
	$method		= isset($params['method']) ? strtoupper($params['method']) : 'POST';
	
	if (isset($url_info['host']) && $url_info['host'] == 'localhost')
		$url_info['host'] = '127.0.0.1';
	
	// cURL
	if (extension_loaded('curl'))
	{
		// Init cURL
		$ch = @curl_init();
		
		if ($method == 'GET' && $data)
			$url .= (strpos($url, '?') === false ? '?' : '&').$data;
		elseif ($method == 'POST')
			@curl_setopt($ch, CURLOPT_POST, true);
		
		// Set options
		@curl_setopt($ch, CURLOPT_URL, $url);
		@curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		@curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		@curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
		@curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		@curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		@curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		@curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		
		// Set timeout
		@curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		
		// Grab data
		@curl_exec($ch);
		
		// Clean up
		@curl_close($ch);
		
		return true;
	}
	
	// fsockopen
	if (function_exists('fsockopen'))
	{
		$errno  = 0;
		$errstr = '';

		$port = $url_info['scheme'] == 'https' ? 443 : 80;
		$ssl  = $url_info['scheme'] == 'https' ? 'ssl://' : '';
	
		// Set timeout
		$fsock = @fsockopen($ssl.$url_info['host'], $port, $errno, $errstr, $timeout);
		
		if ($fsock)
		{
			// Set timeout
			@stream_set_blocking($fsock, 1);
			@stream_set_timeout($fsock, $timeout);
			
			if ($method == 'GET')
			{
				if (!isset($url_info['query']))
					$url_info['query'] = '';
				if ($data)
					$url_info['query'] .= ($url_info['query'] ? '&' : '').$data;
			}
			
			@fwrite($fsock, $method.' '.$url_info['path'].(!empty($url_info['query']) ? '?'.$url_info['query'] : '').' HTTP/1.1'."\r\n");
			@fwrite($fsock, 'Host: '.$url_info['host']."\r\n");
			@fwrite($fsock, "User-Agent: ".$useragent."\r\n");
			if ($method == 'POST')
			{
				@fwrite($fsock, "Content-Type: application/x-www-form-urlencoded\r\n");
				@fwrite($fsock, "Content-Length: ".strlen($data)."\r\n");
			}
			@fwrite($fsock, 'Connection: close'."\r\n");
			@fwrite($fsock, "\r\n");
			
			if ($method == 'POST')
				@fwrite($fsock, $data);
			
			// Clean up
			@fclose($fsock);
			
			return true;
		}
	}
	
	return false;
}