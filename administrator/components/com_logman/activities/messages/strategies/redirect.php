<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
class ComLogmanActivityMessageStrategyRedirect extends ComLogmanActivityMessageStrategyDefault
{
    public function getText(KConfig $config)
    {
        $config->append(array('subject' => '%type% %resource%'));
        return parent::getText($config);
    }

    protected function _getType(KConfig $config)
    {
        $config->append(array('text' => 'redirect', 'translate' => true));
        return $this->_getParameter($config);
    }

    protected function _getResource(KConfig $config)
    {
        $config->append(array('text' => $config->activity->name, 'translate' => true));
        return $this->_getTitle($config);
    }

    protected function _resourceExists($config = array())
    {
        $config = new KConfig($config);
        $config->append(array('table' => 'redirect_links', 'identity_column' => 'id'));
        return parent::_resourceExists($config);
    }
}