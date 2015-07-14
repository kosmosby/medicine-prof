<?php/**  * @version     4.0 +  * @package        Open Source Membership Control - com_osemsc  * @subpackage    Open Source Access Control - com_osemsc  * @author        Open Source Excellence {@link http://www.opensource-excellence.co.uk}  * @author        EasyJoomla {@link http://www.easy-joomla.org Easy-Joomla.org}  * @author        SSRRN {@link http://www.ssrrn.com}  * @author        Created on 15-Sep-2008  * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html  *  *  *  This program is free software: you can redistribute it and/or modify  *  it under the terms of the GNU General Public License as published by  *  the Free Software Foundation, either version 3 of the License, or  *  (at your option) any later version.  *  *  This program is distributed in the hope that it will be useful,  *  but WITHOUT ANY WARRANTY; without even the implied warranty of  *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the  *  GNU General Public License for more details.  *  *  You should have received a copy of the GNU General Public License  *  along with this program.  If not, see <http://www.gnu.org/licenses/>.  *  @Copyright Copyright (C) 2010- ... Open Source Excellence*/
defined('_JEXEC') or die("Direct Access Not Allowed");
class oseMscViewLevels extends oseMscView
{
    function display($tpl = null)
    {
        JHTML::stylesheet('style.css', 'administrator/components/com_osemsc/assets/css/');
        JToolBarHelper::title(JText::_('OSE Joomla Membership Control Manager')         	.' <small><small>[ ' . JText::_('version') . ' ]</small></small>', 'logo');				$list = $this->get('Items');
        $this->assignRef('list', $list);        		oseHTML::initScript();		$com = OSECPU_PATH_JS.'/com_ose_cpu/extjs';				oseHTML::script($com.'/grid/SearchField.js');		oseHTML::script($com.'/ose/app.msg.js');				$objs = $this->getAddons();		        $this->assignRef('objs', $objs);        
        parent::display($tpl);
    }        function getAddons()    {    	return oseMscAddon::getAddonList('level',true,null,'obj');    }
}
