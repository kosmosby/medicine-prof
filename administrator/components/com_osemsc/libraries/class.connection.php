<?php
/**
  * @version     5.0 +
  * @package        Open Source Membership Control - com_osemsc
  * @subpackage    Open Source Access Control - com_osemsc
  * @author        Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
  * @author        Created on 15-Nov-2010
  * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
  *
  *
  *  This program is free software: you can redistribute it and/or modify
  *  it under the terms of the GNU General Public License as published by
  *  the Free Software Foundation, either version 3 of the License, or
  *  (at your option) any later version.
  *
  *  This program is distributed in the hope that it will be useful,
  *  but WITHOUT ANY WARRANTY; without even the implied warranty of
  *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  *  GNU General Public License for more details.
  *
  *  You should have received a copy of the GNU General Public License
  *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
  *  @Copyright Copyright (C) 2010- Open Source Excellence (R)
*/
defined('_JEXEC') or die("Direct Access Not Allowed");
if(!defined('_VALID_MOS') && !defined('_JEXEC'))
	die('Direct Access to '.basename(__FILE__).' is not allowed.');
/**
*
* @version $Id:connectionTools.class.php 431 2006-10-17 21:55:46 +0200 (Di, 17 Okt 2006) soeren_nb $
* @package VirtueMart
* @subpackage classes
* @copyright Copyright (C) 2004-2007 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.net
*/
/**
 * Provides general tools to handle connections (http, headers, ... )
 *
 * @author soeren
 * @since VirtueMart 1.1.0
 */
class OSECONNECTOR {
	//function to send xml request via fsockopen
	static function send_request_via_fsockopen($host, $path, $content,$contentType = 'xml') {
		$posturl= "ssl://".$host;
		$header= "Host: $host\r\n";
		$header .= "User-Agent: PHP Script\r\n";
		if($contentType == 'xml')
		{
			$header .= "Content-Type: text/xml\r\n";
		}
		elseif($contentType == 'urlencoded')
		{
			$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		}
		$header .= "Content-Length: ".strlen($content)."\r\n";
		$header .= "Connection: close\r\n\r\n";
		$fp= fsockopen($posturl, 443, $errno, $errstr, 30);
		if(!$fp) {
			$response= false;
		} else {
			error_reporting(E_ERROR);
			fputs($fp, "POST $path  HTTP/1.1\r\n");
			fputs($fp, $header.$content);
			//fwrite($fp, $header."\r\n".$content);
			$response= "";
			while(!feof($fp)) {
				$response= $response.fgets($fp, 128);
			}
			fclose($fp);
			error_reporting(E_ALL ^ E_NOTICE);
		}
		return $response;
	}
	//function to send xml request via curl
	static function send_request_via_curl($host, $path, $content) {
		$posturl= "https://".$host.$path;
		$ch= curl_init();
		curl_setopt($ch, CURLOPT_URL, $posturl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response= curl_exec($ch);
		return $response;
	}
	//function to parse Authorize.net response
	function parse_return($content) {
		$refId= OSECONNECTOR :: substring_between($content, '<refId>', '</refId>');
		$resultCode= OSECONNECTOR :: substring_between($content, '<resultCode>', '</resultCode>');
		$code= OSECONNECTOR :: substring_between($content, '<code>', '</code>');
		$text= OSECONNECTOR :: substring_between($content, '<text>', '</text>');
		$subscriptionId= OSECONNECTOR :: substring_between($content, '<subscriptionId>', '</subscriptionId>');
		$Status = OSECONNECTOR::substring_between($content,'<Status>','</Status>');

		if (!empty($Status))
		{
			return array ($refId, $resultCode, $code, $text, $subscriptionId, $Status);
		}
		else
		{
			return array($refId, $resultCode, $code, $text, $subscriptionId);
		}
			
	}
	//helper function for parsing response
	function substring_between($haystack, $start, $end) {
		if(strpos($haystack, $start) === false || strpos($haystack, $end) === false) {
			return false;
		} else {
			$start_position= strpos($haystack, $start) + strlen($start);
			$end_position= strpos($haystack, $end);
			return substr($haystack, $start_position, $end_position - $start_position);
		}
	}
}
?>