<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
defined('_JEXEC') or die;

// Need to do this as Joomla 2.5 "protects" parent and manifest properties of the installer
global $installer_manifest, $installer_source;
$installer_manifest = simplexml_load_file($this->parent->getPath('manifest'));
$installer_source = $this->parent->getPath('source');

class com_logmanInstallerScript
{
	/**
	 * Name of the component
	 */
	public $component;

    /**
     * @var string The current installed EXTman version.
     */
    protected $_extman_ver = null;

    /**
     * @var string The current installed LOGman version.
     */
    protected $_current_ver = null;

	public function __construct($installer)
	{
        global $installer_manifest, $installer_source;

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

        // Check EXTman version.
        if ($return === true) {
            if (version_compare($this->getExtmanVersion(), '1.0.0RC5', '<')) {
                $errors[] = sprintf(JText::_('This component requires a newer EXTman version. Please upgrade it first and try again.'));
                $return   = false;
            }
        }

        if ($return == true && $type == 'update') {
            if ($current = $this->getCurrentVersion()) {
                require_once JPATH_ADMINISTRATOR . '/components/com_extman/install/helper.php';
                $helper = new ComExtmanInstallerHelper();
                if ($failed = $helper->checkDatabasePrivileges(array('ALTER'))) {
                    $errors[] = JText::sprintf('The following MySQL privileges are missing: %s. Please make them available to your MySQL user and try again.',
                        htmlspecialchars(implode(', ', $failed), ENT_QUOTES));
                    $return   = false;
                } elseif($failed = $this->_dbUpdate(array('from_ver' => $current))) {
                    $errors[] = JText::_('DB schema update failed while processing queries:');
                    foreach ($failed as $query) {
                        $errors[] = htmlspecialchars($query, ENT_QUOTES);
                    }
                    $return = false;
                }

                // Old system plugin needs to be un-installed.
                if (version_compare($current, '1.0.0RC4', '<')) {
                    $extension = KService::get('com://admin/extman.model.extension')->identifier('plg:system.logman')->getItem();
                    if (!$extension->isNew()) {
                        $extension->delete();
                    }
                }

            } else {
                $errors[] = JText::_('Update failed. Unable to determine previous installed version of LOGman.');
                $return   = false;
            }
        }

		// J1.5 does not remove menu items on unsuccessful installs
		if ($return === false && $type !== 'update' && version_compare(JVERSION, '1.6', '<'))
		{
		    $db = JFactory::getDBO();
		    $db->setQuery(sprintf("DELETE FROM #__components WHERE `option` = 'com_%s'", $this->component));
		    $db->query();
		}

        if ($return == false && $errors) {
            $error = implode('<br />', $errors);
            JError::raiseWarning(null, $error);
        }

		return $return;
	}

	public function postflight($type, $installer)
	{
		global $installer_manifest, $installer_source;

		// Need to do this as Joomla 2.5 "protects" parent and manifest properties of the installer
		$source       = $installer_source;
		$manifest     = $installer_manifest;

        $extension_id = ComExtmanInstaller::getExtensionId(array(
			'type' 	  => 'component',
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

    protected function _dbUpdate($config = array())
    {
        global $installer_manifest;

        $failed = array();

        $current_ver = (string) $installer_manifest->version;

        $queries = array();

        $db = JFactory::getDBO();

        // Only update if a newer version is being installed.
        if (version_compare($config['from_ver'], $current_ver, '<')) {

            // Check that schema isn't already up to date (downgrade and re-install).
            // TODO: Remove when downgrades get disallowed on installers.
            $schema = KService::get('koowa:database.adapter.mysqli')->getTableSchema('activities_activities');

            if (!isset($schema->columns['metadata'])) {
                // Row can now contain non-integer values.
                $queries[] = 'ALTER TABLE `#__activities_activities` MODIFY `row`  VARCHAR(2048) NOT NULL DEFAULT \'\'';

                // Adding indexes.
                $queries[] = 'ALTER TABLE `#__activities_activities` ADD INDEX `package` (`package`)';
                $queries[] = 'ALTER TABLE `#__activities_activities` ADD INDEX `name` (`name`)';
                $queries[] = 'ALTER TABLE `#__activities_activities` ADD INDEX `row` (`row`)';

                // Added ip column.
                $queries[] = 'ALTER TABLE `#__activities_activities` ADD COLUMN `ip` varchar(45) NOT NULL DEFAULT \'\'';
                $queries[] = 'ALTER TABLE `#__activities_activities` ADD INDEX `ip` (`ip`)';

                // Add context row.
                $queries[] = 'ALTER TABLE `#__activities_activities` ADD COLUMN `metadata` text NOT NULL';
            }

            foreach ($queries as $query) {
                $db->setQuery($query);
                if ($db->query() === false) {
                    $failed[] = $query;
                    // Do not continue with the upgrade. This should facilitate a manual intervention in case
                    // anything goes wrong.
                    break;
                }
            }
        }

        return $failed;
    }

    /**
     * Returns the current version (if any) of LOGman.
     *
     * @return string|null The LOGman version if present, null otherwise.
     */
    public function getCurrentVersion()
    {
        if (!$this->_current_ver) {
            $this->_current_ver = $this->_getExtensionVersion('com_logman');
        }
        return $this->_current_ver;
    }

    /**
     * Returns the current installed version (if any) of EXTman.
     *
     * @return null|string The EXTman version if present, null otherwise.
     */
    public function getExtmanVersion()
    {
        if (!$this->_extman_ver) {
            $this->_extman_ver = $this->_getExtensionVersion('com_extman');
        }
        return $this->_extman_ver;
    }

    /**
     * Extension version getter.
     *
     * @param $element The element name, e.g. com_extman, com_logman, etc.
     *
     * @return mixed|null|string The extension version, null if couldn't be determined.
     */
    protected function _getExtensionVersion($element)
    {
        $version = null;
        if (version_compare(JVERSION, '1.6', '<')) {
            // Do a manifest file check.
            if ($manifest = simplexml_load_file(JPATH_ADMINISTRATOR . '/components/'.$element.'/manifest.xml')) {
                $version = (string) $manifest->version;
            }
        } else {
            // Do a DB check.
            $query    = "SELECT manifest_cache FROM #__extensions WHERE element = '{$element}'";
            if ($result = JFactory::getDBO()->setQuery($query)->loadResult()) {
                $manifest = new JRegistry($result);
                $version  = $manifest->get('version', null);
            }
        }
        return $version;
    }
}
