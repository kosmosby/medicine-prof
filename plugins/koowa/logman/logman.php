<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
class PlgKoowaLogman extends PlgKoowaDefault
{
    function __construct($dispatcher, $config = array())
    {
        // Avoid fatal errors due to component downgrade.
        // TODO: Remove when downgrades get disallowed on installers.
        if (version_compare($this->_getLogmanVersion(), '1.0.0RC4', '>=')) {
            $identifiers = array(
                'com://admin/logman.plugin.interface',
                'com://admin/logman.plugin.injector',
                'com://admin/logman.plugin.abstract',
                'com://admin/logman.plugin.context',
                'com://admin/logman.plugin.content');

            // Load LOGman base plugin classes.
            foreach ($identifiers as $identifier) {
                if (!$loaded = KLoader::loadIdentifier($identifier)) {
                    break;
                }
            }

            // Load LOGman plugin group.
            if ($loaded) {
                JPluginHelper::importPlugin('logman');
            }
        }

        parent::__construct($dispatcher, $config);
    }

    /**
     * LOGman version getter.
     *
     * @return string|null The extension version, null if couldn't be determined.
     */
    protected function _getLogmanVersion()
    {
        $version = null;
        if (version_compare(JVERSION, '1.6', '<')) {
            $manifest = JPATH_ADMINISTRATOR . '/components/com_logman/manifest.xml';
            // Do a manifest file check.
            if (file_exists($manifest) && $manifest = simplexml_load_file($manifest)) {
                $version = (string) $manifest->version;
            }
        } else {
            // Do a DB check.
            $query = "SELECT manifest_cache FROM #__extensions WHERE element = 'com_logman'";
            if ($result = JFactory::getDBO()->setQuery($query)->loadResult()) {
                $manifest = new JRegistry($result);
                $version  = $manifest->get('version', null);
            }
        }
        return $version;
    }
}

