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
// no direct access
defined('_JEXEC') or die('Restricted access');

// Import Joomla! libraries

class oseMscViewProfile extends oseMscView
{
    function display($tpl = null) {
    	$tmpl = JRequest::getVar('tmpl');
    	if (empty($tmpl))
    	{
    		JRequest::setVar('tmpl', 'component');
    	}
    	if (JOOMLA16==false)
    	{
    		require_once(OSEMSC_B_PATH.DS.'helpers'.DS."extLanguage.php");
    		oseHTML :: script(OSEMSC_F_URL.'/libraries/joomla.core.js', '1.5');
    		$strings = oseJson::encode(oseText::jsStrings());
    		$document = JFactory::getDocument();
    		$document->addScriptDeclaration('(function(){var strings='.$strings.';Joomla.JText.load(strings)})()');
     	}	
		$com = OSECPU_PATH_JS.'/com_ose_cpu/extjs';
		oseHTML :: initScript();
		oseHTML :: script($com.'/ose/app.msg.js', '1.5');
		oseHTML :: script(OSEMSC_F_URL.'/libraries/init.js', '1.5');
		require_once(OSEMSC_B_PATH.DS.'helpers'.DS."extLanguage.php");
		
		$OSESoftHelper= new OSESoftHelper();
		$footer= $OSESoftHelper -> renderOSETM();
		$this->assignRef('footer', $footer);
		$preview_menus= $OSESoftHelper -> getPreviewMenus();
		$this->assignRef('preview_menus', $preview_menus);
		$this->assignRef('OSESoftHelper', $OSESoftHelper);
		
		$title = JText :: _('OSE Membership™ Profile Management');
		$this->assignRef('title', $title);
		
		parent::display($tpl);
    }
}
?>