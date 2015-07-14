<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
class PlgLogmanConfig extends ComLogmanPluginAbstract
{
    /**
     * After config save event handler.
     *
     * @param string Context.
     * @param object Extension row data.
     * @param bool   Whether the row is new or not.
     */
    public function onConfigurationAfterSave($context, $extension, $isNew)
    {
        $this->save($this->getActivity(array(
            'subject' => array(
                'component' => 'config',
                'resource'  => $extension->type,
                'id'        => $extension->extension_id,
                'title'     => $extension->name,
                'metadata'  => array('element' => $extension->element)),
            'action'  => 'edit')));
    }
}