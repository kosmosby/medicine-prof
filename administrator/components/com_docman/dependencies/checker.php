<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanDependencyChecker extends KObject
{
    const DEPENDENCY_OK = 1;
    const DEPENDENCY_MISSING = 2;
    const DEPENDENCY_LT_MINIMUM = 4;
    const DEPENDENCY_GT_MAXIMUM = 8;
    const PACKAGED_LT_INSTALLED = 16;
    const PACKAGED_GT_INSTALLED = 32;

    protected static $_special_dependencies = array(
        'php' => 'PHP',
        'mysql' => 'MySQL',
        'joomla' => 'Joomla!',
        'EXTman' => 'EXTman'
    );

    protected static $_installed_components = array();

    protected $_translator;

    public $manifest;

    public function __construct($config)
    {
        parent::__construct($config);

        $this->setManifest($config->manifest);

        $this->_translator = $config->translator;
    }

    protected function _initialize($config)
    {
        try {
            $config->append(array(
                'manifest' => null,
                'translator' => $this->getService('translator')
            ));
        } catch (KServiceIdentifierException $e) {
            throw new KException('Please first upgrade EXTman to be able to use this extension.');
        }

        parent::_initialize($config);
    }

    public function setManifest($manifest)
    {
        if (is_string($manifest)) {
            $manifest = simplexml_load_string($manifest);
        }

        $this->manifest = $manifest;

        return $this;
    }

    public function getDependencies()
    {
        $specials = array_keys(self::$_special_dependencies);
        $results = array();

        foreach ($this->manifest->dependencies->children() as $require) {
            if (!in_array($require->getName(), array('requisite'))) {
                continue;
            }

            $entry = (object) array(
                'result'          => self::DEPENDENCY_OK,
                'name'            => strtolower($require['name']),
                'display_name'    => (string) $require['display_name'] ? (string) $require['display_name'] : (string) $require['name'],
                'packaged' => (string) $require['packaged'] ? (string) $require['packaged'] : null,
                'minimum'  => (string) $require['minimum'],
                'maximum'  => (string) $require['maximum'],
                'current'  => null
            );

            if (in_array($entry->name, $specials)) {
                $entry->current = $this->getSpecialDependencyVersion($entry->name);
            } else {
                $components = self::getInstalledComponents();

                if (!isset($components['com:'.$entry->name])) {
                    $entry->result = self::DEPENDENCY_MISSING;
                } else {
                    $entry->current = $components['com:'.$entry->name]->version;
                }
            }

            // Dependency exists, check for version
            if ($entry->result === self::DEPENDENCY_OK) {
                if ($entry->minimum && !version_compare($entry->current, $entry->minimum, '>=')) {
                    $entry->result = self::DEPENDENCY_LT_MINIMUM;
                }

                if ($entry->maximum && !version_compare($entry->current, $entry->maximum, '<=')) {
                    $entry->result = self::DEPENDENCY_GT_MAXIMUM;
                }

                if ($entry->current && $entry->packaged && version_compare($entry->current, $entry->packaged, '>')) {
                    $entry->result |= self::PACKAGED_LT_INSTALLED;
                }

                if ($entry->current && $entry->packaged && version_compare($entry->current, $entry->packaged, '<')) {
                    $entry->result |= self::PACKAGED_GT_INSTALLED;
                }
            }

            $results[$entry->name] = $entry;
        }

        return $results;
    }

    protected function _getSpecialDependencyError($entry)
    {
        if ($entry->result & self::DEPENDENCY_OK) {
            return false;
        }

        $entry->display_name = self::$_special_dependencies[$entry->display_name];

        $min = $entry->minimum;
        $max = $entry->maximum;

        $string = '';

        // Print the required version info
        if ($min && $max) {
            $string .= JText::_('This extension requires %1$s between versions %2$s to %3$s to work.');
        } elseif ($min) {
            $string .= JText::_('This extension requires %1$s %2$s or newer to work.');
        } elseif ($max) {
            $string .= JText::_('This extension requires %1$s %3$s or an older version to work.');
        } else {
            $string .= JText::_('This extension requires %1$s to work.');
        }

        // Inform about current version
        if ($min || $max) {
            $string .= ' '.JText::_('You currently have version %4$s installed.');
        }

        $string = sprintf($string, $entry->display_name, $min, $max, $entry->current);

        return $string;
    }

    public function getDependencyErrors($results, $type = 'runtime', $component = null)
    {
        $errors = array();

        if ($component === null) {
            if (is_object($this->manifest)) {
                $component = (string) $this->manifest->name;
            }
        }

        foreach ($results as $entry)
        {
            if (isset(self::$_special_dependencies[$entry->display_name]))
            {
                if ($error = $this->_getSpecialDependencyError($entry)) {
                    $errors[] = $error;
                }
                continue;
            }

            // This breaks the loop when special dependencies fail so that user can fix his server first
            if (count($errors)) {
                break;
            }

            if ($entry->result === self::DEPENDENCY_OK) {
                continue;
            }

            if ($type !== 'runtime' && ($entry->result & self::DEPENDENCY_MISSING)) {
                // we are gonna install it anyway so skip it
                continue;
            }

            $string = '';

            // Missing or version problem
            if ($type === 'runtime' & !($entry->result & self::DEPENDENCY_OK))
            {
                $string = $this->_translator->translate('%extension% needs to be reinstalled as it is missing some dependencies.', array(
                    '%extension%' => $component
                ));
            }

            if ($type !== 'runtime'
                && ($entry->result & (self::PACKAGED_LT_INSTALLED | self::PACKAGED_GT_INSTALLED)))
            {
                $dependency = $this->getService('com://admin/extman.model.extensions')
                ->identifier('com:'.$entry->name)->getItem();

                $extensions = $this->getService('com://admin/extman.model.extensions')
                ->top_level(true)->depends_on($dependency->id)->getList();

                $names = $extensions->getColumn('name');

                foreach ($names as $i => $name) {
                    if ($name === $component) {
                        unset($names[$i]);
                        break;
                    }
                }

                if ($entry->result & self::PACKAGED_LT_INSTALLED)
                {
                    // applies to both upgrade and install:
                    $string = $this->_translator->translate('You need a newer version of this extension. Otherwise it would break following extensions on your site: %extensions%',
                            array('%extensions%' => implode(', ', $names))
                    );
                }
                elseif (count($names))
                {
                    if ($type === 'install')
                    {
                        $string = $this->_translator->translate('You need to upgrade following extensions first before installing this one since they share dependencies: %extensions%',
                                array('%extensions%' => implode(', ', $names))
                        );
                    }
                    elseif ($type === 'update')
                    {
                        // TODO: disable extensions if there are no errors yet
                        $notice = $this->_translator->translate('CAUTION: Following extensions are disabled since they share dependencies and otherwise would break. You need to upgrade them as well: %extensions%',
                                array('%extensions%' => implode(', ', $names))
                        );
                        JFactory::getApplication()->enqueueMessage($notice, 'warning');
                    }
                }
            }

            if ($string) {
                $errors[] = $string;
            }
        }

        return $errors;
    }

    protected function _disableExtension($identifier)
    {
        $components = self::getInstalledComponents();

        if (!isset($components[$identifier])) {
            return false;
        }

        $component = $components[$identifier];

        $id = $component->joomla_extension_id;

        $db = JFactory::getDBO();
        if (version_compare(JVERSION, '1.6', '<')) {
            $query = 'UPDATE #__components SET enabled = 0 WHERE id = %d';
        } else {
            $query = 'UPDATE #__extensions SET enabled = 0 WHERE id = %d';
        }

        $db->setQuery(sprintf($query, $id));

        return $db->query();
    }

    public function checkRuntimeDependencies($name, $display_errors = null)
    {
        $errors = array();
        if ($display_errors === null) {
            $display_errors = JFactory::getApplication()->isAdmin();
        }

        $components = self::getInstalledComponents();

        if (!isset($components['com:'.$name])) {
            $errors = array(JText::_('Invalid component name supplied for dependency checker'));
        } else {
            $component = $components['com:'.$name];

            $manifest = simplexml_load_string($component->manifest);
            if (is_object($manifest)) {
                $this->setManifest($manifest);

                $results = $this->getDependencies();
                $errors = $this->getDependencyErrors($results, $component, 'runtime');
            }
        }

        if ($display_errors) {
            foreach ($errors as $error) {
                JFactory::getApplication()->enqueueMessage($error, 'error');
            }
        }

        return count($errors) === 0;
    }

    public function getSpecialDependencyVersion($name)
    {
        switch ($name) {
            case 'php':
                return PHP_VERSION;
            case 'mysql':
                return @mysql_get_server_info();
            case 'joomla':
                return JVERSION;
        }

        return false;
    }

    public static function getInstalledComponents()
    {
        if (empty(self::$_installed_components)) {
            $db = JFactory::getDbo();
            $db->setQuery("SELECT * FROM #__extman_extensions WHERE type = 'component'");
            $components = $db->loadObjectList('identifier');

            $manifest = JPATH_ROOT.'/administrator/components/com_extman/manifest.xml';
            if (file_exists($manifest)) {
                $manifest = simplexml_load_file($manifest);
                $components['com:extman'] = (object) array(
                    'identifier' => 'com:extman',
                    'version' => (string) $manifest->version
                );
            }

            self::$_installed_components = $components;
        }

        return self::$_installed_components;
    }
}
