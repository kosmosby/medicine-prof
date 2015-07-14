<?php
/**------------------------------------------------------------------------
thefactory - The Factory Class Library - v 2.0.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
 * @build: 01/04/2012
 * @package: thefactory
 * @subpackage: themes
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class JTheFactoryThemesController extends JTheFactoryController
{
    var $name='Themes';
    var $_name='Themes';
	var $modulename = 'Themes';

	function __construct()
	{
		$lang = JFactory::getLanguage();
		$lang->load('thefactory.' . strtolower($this->modulename));
		parent::__construct();
	}
    function listThemes()
    {
        $themefiles=JTheFactoryThemesHelper::getThemeList();
        $themes=array();
        foreach($themefiles as $themefile)
            $themes[]=JTheFactoryThemesHelper::getThemeHeader($themefile);
        
        $view=$this->getView('themes');
        $view->assignRef('themes',$themes);
        $view->display('list');
    }
    function cloneTheme()
    {
        $themename=JRequest::getVar('theme');
        if (is_array($themename)) $themename=$themename[0];        
        $themedir=JTheFactoryThemesHelper::getThemeDir();
        if (!JTheFactoryThemesHelper::isThemeFile($themedir.DS.$themename.DS."theme.xml"))
        {
            JError::raiseWarning(420,JText::_("FACTORY_THEME_IS_NOT_VALID"));
            $this->setRedirect("index.php?option=".APP_EXTENSION."&task=themes.listthemes");
            return true;
        }
        $theme=JTheFactoryThemesHelper::getThemeHeader($themedir.DS.$themename.DS."theme.xml");
        
        $view=$this->getView('themes');
        $view->assign('themename',$themename);
        $view->assignRef('theme',$theme);
        $view->display('clone');
    }
    function doclone()
    {
        $themename=JRequest::getVar('themename');
        $newname=JRequest::getString('name');
        $newfolder=JRequest::getString('themefolder');
        $newdescription=JRequest::getString('description');
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');
        
        if (is_array($themename)) $themename=$themename[0];        
        $themedir=JTheFactoryThemesHelper::getThemeDir();
        if (!JTheFactoryThemesHelper::isThemeFile($themedir.DS.$themename.DS."theme.xml"))
        {
            JError::raiseWarning(420,JText::_("FACTORY_THEME_IS_NOT_VALID"));
            $this->setRedirect("index.php?option=".APP_EXTENSION."&task=themes.listthemes");
            return true;
        }
        if (JFolder::exists($themedir.DS.$newfolder))
        {
            JError::raiseWarning(420,JText::_("FACTORY_FOLDER_ALREADY_EXISTS")." ".$newfolder);
            $this->setRedirect("index.php?option=".APP_EXTENSION."&task=themes.listthemes");
            return true;
        }
        JFolder::copy($themedir.DS.$themename,$themedir.DS.$newfolder);     
        $manifest_file=$themedir.DS.$newfolder.DS."theme.xml";
        $xml = JFactory::getXML($manifest_file);
        unset($xml['priority']);
        $xml['folder']=$newfolder;
        $xml->name=$newname;
        $xml->description=$newdescription;
        $xml->creationDate=date('Y-m-d');
        
        $xmlcontent="<?xml version=\"1.0\" encoding=\"utf-8\"?>".
                $xml->asFormattedXML();
        JFile::write($manifest_file,$xmlcontent);
           
        $this->setRedirect("index.php?option=".APP_EXTENSION."&task=themes.listthemes",JText::_("FACTORY_THEME_SAVED"));
    }
    function cancel()
    {
        $this->setRedirect("index.php?option=".APP_EXTENSION."&task=themes.listthemes");
    }
    function delete()
    {
        $themename=JRequest::getVar('theme');
        if (is_array($themename)) $themename=$themename[0];        
        $themedir=JTheFactoryThemesHelper::getThemeDir();
        if (!JTheFactoryThemesHelper::isThemeFile($themedir.DS.$themename.DS."theme.xml"))
        {
            JError::raiseWarning(420,JText::_("FACTORY_THEME_IS_NOT_VALID"));
            $this->setRedirect("index.php?option=".APP_EXTENSION."&task=themes.listthemes");
            return true;
        }
        if (JTheFactoryThemesHelper::isCoreTheme($themedir.DS.$themename.DS."theme.xml"))
        {
            JError::raiseWarning(420,JText::_("FACTORY_THEME_IS_CORE_AND_CAN_NOT_BE_DELETED"));
            $this->setRedirect("index.php?option=".APP_EXTENSION."&task=themes.listthemes");
            return true;
        }
        
        jimport('joomla.filesystem.folder');
        JFolder::delete($themedir.DS.$themename); 
        $this->setRedirect("index.php?option=".APP_EXTENSION."&task=themes.listthemes",JText::_("FACTORY_THEME_DELETED"));
    }
    function upload()
    {
        $view=$this->getView('themes');
        $view->display('upload');
    }
    function doupload()
    {
		// Get the uploaded file information
	   $userfile = JRequest::getVar('theme', null, 'files', 'array');
       $lang= JFactory::getLanguage();
       $lang->load('com_installer');
		// Make sure that file uploads are enabled in php
		if (!(bool) ini_get('file_uploads')) {
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLFILE'));
            $this->setRedirect("index.php?option=".APP_EXTENSION."&task=themes.upload");
			return true;
		}

		// Make sure that zlib is loaded so that the package can be unpacked
		if (!extension_loaded('zlib')) {
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLZLIB'));
            $this->setRedirect("index.php?option=".APP_EXTENSION."&task=themes.upload");
			return true;
		}

		// If there is no uploaded file, we have a problem...
		if (!is_array($userfile)) {
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_NO_FILE_SELECTED'));
            $this->setRedirect("index.php?option=".APP_EXTENSION."&task=themes.upload");
			return true;
		}

		// Check if there was a problem uploading the file.
		if ($userfile['error'] || $userfile['size'] < 1) {
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLUPLOADERROR'));
            $this->setRedirect("index.php?option=".APP_EXTENSION."&task=themes.upload");
			return true;
		}         
		// Build the appropriate paths
		$config		= JFactory::getConfig();
		$tmp_dest	= $config->get('tmp_path') . '/' . $userfile['name'];
		$tmp_src	= $userfile['tmp_name'];

		// Move uploaded file
		jimport('joomla.filesystem.file');
		$uploaded = JFile::upload($tmp_src, $tmp_dest);
  		jimport('joomla.installer.helper');

		$package = JTheFactoryThemesHelper::unpackTheme($tmp_dest); 
		// Was the package unpacked?
		if (!$package) {
			JError::raiseWarning('', JText::_('COM_INSTALLER_UNABLE_TO_FIND_INSTALL_PACKAGE'));
            $this->setRedirect("index.php?option=".APP_EXTENSION."&task=themes.upload");
			return true;
		} 
        JTheFactoryThemesHelper::installTheme($package['dir']);
		if (!is_file($package['packagefile'])) {
			$config = JFactory::getConfig();
			$package['packagefile'] = $config->get('tmp_path') . '/' . $package['packagefile'];
		}

		JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);                                  
        $this->setRedirect("index.php?option=".APP_EXTENSION."&task=themes.listthemes",JText::_("FACTORY_THEME_INSTALLED"));
    }
    function setDefault()
    {
        $themename=JRequest::getVar('theme');
        if (is_array($themename)) $themename=$themename[0];        
        
        JTheFactoryThemesHelper::setCurrentTheme($themename);
                
        $this->setRedirect("index.php?option=".APP_EXTENSION."&task=themes.listthemes",JText::_("FACTORY_CURRENT_THEME_SET"));
    }
}
 
