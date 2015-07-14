<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class com_docmanInstallerScript
{
    /**
     * Name of the component
     */
    public $component;

    public function __construct($installer)
    {
        preg_match('#^com_([a-z0-9_]+)#', get_class($this), $matches);
        $this->component = $matches[1];
    }

    public function preflight($type, $installer)
    {
        $return = true;
        $errors = array();

        if (!class_exists('Koowa') || !class_exists('ComExtmanControllerExtension'))
        {
            if (file_exists(JPATH_ADMINISTRATOR.'/components/com_extman/extman.php') && !JPluginHelper::isEnabled('system', 'koowa')) {
                $errors[] = sprintf(JText::_('This component requires System - Nooku Framework plugin to be installed and enabled. Please go to <a href=%s>Plugin Manager</a>, enable <strong>System - Nooku Framework</strong> and try again'), JRoute::_('index.php?option=com_plugins&view=plugins&filter_folder=system'));
            } else {
                $errors[] = JText::_('This component requires EXTman to be installed on your site. Please download this component from <a href=http://joomlatools.com target=_blank>joomlatools.com</a> and install it');
            }

            $return = false;
        }

        // Check EXTman version.
        if ($return === true)
        {
            $query    = "SELECT manifest_cache FROM #__extensions WHERE element = 'com_extman'";
            $manifest = JFactory::getDBO()->setQuery($query)->loadResult();
            $cache    = new JRegistry($manifest);
            $version  = $cache->get('version');

            if (version_compare($version, '2.0.5', '<') || !class_exists('ComExtmanModelEntityExtension'))
            {
                $errors[] = JText::_('This component requires a newer EXTman version. Please download EXTman from <a href=http://joomlatools.com target=_blank>joomlatools.com</a> and upgrade it first.');
                $return   = false;
            }
        }

        if ($return === true)
        {
            // If user has Docman 1.x installed, stop the installation
            if ($type === 'update' && file_exists(JPATH_ADMINISTRATOR.'/components/com_docman/docman.class.php'))
            {
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
                foreach ($tables as $table)
                {
                    if (!$this->_backupTable($table))
                    {
                        $result = false;
                        break;
                    }
                }

                if (!$result)
                {
                    $errors[] = JText::_('Unable to backup and remove old Docman database tables.');
                    $return   = false;
                }
            }
        }

        if ($return === true && $type !== 'update')
        {
            jimport('joomla.filesytem.file');
            jimport('joomla.filesytem.folder');

            $path = JPATH_ROOT.'/joomlatools-files';
            if (JFolder::exists($path))
            {
                // Try to write a file
                $test  = $path.'/removethisfile';
                $blank = '';
                if (!JFile::write($test, $blank)) {
                    $errors[] = JText::_('Document path is not writable. Please make sure that joomlatools-files folder in your site root is writable.');
                    $return   = false;
                }
                elseif (JFile::exists($test)) {
                    JFile::delete($test);
                }

            }
            elseif (!JFolder::create($path))
            {
                $errors[] = JText::_('Document path cannot be automatically created. Please create a folder named joomlatools-files in your site root and make sure it is writable.');
                $return   = false;
            }
        }

        if ($return === false && $errors)
        {
            $error_string = implode("<br />", $errors);
            $installer->getParent()->abort($error_string);
        }

        return $return;

    }

    protected function _backupTable($table)
    {
        $db     = JFactory::getDBO();
        $query  = 'SHOW TABLES LIKE %s';

        $source_exists = $db->setQuery(sprintf($query, $db->quote($db->replacePrefix($table))))->loadResult();
        $destination_exists = $db->setQuery(sprintf($query, $db->quote($db->replacePrefix($table.'_bkp'))))->loadResult();

        if ($source_exists)
        {
            if ($destination_exists) {
                $return = false;
            }
            else
            {
                $db->setQuery(sprintf('RENAME TABLE `%1$s` TO `%1$s_bkp`;', $table));
                $return = $db->query();
            }
        }
        else $return = true;

        return $return;
    }

    protected function _updateRedirectPlugin($installer)
    {
        $plugin_exists = ComExtmanModelEntityExtension::getExtensionId(array(
            'type'    => 'plugin',
            'element' => 'docman_redirect',
            'folder'  => 'system'
        ));

        if ($plugin_exists)
        {
            $path = $installer->getParent()->getPath('source').'/extensions/plg_system_docman_redirect';
            $instance = new JInstaller();
            $instance->install($path);
        }
    }

    public function postflight($type, $installer)
    {
        if ($type === 'update') {
            $this->_updateRedirectPlugin($installer);
        }

        $extension_id = ComExtmanModelEntityExtension::getExtensionId(array(
            'type'    => 'component',
            'element' => 'com_'.$this->component
        ));

        $controller = KObjectManager::getInstance()->getObject('com://admin/extman.controller.extension')
            ->view('extension')
            ->layout('success')
            ->event($type === 'update' ? 'update' : 'install');

        $controller->add(array(
            'source'              => $installer->getParent()->getPath('source'),
            'manifest'            => $installer->getParent()->getPath('manifest'),
            'joomla_extension_id' => $extension_id,
            'install_method'      => $type,
            'event'               => $type === 'update' ? 'update' : 'install'
        ));

        echo $controller->render();
    }
}
