<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Script file of Joooid component
 */
class pkg_joooidInstallerScript
{
        /**
         * method to install the component
         *
         * @return void
         */
        function install($parent) 
        {
               
        }
 
        /**
         * method to uninstall the component
         *
         * @return void
         */
        function uninstall($parent) 
        {
               
        }
 
        /**
         * method to update the component
         *
         * @return void
         */
        function update($parent) 
        {
                 
        }
 
        /**
         * method to run before an install/update/uninstall method
         *
         * @return void
         */
        function preflight($type, $parent) 
        {
                
        }
 
        /**
         * method to run after an install/update/uninstall method
         *
         * @return void
         */
        function postflight($type, $parent) 
        {
         // $parent is the class calling this method
		$db = JFactory::getDBO();
                $query = "UPDATE #__extensions SET enabled=1  where name like '%joooid%'";
		$db->setQuery($query);
		$db->query();

                $parent->getParent()->setRedirectURL('index.php?option=com_joooid');
               
        }
} 
