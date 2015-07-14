<?php
/**
  * @version     4.0 +
  * @package        Open Source Membership Control - com_osemsc
  * @subpackage    Open Source Access Control - com_osemsc
  * @author        Open Source Excellence {@link 
http://www.opensource-excellence.co.uk}
  * @author        EasyJoomla {@link http://www.easy-joomla.org 
Easy-Joomla.org}
  * @author        SSRRN {@link http://www.ssrrn.com}
  * @author        Created on 15-Sep-2008
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
  *  @Copyright Copyright (C) 2010- ... Open Source Excellence
*/

defined('_JEXEC') or die("Direct Access Not Allowed");

oseHTML::script(oseMscMethods::getJsModPath('level','level'),'1.5');
oseHTML::script(oseMscMethods::getJsModPath('levels','level'),'1.5');


oseHTML::script("administrator/".OSEMSCFOLDER."/views/levels/js/js.levels.js",'1.5');

//oseMscAddon::loadAddons($this->objs);
?>

<script type="text/javascript">
Ext.onReady(function(){
	<?php //echo oseMscAddon::addAddons('oseMemMsc.edit.tabs',$this->objs);?>
	//oseMemMsc.edit.tabs.doLayout();
	
	
});

</script>

<div id="osemsc-levels"> </div>

