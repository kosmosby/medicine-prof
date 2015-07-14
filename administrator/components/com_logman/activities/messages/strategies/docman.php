<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
class ComLogmanActivityMessageStrategyDocman extends ComLogmanActivityMessageStrategyDefault
{
    /**
     * @var int Installed DOCman version (-1 if not installed).
     */
    static protected $_docman_version;

    public function getIcon(KConfig $config)
    {
        $activity = $config->activity;
        if ($activity->action == 'download')
        {
            $config->append(array('class' => 'icon-download'));
        }
        return parent::getIcon($config);
    }

    protected function _getResourceUrl(ComActivitiesDatabaseRowActivity $activity)
    {
        if ($activity->name == 'document') {
            $version = $this->_getComponentVersion();
            if ($version == -1) {
                $url = null;
            } elseif (version_compare($version, '2', '<')) {
                $url = 'index.php?option=com_docman&task=edit&section=documents&cid=' . $activity->row;
            } else {
                $url = 'index.php?option=com_docman&view=document&id=' . $activity->row;
            }
        } else {
            // Assume category.
            $url = 'index.php?option=com_docman&view=category&id=' . $activity->row;
        }

        return $url;
    }

    public function getText(KConfig $config)
    {
        $config->append(array('subject' => '%type% %resource% %title%'));
        return parent::getText($config);
    }

    protected function _getType(KConfig $config)
    {
        $config->append(array('text' => 'DOCman'));
        return $this->_getParameter($config);
    }

    protected function _getComponentVersion()
    {
        if (!self::$_docman_version) {
            if (version_compare(JVERSION, '1.6', '<')) {
                // Use manifest file.
                $file = JPATH_ADMINISTRATOR . '/components/com_docman/manifest.xml';
                if (file_exists($file)) {
                    $manifest = JApplicationHelper::parseXMLInstallFile($file);
                    $version  = $manifest['version'];
                }
            } else {
                // Use DB manifest cache.
                $query = "SELECT manifest_cache FROM #__extensions WHERE element = 'com_docman'";
                if ($result = JFactory::getDBO()->setQuery($query)->loadResult()) {
                    $manifest = new JRegistry($result);
                    $version  = $manifest->get('version');
                }
            }
            self::$_docman_version = isset($version) ? $version : -1;
        }

        return self::$_docman_version;
    }

    protected function _resourceExists($config = array())
    {
        $config = new KConfig($config);

        $version = $this->_getComponentVersion();
        $parts   = explode('.', $version);

        switch ($parts[0]) {
            case '-1':
                // DOCman not installed. Target does not exists.
                $result = false;
                break;
            case '1':
                $config->append(array('table' => 'docman', 'identity_column' => 'id'));
            default:
                $result = parent::_resourceExists($config);
                break;
        }

        return $result;
    }
}