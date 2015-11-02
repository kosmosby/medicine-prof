<?php

class pkg_widgetkitInstallerScript
{
    public function install($parent)
    {
    	JFactory::getDBO()->setQuery("UPDATE `#__extensions` SET `enabled` = 1 WHERE `element` = 'widgetkit' AND `folder` IN ('content', 'editors-xtd')")->execute();
    }

    public function uninstall($parent) {}

    public function update($parent) {}

    public function preflight($type, $parent)
    {
        $params = JFactory::getDBO()->setQuery("SELECT manifest_cache FROM `#__extensions` WHERE `element` = 'com_widgetkit'")->loadResult();

        if ($params = @json_decode($params, true) and isset($params['version']) && version_compare($params['version'], '2.0.0', '<')) {
            JError::raiseWarning(null, 'Cannot install Widgetkit 2.0, please read the <a href="http://yootheme.com/widgetkit/documentation/troubleshooting/how-to-migrate" target="_blank">Widgetkit migration guide</a>');
            return false;
        }
    }

    public function postflight($type, $parent) {

        // updateservers url update workaround
        if ('update' == $type) {

            $db = JFactory::getDBO();

            if ($parent->manifest->updateservers) {

                $servers = $parent->manifest->updateservers->children();

                $db->setQuery(
                    "UPDATE `#__update_sites` a" .
                    " LEFT JOIN `#__update_sites_extensions` b ON b.update_site_id = a.update_site_id" .
                    " SET location = " . $db->quote(trim((string) $servers[0])) . ', enabled = 1' .
                    " WHERE b.extension_id = (SELECT `extension_id` FROM `#__extensions` WHERE `type` = 'package' AND `element` = 'pkg_widgetkit')"
                )->execute();

            }
        }
    }
}
