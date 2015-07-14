<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
class ComLogmanActivityMessageStrategyConfig extends ComLogmanActivityMessageStrategyDefault
{
    public function getText(KConfig $config)
    {
        $config->append(array('subject' => '%name% %resource%'));
        return parent::getText($config);
    }

    protected function _getResource(KConfig $config)
    {
        $config->append(array('text' => 'settings'));
        return parent::_getResource($config);
    }

    protected function _getResourceUrl(ComActivitiesDatabaseRowActivity $activity)
    {
        if ($activity->name == 'component') {
            $url = 'index.php?option=' . $activity->metadata->element;
        } else {
            // Assume application.
            $url = 'index.php?option=com_config';
        }

        return $url;
    }

    protected function _getName(KConfig $config)
    {
        $activity = $config->activity;

        switch ($activity->name) {
            case 'component':
                $text = $activity->title;
                break;
            case 'application':
                $text = 'application';
                break;
            default:
                $text = 'extension';
                break;
        }

        $config->append(array('text' => $text, 'translate' => true));

        return $this->_getTitle($config);
    }

    protected function _resourceExists($config = array())
    {
        $config = new KConfig($config);

        $activity = $config->activity;

        if ($activity->name == 'application') {
            $result = true;
        } elseif ($activity->name == 'component') {
            $config->append(array('table' => 'extensions', 'identity_column' => 'extension_id'));

            $result = parent::_resourceExists($config);

            if ($result) {
                // Check if an entry point file exists.
                $element = $activity->metadata->element;
                $result  = (bool) file_exists(JPATH_ADMINISTRATOR . '/components/' . $element . '/' . str_replace('com_',
                    '',
                    $element) . '.php');
            }
        } else {
            $result = false;
        }

        return $result;
    }
}