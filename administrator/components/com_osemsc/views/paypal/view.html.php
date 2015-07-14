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
class oseMscViewPaypal extends oseMscView {
	function display($tpl= null) {
		JToolBarHelper :: title(JText :: _('OSE Membership™ Update Member Info.').' <small><small>[ '.OSEMSCVERSION.' ]</small></small>', 'logo');
		
		$doc = JFactory::getDocument();
		
		// init table
		$db= & JFactory :: getDBO();
		// ACL Info;
		$tables= $db->getTableList();
		$jConfig = JFactory::getConfig();
		
		
		$this->checkFixTable();

		//$doc->addScript(JURI::root().'media/system/js/core.js');
		$com= OSECPU_PATH_JS.'/com_ose_cpu/extjs';
		oseHtml::script('media/system/js/core.js');
		oseHTML :: initScript();
		oseHTML :: script($com.'/ose/app.msg.js');
		oseHTML :: script($com.'/ose/func.js');
		$com= OSECPU_PATH_JS.'/com_ose_cpu/extjs';
		
		$this->items		= $this->get('Items');
		$this->modules		= $this->get('Modules');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		
		parent::display($tpl);
	}
	
	protected function checkFixTable()
	{
		// init table
		$db= & JFactory :: getDBO();
		// ACL Info;
		$tables= $db->getTableList();
		$jConfig = JFactory::getConfig();
	
		if(!in_array(str_replace('#__', $jConfig->get('dbprefix'), '#__osemsc_order_fix'),$tables))
		{
			$query = "CREATE TABLE IF NOT EXISTS `#__osemsc_order_fix` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `order_id` int(11) NOT NULL,
	  `order_item_id` int(11) NOT NULL DEFAULT '0',
	  `member_id` int(11) NOT NULL DEFAULT '0',
	  `msc_id` int(11) NOT NULL DEFAULT '0',
	  `user_id` int(11) NOT NULL DEFAULT '0',
	  `msc_option` varchar(20) DEFAULT NULL,
	  `hasParams` int(1) NOT NULL DEFAULT '1',
	  `msc_total` int(3) NOT NULL DEFAULT '1',
	  `status` varchar(20) DEFAULT NULL,
	  `payment_method` varchar(32) DEFAULT NULL COMMENT 'pp for paypal, gco for Google Checkout, 2co for 2CheckOut, authorize for Authorize.Net',
	  `create_date` datetime DEFAULT NULL,
	  `payment_mode` varchar(3) DEFAULT NULL COMMENT 'a for automaticall, m for manually',
	  `params` text,
	  `email` varchar(100) DEFAULT NULL,
	  PRIMARY KEY (`id`),
	  KEY `user_id` (`member_id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8";
			$db->setQuery($query);
			$db->query();
	
			$fields= $db->getTableFields('#__osemsc_member');
			if(!isset($fields['#__osemsc_member']['virtual_status']))
			{
				$query= "ALTER TABLE `#__osemsc_member` ADD  `virtual_status` int(1) NOT NULL default '1';";
				$db->setQuery($query);
				$db->query();
					
				$query = " UPDATE `#__osemsc_member`"
				." SET `virtual_status` = `status`"
				;
				$db->setQuery($query);
				$db->query();
			}
		}
	
		$fields= $db->getTableFields('#__osemsc_order_fix');
		if(!isset($fields['#__osemsc_order_fix']['email']))
		{
			$query= "ALTER TABLE `#__osemsc_order_fix` ADD  `email` varchar(100) NULL;";
			$db->setQuery($query);
			$db->query();
		}
	}
}