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

class JTheFactoryThemesHelper
{
    function getCurrentTheme()
    {
        $cfg= JTheFactoryHelper::getConfig();

        return self::getThemeDir().DS.$cfg->theme.DS."theme.xml";
    }
    function getThemeDir()
    {
        return JPATH_ROOT.DS.'components'.DS.APP_EXTENSION.DS.'templates';
    }
    function isCurrentTheme($theme)
    {
        $cfg= JTheFactoryHelper::getConfig();
        if (basename($theme)=='theme.xml')
            $theme=basename(dirname($theme));
        return $theme==$cfg->theme;
    }
    function getThemeList()
    {
        jimport('joomla.filesystem.folder');
        $themelist=array();
        $themedir=self::getThemeDir();
        $folders=JFolder::folders($themedir);
        if (count($folders))
            foreach($folders as $folder)
                if (file_exists($themedir.DS.$folder.DS.'theme.xml') && self::isThemeFile($themedir.DS.$folder.DS.'theme.xml') )
                    $themelist[]=$themedir.DS.$folder.DS.'theme.xml';
        return $themelist;
    }
    function isThemeFile($manifest_file)
    {
		$xml = JFactory::getXML($manifest_file);
		if( ! $xml) {
		    return false;
        }
        if (!isset($xml->attributes()->type))
		    return false;
        if ((string)$xml->attributes()->type!="theme")
		    return false;
            
        return true;
    }
    function isCoreTheme($manifest_file)
    {
		if( ! self::isThemeFile($manifest_file)) {
		    JError::raiseWarning(500,JText::_("FACTORY_FILE_IS_NOT_A_VALID_THEME_MANIFEST").$manifest_file);
		    return false;
        }
		$xml = JFactory::getXML($manifest_file);
        if (!isset($xml->attributes()->priority))
		    return false;
        if ((string)$xml->attributes()->priority=="core")
		    return true;
        return false;
                
    }
    function getThemePages($manifest_file)
    {
		if( ! self::isThemeFile($manifest_file)) {
		    JError::raiseWarning(500,JText::_("FACTORY_FILE_IS_NOT_A_VALID_THEME_MANIFEST").$manifest_file);
		    return null;
        }
		$xml = JFactory::getXML($manifest_file);
        $pages=array();
        if (isset($xml->pages->page) && count($xml->pages->page)) {
            foreach ($xml->pages->page as $page)
            {
                $p=new stdClass();
                $p->name=(string)$page->attributes()->name;
                $p->description=(string)$page->attributes()->description;
                $p->thumbnail=(string)$page->attributes()->thumbnail;
                $pages[]=$p;
            }
        }
        return $pages;
    }
    function getThemeHeader($manifest_file)
    {
		if( ! self::isThemeFile($manifest_file)) {
		    JError::raiseWarning(500,JText::_("FACTORY_FILE_IS_NOT_A_VALID_THEME_MANIFEST").$manifest_file);
		    return null;
        }
        $xml = JFactory::getXML($manifest_file);

        $header=new stdClass();
        $header->name = (string)$xml->name;
        $header->author = (string)$xml->author;
        $header->creationdate = (string)$xml->creationDate;
        $header->copyright = (string)$xml->copyright;
        $header->license = (string)$xml->license;
        $header->authoremail = (string)$xml->authorEmail;
        $header->authorurl = (string)$xml->authorUrl;
        $header->version = (string)$xml->version;
        $header->description = (string)$xml->description;
        $header->manifest_file=$manifest_file;
        $header->folder=(string)$xml->attributes()->folder;
        $header->priority=(string)$xml->attributes()->priority;
        
        return $header;
    }
    function getPage($manifest_file,$pagename)
    {
		if( ! self::isThemeFile($manifest_file)) {
		    JError::raiseWarning(500,JText::_("FACTORY_FILE_IS_NOT_A_VALID_THEME_MANIFEST").$manifest_file);
		    return null;
        }
        $xml = JFactory::getXML($manifest_file);

        if (isset($xml->pages->page) && count($xml->pages->page)) {
            foreach ($xml->pages->page as $page)
                if ((string)$page->attributes()->name==$pagename)
                    return $page;
        }
        return null;
    }
    function getPagePositions($manifest_file,$pagename)
    {
		if( ! self::isThemeFile($manifest_file)) {
		    JError::raiseWarning(500,JText::_("FACTORY_FILE_IS_NOT_A_VALID_THEME_MANIFEST").$manifest_file);
		    return null;
        }
        $xml = JFactory::getXML($manifest_file);
        $positions=array();

        if (isset($xml->pages->page) && count($xml->pages->page)) {
            foreach ($xml->pages->page as $page)
                if ((string)$page->attributes()->name==$pagename)
                    if (isset($page->positions->position) && count($page->positions->position))
                    foreach($page->positions->position as $position)
                    {
                        $p=new stdClass();
                        $p->name=(string)$position->attributes()->name;
                        $p->pagename=(string)$pagename;
                        $positions[]=$p;
                    }
        }
        return $positions;

    }
    function installTheme($sourcepath)
    {
		if( ! self::isThemeFile($sourcepath.DS."theme.xml")) {
		    JError::raiseWarning(500,JText::_("FACTORY_FILE_IS_NOT_A_VALID_THEME_MANIFEST").$sourcepath.DS."theme.xml");
		    return null;
        }
        $xml = JFactory::getXML($sourcepath.DS."theme.xml");
        $destfolder=(string)$xml->attributes()->folder;
        $destination=self::getThemeDir().DS.$destfolder;
        jimport("joomla.filesystem.folder");
        JFolder::copy($sourcepath,$destination);     
    }
    function unpackTheme($p_filename)
    {
		// Path to the archive
		$archivename = $p_filename;

		// Temporary folder to extract the archive into
		$tmpdir = uniqid('install_');

		// Clean the paths to use for archive extraction
		$extractdir = JPath::clean(dirname($p_filename) . '/' . $tmpdir);
		$archivename = JPath::clean($archivename);
        jimport('joomla.filesystem.archive');
		// Do the unpacking of the archive
		$result = JArchive::extract($archivename, $extractdir);

		if ($result === false) {
			return false;
		}


		/*
		 * Let's set the extraction directory and package file in the result array so we can
		 * cleanup everything properly later on.
		 */
		$retval['extractdir'] = $extractdir;
		$retval['packagefile'] = $archivename;

		/*
		 * Try to find the correct install directory.  In case the package is inside a
		 * subdirectory detect this and set the install directory to the correct path.
		 *
		 * List all the items in the installation directory.  If there is only one, and
		 * it is a folder, then we will set that folder to be the installation folder.
		 */
		$dirList = array_merge(JFolder::files($extractdir, ''), JFolder::folders($extractdir, ''));

		if (count($dirList) == 1)
		{
			if (JFolder::exists($extractdir . '/' . $dirList[0]))
			{
				$extractdir = JPath::clean($extractdir . '/' . $dirList[0]);
			}
		}

		/*
		 * We have found the install directory so lets set it and then move on
		 * to detecting the extension type.
		 */
		$retval['dir'] = $extractdir;
        return $retval;
    }
    function setCurrentTheme($theme)
    {
        $MyApp= JTheFactoryApplication::getInstance();

        $cfg= JTheFactoryHelper::getConfig();
        $cfg->theme=$theme;

        JTheFactoryHelper::modelIncludePath('config');
        $formxml=JPATH_ROOT.DS."administrator".DS."components".DS.APP_EXTENSION.DS. $MyApp->getIniValue('configxml');
        $model= JModel::getInstance('Config','JTheFactoryModel',array('formxml'=>$formxml));

        $model->save($cfg);


    }
}
