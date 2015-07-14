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
oseHTML::script(OSEMSCFOLDER . "/views/thankyou/js/js.thankyou.js", '1.5');
?>
<?php
if ($this->menuParams->get('show_page_heading')) {
?>
		<div class='componentheading <?php echo $this->menuParams->get('pageclass_sfx'); ?>'><?php echo $this->menuParams->get('page_heading'); ?></div>
<?php
}
?>
<table id ="orderInfo">
<tr>
<td colspan='2'>
<?php echo JText::_("CONGRATULATIONS_YOUR_ORDER_HAS_BEEN_PROCEEDED_SUCCESSFULLY"); ?>
</td>
</tr>
<tr>
<td>
<?php echo JText::_("PAYMENT_STATUS"); ?>
</td>
<td>
<?php echo urldecode($this->orderInfo->order_status); ?>
</td>
</tr>
<tr>
<td>
<?php echo JText::_("AMOUNT"); ?>
</td>
<td>
<?php echo urldecode($this->orderInfo->payment_currency) . " " . urldecode($this->orderInfo->payment_price); ?>
</td>
</tr>
</table>
<?php include(JPATH_COMPONENT . DS . "views" . DS . "footer.php"); ?>
