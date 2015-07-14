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
 * @subpackage: payments
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class JTheFactoryPricingHelper
{
    static function registerEvents()
    {
        $db = JFactory::getDbo();
        $db->setQuery("select * from `#__".APP_PREFIX."_pricing` where `enabled`=1");
        $items=$db->loadObjectList();

        foreach($items as $item)
            JTheFactoryEventsHelper::registerEvents(JPATH_ADMINISTRATOR.DS.'components'.DS.APP_EXTENSION.DS.'pricing'.DS.$item->itemname.DS.'events');
    }
    function getModel($model)
    {
        jimport('joomla.application.component.model');
        JTheFactoryHelper::modelIncludePath('payments');
        return JModel::getInstance($model,'JTheFactoryModel');

    }
    function isPackXML($manifest_file)
    {
		$xml = JFactory::getXML($manifest_file);
		if( ! $xml) {
		    return false;
        }
        if (!isset($xml->attributes()->type))
		    return false;
        if ((string)$xml->attributes()->type!="pricing")
		    return false;

        return true;
    }
    function parseQueries($element)
   	{

   		if ( ! $element || ! count($element->children())) {
   			// Either the tag does not exist or has no children therefore we return zero files processed.
   			return 0;
   		}
   		// Get the array of query nodes to process
   		$queries = $element->children();
   		if (count($queries) == 0) {
   			// No queries to process
   			return 0;
   		}
       // Get the database connector object
        $db =  JFactory::getDbo();
   		// Process each query in the $queries array (children of $tagName).
   		foreach ($queries as $query)
   		{
   			$db->setQuery($query->data());

   			if (!$db->query()) {
   				JError::raiseWarning(1, JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)));

   				return false;
   			}
   		}

   		return (int) count($queries);
   	}

    function installPricingPack($sourcepath)
    {
        if( ! self::isPackXML($sourcepath.DS."manifest.xml")) {
                JError::raiseWarning(500,JText::_("FACTORY_FILE_IS_NOT_A_VALID_PRICING_MANIFEST").$sourcepath.DS."manifest.xml");
                return null;
        }
        $xml = JFactory::getXML($sourcepath.DS."manifest.xml");
        $destfolder=(string)$xml->attributes()->folder;
        $destination=JPATH_COMPONENT_ADMINISTRATOR.DS.'pricing'.DS.$destfolder;
        jimport("joomla.filesystem.folder");
        JFolder::copy($sourcepath,$destination);

        $db= JFactory::getDbo();
        $db->setQuery("select max(`ordering`)+1 from #__".APP_PREFIX."_pricing");
        $maxordering=$db->loadResult();

        $paytable= JTable::getInstance('PricingTable','JTheFactory');

        $paytable->itemname=(string)$xml->attributes()->folder;
        $paytable->pricetype=(string)$xml->pricetype;
        $paytable->name=(string)$xml->name;
        $paytable->price=(string)$xml->price;
        $paytable->currency=(string)$xml->currency;
        $paytable->enabled=0;
        $paytable->params=null;
        $paytable->ordering=$maxordering;
        $paytable->store();

        self::parseQueries($xml->queries);

    }
    function unpackPricingPack($p_filename)
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

}
