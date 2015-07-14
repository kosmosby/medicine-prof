<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

/**
 * LOGman extension plugin.
 *
 * Provides handlers for dealing with extension level events from core J! extensions.
 */
class PlgLogmanExtension extends ComLogmanPluginContext
{
    /**
     * @see ComLogmanPluginContext::$_contexts
     */
    protected $_contexts = array(
        'com_modules.module',
        'com_plugins.plugin',
        'com_templates.style',
        'com_languages.language');

    /**
     * After extension save event handler.
     *
     * @param $context
     * @param $data
     * @param $isNew
     */
    public function onExtensionAfterSave($context, $data, $isNew)
    {
        if ($activity = $this->getActivity(array(
            'context' => $context,
            'data'    => $data,
            'action'  => $isNew ? 'add' : 'edit'))
        ) {
            $this->save($activity);
        }
    }

    /**
     * After extension delete event handler.
     *
     * @param $context
     * @param $data
     */
    public function onExtensionAfterDelete($context, $data)
    {
        if ($activity = $this->getActivity(array('context' => $context, 'data' => $data, 'action' => 'delete'))) {
            $this->save($activity);
        }
    }

    protected function _getLanguagesLanguageSubject($data)
    {
        return array('id' => $data->lang_id, 'title' => $data->title);
    }

    protected function _getPluginsPluginSubject($data)
    {
        return array('id' => $data->extension_id, 'title' => $data->folder . ' - ' . $data->element);
    }
}