<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
class ComLogmanActivityMessageStrategyTemplates extends ComLogmanActivityMessageStrategyDefault
{
    public function getText(KConfig $config)
    {
        $config->append(array('subject' => '%type% %resource% %title%'));
        return parent::getText($config);
    }

    protected function _getType(KConfig $config)
    {
        $config->append(array('text' => 'template', 'translatable' => true));
        return $this->_getResource($config);
    }

    protected function _resourceExists($config = array())
    {
        $config = new KConfig($config);
        $config->append(array('table' => 'template_styles', 'identity_column' => 'id'));
        return parent::_resourceExists($config);
    }
}