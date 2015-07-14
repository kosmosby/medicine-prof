<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
class ComLogmanActivityMessageStrategyMenus extends ComLogmanActivityMessageStrategyDefault
{
    public function _getResource(KConfig $config)
    {
        if ($config->activity->name == 'item') {
            $config->append(array('text' => 'menu item'));
        }

        return parent::_getResource($config);
    }

    protected function _resourceExists($config = array())
    {
        $config = new KConfig($config);

        switch ($config->activity->name) {
            case 'item':
                $table = 'menu';
                break;
            case 'menu':
                $table = 'menu_types';
                break;
        }

        $config->append(array('table' => $table, 'identity_column' => 'id'));

        return parent::_resourceExists($config);
    }
}