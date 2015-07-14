<?php
/**
 * @version 1.6
 * @package JomWALL -Joomla
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/

defined('_JEXEC') or die('Restricted access');
// Return content of the given url
function getContentFromUrl($url)
{
	if (!$url)
		return false;

	$response = '';
	if (function_exists('curl_init'))
	{		
		$ch =curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($ch);
		curl_close($ch);
	}
	else
	{
		$fp = fsockopen($url, 80, $errno, $errstr, 30);
		if (!$fp) {
			return false;
		} else {
		    $header  = 'GET / HTTP/1.1\r\n';
		    $header .= 'Host: ' . $url . '\r\n';
		    $header .= 'Connection: Close\r\n\r\n';
		    fwrite($fp, $header);
		    while (!feof($fp)) {
		        $response .= fgets($fp, 128);
		    }
		    fclose($fp);
		}
	}
	return $response;
}
