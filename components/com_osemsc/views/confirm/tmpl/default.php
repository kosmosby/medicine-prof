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
oseHTML::script(OSEMSCFOLDER . "/views/confirm/js/js.confirm.js", '1.5');
?>
<?php
if ($this->menuParams->get('show_page_heading')) {
?>
		<div class='componentheading <?php echo $this->menuParams->get('pageclass_sfx'); ?>'><?php echo $this->menuParams->get('page_heading'); ?></div>
<?php
}
?>
<?php
if ($this->orderInfo['ACK'] == 'Success' && $this->orderInfo['PAYMENTSTATUS'] == 'Completed') {
?>
<table id ="orderInfo">
<tr>
<td colspan='2'>
<?php echo JText::_("Congratulations! Your order has been proceeded successfully!"); ?>
</td>
</tr>
<tr>
<td>
<?php echo JText::_("Payment Status"); ?>
</td>
<td>
<?php echo urldecode($this->orderInfo['PAYMENTSTATUS']); ?>
</td>
</tr>
<tr>
<td>
<?php echo JText::_("Amount"); ?>
</td>
<td>
<?php echo urldecode($this->orderInfo['CURRENCYCODE']) . " " . urldecode($this->orderInfo['AMT']); ?>
</td>
</tr>
</table>
<?PHP
} elseif ($this->orderInfo['ACK'] == 'Success')
{
?>
<table id ="orderInfo">
<tr>
<td colspan='2'>
<?php echo JText::_("Congratulations! Your order has been proceeded successfully!"); ?>
</td>
</tr>
<tr>
<td>
<?php echo JText::_("Profile Status"); ?>
</td>
<td>
<?php echo 'Created'; ?>
</td>
</tr>
<tr>
<td>
<?php echo JText::_("Profile ID"); ?>
</td>
<td>
<?php echo urldecode($this->orderInfo['PROFILEID']); ?>
</td>
</tr>
</table>
<?php
} elseif ($this->orderInfo['ACK'] == 'Success' && $this->orderInfo['PROFILESTATUS'] == 'PendingProfile') {
?>
<table id ="orderInfo">
<tr>
<td colspan='2'>
<?php echo JText::_("Congratulations! Your order has been proceeded successfully!"); ?><br />
<?php echo JText::_("Your membership will be activated in a few minutes when charge successfully"); ?>
</td>
</tr>
<tr>
<td>
<?php echo JText::_("Profile Status"); ?>
</td>
<td>
<?php echo urldecode($this->orderInfo['PROFILESTATUS']); ?>
</td>
</tr>
<tr>
<td>
<?php echo JText::_("Profile ID"); ?>
</td>
<td>
<?php echo urldecode($this->orderInfo['PROFILEID']); ?>
</td>
</tr>
</table>
<?php
} else {
?>
<table id ="orderInfo">
<tr>
<td colspan='2'>
<?php echo JText::_("Payment was processed unsuccessfully!"); ?>
</td>
</tr>
<tr>
<td>
<?php echo JText::_("Payment Status"); ?>
</td>
<td>
<?php echo 'Failed'; ?>
</td>
</tr>
<tr>
<td>
<?php echo JText::_("Reason"); ?>
</td>
<td>
<?php echo urldecode($this->orderInfo['L_LONGMESSAGE0']); ?>
</td>
</tr>
<?php
}
?>
</table>
<?php include(JPATH_COMPONENT . DS . "views" . DS . "footer.php"); ?>
