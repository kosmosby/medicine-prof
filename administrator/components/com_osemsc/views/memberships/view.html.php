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
class oseMscViewMemberships extends oseMscView {
	function display($tpl= null) {
		$tmpl = JRequest::getVar('tmpl');
		if (empty($tmpl))
		{
			JRequest::setVar('tmpl', 'component');
		}
		
		$list= $this->get('Items');
		$this->assignRef('list', $list);
		$com= OSECPU_PATH_JS.'/com_ose_cpu/extjs';
		
		$this->loadViewJs('jquery');
		$this->loadGridJs();
		$this->loadTinyMCE();
		$this->loadMultiSelect();
		oseHTML :: stylesheet($com.'/fileupload/field.css', '1.5');
		oseHTML :: script($com."/fileupload/field.js", '1.5');
		$objs= $this->getAddons('panel');
		$this->assignRef('objs', $objs);
		$content_objs= $this->getAddons('content');
		$this->assignRef('content_objs', $content_objs);

		
		$OSESoftHelper= new OSESoftHelper();
		$footer= $OSESoftHelper -> renderOSETM();
		$this->assignRef('footer', $footer);
		$preview_menus= $OSESoftHelper -> getPreviewMenus();
		$this->assignRef('preview_menus', $preview_menus);
		$this->assignRef('OSESoftHelper', $OSESoftHelper);
		
		$title = JText :: _('OSE Membership™ Management System');
		$this->assignRef('title', $title);
		parent :: display($tpl);
	}
	function getAddons($type) {
		return oseMscAddon :: getAddonList($type, true, null, 'obj');
	}
}