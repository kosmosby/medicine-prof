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

$current_url = JUri::current();
$current_url_arr = explode("/", $current_url);
$current_domain = $current_url_arr[2];
$current_domain = str_replace("www.", "", $current_domain);
?>

<fieldset class="adminform">
	<legend>JoomUltra Download</legend>
	<table width="100%" cellspacing="1" class="admintable">
		<tbody>
			<tr>
				<td rowspan="5" width="150px" class="center">
					<img src="components/com_judownload/assets/img/joomultra.png" alt="JoomUltra" /></td>
				<td style="width:70px">&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td><b>Author:</b></td>
				<td>JoomUltra</td>
			</tr>
			<tr>
				<td><b>Email:</b></td>
				<td>admin@joomultra.com</td>
			</tr>
			<tr>
				<td><b>Website:</b></td>
				<td><a href="http://www.JoomUltra.com">www.JoomUltra.com</a></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
			</tr>
		</tbody>
	</table>
</fieldset>