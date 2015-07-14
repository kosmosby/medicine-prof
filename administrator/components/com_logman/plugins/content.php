<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

/**
 * Base LOGman content plugin.
 *
 * Generic class for dealing with content level events.
 */
abstract class ComLogmanPluginContent extends ComLogmanPluginContext
{
    /**
     * J!2.5 after content save event handler.
     *
     * @param $context
     * @param $content
     * @param $isNew
     */
    public function onContentAfterSave($context, $content, $isNew)
    {
        if ($activity = $this->getActivity(array(
            'context' => $context,
            'data'    => $content,
            'action'  => $isNew ? 'add' : 'edit'))
        ) {
            $this->save($activity);
        }
    }

    /**
     * J!2.5 after content delete event handler.
     *
     * @param $context
     * @param $content
     */
    public function onContentAfterDelete($context, $content)
    {
        if ($activity = $this->getActivity(array('context' => $context, 'data' => $content, 'action' => 'delete'))) {
            $this->save($activity);
        }
    }
}