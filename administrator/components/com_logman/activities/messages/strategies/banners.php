<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
class ComLogmanActivityMessageStrategyBanners extends ComLogmanActivityMessageStrategyDefault
{
    public function getText(KConfig $config)
    {
        if ($config->activity->name == 'client') {
            $config->append(array('subject' => '%type% %resource% %name%'));
        }
        return parent::getText($config);
    }

    protected function _getName(KConfig $config)
    {
        return $this->_getTitle($config);
    }

    protected function _getType(KConfig $config)
    {
        $config->append(array('text' => 'banners', 'translate' => true));
        return $this->_getParameter($config);
    }

    protected function _resourceExists($config = array())
    {
        $config = new KConfig($config);

        switch ($config->activity->name) {
            default:
            case 'banner':
                $table = 'banners';
                break;
            case 'client':
                $table = 'banner_clients';
                break;
        }

        $config->append(array('table' => $table, 'identity_column' => 'id'));

        return parent::_resourceExists($config);
    }
}