<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
class ComLogmanActivityMessageStrategyContact extends ComLogmanActivityMessageStrategyDefault
{
    public function getIcon(KConfig $config)
    {
        if ($config->activity->action == 'contact') {
            $config->append(array('class' => 'icon-envelope'));
        }
        return parent::getIcon($config);
    }

    public function getText(KConfig $config)
    {
        if ($config->activity->action == 'contact') {
            $config->append(array('actor' => '%user% %name%', 'subject' => '%resource%'));
        } else {
            $config->append(array('subject' => '%resource% %name%'));
        }
        return parent::getText($config);
    }

    protected function _getResource(KConfig $config)
    {
        $activity = $config->activity;

        if ($activity->action == 'contact') {
            $result = $this->_getTitle($config);
        } else {
            $result = parent::_getResource($config);
        }
        return $result;
    }

    protected function _getName(KConfig $config)
    {
        $activity = $config->activity;

        if ($activity->action == 'contact') {
            $config->append(array(
                'text' => $activity->metadata->sender->name,
                'link' => array('url' => 'mailto:' . $activity->metadata->sender->email)));
            $result = $this->_getParameter($config);

       } else {
           $result = $this->_getTitle($config);
        }
return $result;
    }

    protected function _resourceExists($config = array())
    {
        $config = new KConfig($config);
        $config->append(array('table' => 'contact_details', 'identity_column' => 'id'));
        return parent::_resourceExists($config);
    }
}
