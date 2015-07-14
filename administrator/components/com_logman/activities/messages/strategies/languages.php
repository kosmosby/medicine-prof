<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
class ComLogmanActivityMessageStrategyLanguages extends ComLogmanActivityMessageStrategyDefault
{
    public function getText(KConfig $config)
    {
        $config->append(array('subject' => '%type% %resource% %title%'));
        return parent::getText($config);
    }

    protected function _getType(KConfig $config)
    {
        $config->append(array('text' => 'content', 'translate' => true));
        return $this->_getParameter($config);
    }

    protected function _getResourceUrl(ComActivitiesDatabaseRowActivity $activity)
    {
        return 'index.php?option=com_' . $activity->package . '&task=' . $activity->name . '.edit&lang_id=' . $activity->row;
    }

    protected function _resourceExists($config = array())
    {
        $config = new KConfig($config);
        $config->append(array('table' => 'languages', 'identity_column' => 'lang_id'));
        return parent::_resourceExists($config);
    }
}