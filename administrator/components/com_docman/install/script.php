<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

defined('_JEXEC') or die;

// Need to do this as Joomla 2.5 "protects" parent and manifest properties of the installer
global $installer_manifest, $installer_source;
$installer_manifest = simplexml_load_file($this->parent->getPath('manifest'));
$installer_source = $this->parent->getPath('source');

class com_docmanInstallerScript
{
    /**
     * Name of the component
     */
    public $component;

    public function __construct($installer)
    {
        $class = get_class($this);
        preg_match('#^com_([a-z0-9_]+)#', get_class($this), $matches);
        $this->component = $matches[1];
    }

    public function preflight($type, $installer)
    {
        global $installer_manifest, $installer_source;

        $return = true;
        $errors = array();

        if (!class_exists('Koowa') || !class_exists('ComExtmanDatabaseRowExtension'))
        {
            if (file_exists(JPATH_ADMINISTRATOR.'/components/com_extman/extman.php') && !JPluginHelper::isEnabled('system', 'koowa'))
            {
                $link = version_compare(JVERSION, '1.6.0', '>=') ? '&view=plugins&filter_folder=system' : '&filter_type=system';
                $errors[] = sprintf(JText::_('This component requires System - Joomlatools Framework plugin to be installed and enabled. Please go to <a href=%s>Plugin Manager</a>, enable <strong>System - Joomlatools Framework</strong> and try again'), JRoute::_('index.php?option=com_plugins'.$link));
            }
            else $errors[] = JText::_('This component requires EXTman to be installed on your site. Please download this component from <a href=http://joomlatools.com target=_blank>joomlatools.com</a> and install it');

            $return = false;
        }

        if ($return === true)
        {
            $query = "SELECT manifest_cache FROM #__extensions WHERE element = 'com_extman'";
            $manifest = JFactory::getDBO()->setQuery($query)->loadResult();
            $cache = new JRegistry($manifest);
              $version =  $cache->get('version');

            if (version_compare($version, '1.0.0RC5', '<')) {
                $errors[] = sprintf(JText::_('This component requires a newer EXTman version. Please upgrade it first and try again.'));
                $return = false;
            }
        }

        /*if ($return === true) {
            require_once dirname(dirname(__FILE__)).'/administrator/components/com_docman/dependencies/checker.php';

            $checker = KService::get('com://admin/docman.dependency.checker', array(
                'manifest' => $installer_manifest
            ));
            $errors = $checker->getDependencyErrors($checker->getDependencies(), $type === 'update' ? 'update' : 'install');

            if ($errors) {
                $return = false;
            }
        }*/

        if ($return === true)
        {
            // If user has Docman 1.x installed, stop the installation
            if ($type === 'update' && file_exists(JPATH_ADMINISTRATOR.'/components/com_docman/docman.class.php')) {
                $errors[] = JText::_('It seems that you have DOCman 1.6 installed. In order to install DOCman 2, you need to uninstall it since there is no migration available yet for your existing documents.');
                $return   = false;
            }
            else
            {
                // If user used to have Docman 1.x installed, Docman leaves some tables around so back them up
                $tables = array(
                    '#__docman',
                    '#__docman_groups',
                    '#__docman_history',
                    '#__docman_licenses',
                    '#__docman_log'
                );

                // Special case for docman_categories since it also exists for 2.0
                $db = JFactory::getDbo();
                $db->setQuery('SHOW TABLES LIKE '.$db->quote($db->replacePrefix('#__docman_categories')));
                if ($db->loadResult())
                {
                    $fields = $db->getTableColumns('#__docman_categories');
                    if (isset($fields['parent_id']) || isset($fields['section'])) {
                        $tables[] = '#__docman_categories';
                    }
                }

                $result = true;
                foreach ($tables as $table) {
                    $result = $this->_backupTable($table);
                }

                if (!$result) {
                    $errors[] = JText::_('Unable to backup and remove old Docman database tables.');
                    $return   = false;
                }
            }
        }

        if ($return === false && $errors) {
            $error_string = implode("<br />", $errors);
            $installer->getParent()->abort($error_string);
        }

        return $return;

    }

    protected function _backupTable($table)
    {
        $return = true;
        $db     = JFactory::getDBO();

        $db->setQuery('SHOW TABLES LIKE '.$db->quote($db->replacePrefix($table)));

        if ($db->loadResult()) {
            $db->setQuery(sprintf('RENAME TABLE `%1$s` TO `%1$s_bkp`;', $table));
            $return = $db->query();
        }

        return $return;
    }

    public function postflight($type, $installer)
    {
        global $installer_manifest, $installer_source;
        // Need to do this as Joomla 2.5 "protects" parent and manifest properties of the installer
        $source = $installer_source;
        $manifest = $installer_manifest;
        $extension_id = ComExtmanInstaller::getExtensionId(array(
            'type' => 'component',
            'element' => 'com_'.$this->component
        ));

        $controller = KService::get('com://admin/extman.controller.extension', array(
            'request' => array(
                'view'   => 'extension',
                'layout' => 'success',
                'event'  => $type === 'update' ? 'update' : 'install'
            )
        ));

        $controller->add(array(
            'source' => $source,
            'manifest' => $manifest,
            'joomla_extension_id' => $extension_id,
            'event' => $type === 'update' ? 'update' : 'install'
        ));

        echo $controller->display();
    }
}
