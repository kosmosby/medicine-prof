<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

/**
 * LOGman installer plugin.
 *
 * Provides handlers for dealing with J! installer events.
 */
class PlgLogmanInstaller extends ComLogmanPluginAbstract
{
    /**
     * @var bool If true, the plugin will log activities for captured events.
     */
    protected $_enabled = true;

    /**
     * @var array An associative array containing extension data to be shared among events.
     */
    protected $_extensions = array();

    /**
     * Overridden for checking if Nooku Framework is loaded.
     *
     * @see JEvent::update()
     */
    public function update(&$args)
    {
        $return = null;

        // Fire event iff activity logging is enabled.
        if ($this->_enabled) {
            $return = parent::update($args);
        }

        return $return;
    }

    /**
     * Before extension install event handler.
     *
     * @param $method
     * @param $type
     * @param $manifest
     * @param $extension
     */
    public function onExtensionBeforeInstall($method, $type, $manifest, $extension)
    {
        $name = (string) $manifest->name;

        $db    = JFactory::getDBO();
        $query = "SELECT COUNT(*) FROM `#__extensions` WHERE `type` = '{$type}' AND `name` = '{$name}'";
        $db->setQuery($query);

        // Keep information about the installation type.
        $this->_extensions[$name] = (bool) $db->loadResult() ? 'update' : 'install';

        if (strtolower($name) == 'logman') {
            // LOGman is being installed. Changes in the component could make the plugin
            // log actions to fail. Because of this, we rather disable the plugin.
            $this->_enabled = false;
        }
    }

    /**
     * After extension install event handler.
     *
     * @param $installer
     * @param $eid
     */
    public function onExtensionAfterInstall($installer, $eid)
    {
        if ($eid && isset($this->_extensions[(string) $installer->manifest->name])) {
            $action = $this->_extensions[(string) $installer->manifest->name];
            $this->_handleInstallerEvent(array(
                'action'    => $action,
                'installer' => $installer,
                'eid'       => $eid));
        }
    }

    /**
     * After extension update event handler.
     *
     * @param $installer
     * @param $eid
     */
    public function onExtensionAfterUpdate($installer, $eid)
    {
        if ($eid) {
            $this->_handleInstallerEvent(array('action' => 'update', 'installer' => $installer, 'eid' => $eid));
        }
    }

    /**
     * Before extension uninstall event handler.
     *
     * @param $eid
     */
    public function onExtensionBeforeUninstall($eid)
    {
        $extension = JTable::getInstance('extension');
        $extension->load($eid);
        $this->_extensions[$eid] = $extension;

        if ($extension->element == 'com_logman') {
            // LOGman is being un-installed, "disable" the plugin.
            $this->_enabled = false;
        }
    }

    /**
     * After extension install event handler.
     *
     * @param $installer
     * @param $eid
     * @param $result
     */
    public function onExtensionAfterUninstall($installer, $eid, $result)
    {
        if ($result && isset($this->_extensions[$eid])) {
            $this->_handleInstallerEvent(array(
                'extension' => $this->_extensions[$eid],
                'action'    => 'uninstall',
                'installer' => $installer,
                'eid'       => $eid));
        }
    }

    /**
     * Installer event handler.
     *
     * @param array $config
     */
    protected function _handleInstallerEvent($config = array())
    {
        $config = new KConfig($config);

        if (!$extension = $config->extension) {
            $extension = JTable::getInstance('extension');
            $extension->load($config->eid);
        }

        $installer = $config->installer;

        switch ($action = $config->action) {
            case 'install':
                $result = 'installed';
                break;
            case 'uninstall':
                $result = 'uninstalled';
                break;
            case 'update':
                $result = 'upgraded';
                break;
        }

        $metadata = array();

        // Store extension version (if set) in meta data.
        if ($version = (string) $installer->manifest->version) {
            $metadata['version'] = $version;
        }

        $metadata['client']  = $extension->client_id ? 'admin' : 'site';
        $metadata['element'] = $extension->element;
        $metadata['name']    = $extension->name;
        $metadata['folder']  = $extension->folder;

        // Set the resource title
        switch ($extension->type) {
            case 'plugin':
                $title = $extension->folder . ' - ' . $extension->element;
                break;
            default:
                $title = $extension->name;
                break;
        }

        $activity = $this->getActivity(array(
            'action'  => $action,
            'result'  => $result,
            'subject' => array(
                'component' => 'installer',
                'resource'  => $extension->type,
                'id'        => $config->eid,
                'title'     => $title,
                'metadata'  => empty($metadata) ? null : $metadata)));

        $this->save($activity);
    }
}