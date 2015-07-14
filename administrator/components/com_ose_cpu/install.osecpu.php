<?php
/**
 * @version     4.0 +
 * @package     Open Source Excellence Central Processing Units
 * @author      Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
 * @author      Created on 17-May-2011
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @Copyright Copyright (C) 2010- Open Source Excellence (R)
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
 */
defined('_JEXEC') or die("Direct Access Not Allowed");
jimport('joomla.application.component.controller');
jimport('joomla.application.component.model');
jimport('joomla.installer.installer');
jimport('joomla.installer.helper');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.archive');
jimport('joomla.filesystem.path');
/**
 * Main installer
 */
function com_install() {
	//-- common images
	$img_OK = '<img src="images/publish_g.png" />';
	$img_WARN = '<img src="images/publish_y.png" />';
	$img_ERROR = '<img src="images/publish_r.png" />';
	$BR = '<br />';
	//--install...
	$db = &JFactory::getDBO();
	//
	$query = "CREATE TABLE IF NOT EXISTS `#__ose_app` (
  	`id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(200) DEFAULT NULL,
 	`title` varchar(100) DEFAULT NULL,
    `activated` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
	$db->setQuery($query);
	if (!$db->query()) {
		echo $img_ERROR . JText::_('Unable to create table') . $BR;
		echo $db->getErrorMsg();
		return false;
	}
	$query = "CREATE TABLE IF NOT EXISTS `#__ose_email` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `app` varchar(20) NOT NULL,
  `subject` text,
  `body` text,
  `type` varchar(20) DEFAULT NULL COMMENT '1 for email, 2 for receipt',
  `params` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
	$db->setQuery($query);
	if (!$db->query()) {
		echo $img_ERROR . JText::_('Unable to create table') . $BR;
		echo $db->getErrorMsg();
		return false;
	}
	$query = "SELECT COUNT(*) FROM `#__ose_email`";
	$db->setQuery($query);
	$count = $db->loadResult();
	if ($count < 1) {
		$query = 'INSERT INTO `jos_ose_email` (`id`, `app`, `subject`, `body`, `type`, `params`) VALUES
				(1, \'credit\', \'Sales Receipt\', \'<div class="osereceipt">\r\n<div class="receipt-content">\r\n<table width="100%">\r\n<tbody>\r\n<tr>\r\n<td width="70%">\r\n<p><span class="invoice-header">INVOICE</span><br /><br /> <span class="date">Date: [order.date]</span><br /><br /><span class="invoice-number">Invoice # 2011-[order.order_id]</span></p>\r\n<p class="billing-info"><strong><span class="billing-header">Billing Address</span></strong><br /> [user.company]<br /> [user.address1]<br /> [user.city], [user.state], [user.postcode]<br /> [user.firstname] [user.lastname]</p>\r\n<p class="customer-ref">Customer Reference Number: <br /> [order.order_number]</p>\r\n</td>\r\n<td style="color: #666666;">&nbsp;YOUR LOGO HERE</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<br /> \r\n<table class="receipt-list" width="100%">\r\n<tbody>\r\n<tr class="rows">\r\n<td class="header" height="25px" valign="middle">Subscription Detail</td>\r\n</tr>\r\n<tr class="rows">\r\n<td class="items" height="70px" valign="middle">[order.itemlist]</td>\r\n</tr>\r\n<tr class="rows" align="right">\r\n<td class="subtotal" height="25px" valign="middle">SUBTOTAL EUR [order.subtotal]</td>\r\n</tr>\r\n<tr class="rows" align="right">\r\n<td class="subtotal" height="25px" valign="middle">SALES TAX EUR [order.gross_tax]</td>\r\n</tr>\r\n<tr class="rows" align="right">\r\n<td class="subtotal" height="25px" valign="middle">TOTAL EUR [order.total]</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<br /><br />\r\n<div style="text-align: center;"><span class="thank-you">Thank you for your business!</span></div>\r\n<br /><br />\r\n<div style="text-align: center;"><span class="slogan">YOUR SLOGAN HERE</span></div>\r\n</div>\r\n</div>\', \'receipt\', \'{"user.username":"user.username","user.name":"user.jname","user.email":"user.email","user.user_status":"user.block","user.firstname":"user.firstname","user.lastname":"user.lastname","user.company":"user.company","user.address1":"user.addr1","user.address2":"user.addr2","user.city":"user.city","user.state":"user.state","user.country":"user.country","user.postcode":"user.postcode","user.telephone":"user.telephone","order.order_id":"order.order_id","order.order_number":"order.order_number","order.order_status":"order.order_status","order.vat_number":"order.vat_number","order.subtotal":"order.subtotal","order.total":"order.total","order.gross_tax":"order.gross_tax","order.discount":"order.discount","order.itemlist":"order.itemlist","order.payment_method":"order.payment_method","order.date":"order.create_date","order.payment_mode":"order.payment_mode"}\');';
		$db->setQuery($query);
		if (!$db->query()) {
			echo $img_ERROR . JText::_('Unable to create table') . $BR;
			echo $db->getErrorMsg();
			return false;
		}
	}
}
