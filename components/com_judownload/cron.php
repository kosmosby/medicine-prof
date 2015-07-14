<?php
/**
 * ------------------------------------------------------------------------
 * JUDownload for Joomla 2.5, 3.x
 * ------------------------------------------------------------------------
 *
 * @copyright      Copyright (C) 2010-2015 JoomUltra Co., Ltd. All Rights Reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @author         JoomUltra Co., Ltd
 * @website        http://www.joomultra.com
 * @----------------------------------------------------------------------@
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

########################################
##### Configuration options.
########################################


$host = 'localhost';

########################################

if (stripos($host, 'http://') !== false || stripos($host, 'https://') !== false)
{
	return;
}

$fp = @fsockopen($host, 80, $errorNum, $errorStr);

if (!$fp)
{
	echo 'There was an error connecting to the site';
	exit;
}

function connect($fp, $host, $url)
{
	$request = "GET /" . $url . " HTTP/1.1\r\n";
	$request .= "Host: " . $host . "\r\n";
	$request .= "Connection: Close\r\n\r\n";
	fwrite($fp, $request);
}

connect($fp, $host, 'server/joomultra3/index.php?option=com_judownload&task=cron');

fclose($fp);

echo "Cronjob processed.\r\n";

return;
