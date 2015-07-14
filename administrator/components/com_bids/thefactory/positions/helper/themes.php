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
 * @subpackage: positions
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class JTheFactoryThemesHelper
{
    function getThemePages($manifest_file)
    {
		$xml = JFactory::getXML($manifest_file);
		if( ! $xml) {
		    JError::raiseWarning(JText::_("FACTORY_FILE_IS_NOT_A_VALID_THEME_MANIFEST").$manifest_file);
		    return null;
        }
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
        $xml = JFactory::getXML($manifest_file);
        if( ! $xml) {
            JError::raiseWarning(500,JText::_("FACTORY_FILE_IS_NOT_A_VALID_THEME_MANIFEST").$manifest_file);
            return null;
        }

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

        return $header;
    }
    function getPagePositions($manifest_file,$pagename)
    {
        $xml = JFactory::getXML($manifest_file);
        if( ! $xml) {
            JError::raiseWarning(500,JText::_("FACTORY_FILE_IS_NOT_A_VALID_THEME_MANIFEST").$manifest_file);
            return null;
        }
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

}
