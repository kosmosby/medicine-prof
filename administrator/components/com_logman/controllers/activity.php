<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComLogmanControllerActivity extends ComActivitiesControllerActivity
{
    public function __construct(KConfig $config)
    {
        parent::__construct($config);

        // TODO To be removed as soon as the problem with language files loading on HMVC calls is solved
        JFactory::getLanguage()->load('com_logman', JPATH_ADMINISTRATOR);

        $this->registerCallback('before.get', array($this, 'setPluginWarning'));
        $this->registerCallback('before.get', array($this, 'purgeOldActivities'));
        $this->registerCallback('after.add', array($this, 'handleErrors'));
    }

    protected function _initialize(KConfig $config)
    {
        $config->append(array('behaviors' => array('com://admin/logman.controller.behavior.exportable.csv')));
        parent::_initialize($config);
    }

    /**
     * Error handler.
     *
     * @param KCommandContext $context
     */
    public function handleErrors(KCommandContext $context)
    {
        $result = $context->result;

        if ($result->getStatus() !== KDatabase::STATUS_CREATED) {
            if (JFactory::getApplication()->getCfg('debug')) {
                // Notify user about error.
                $translator = $this->getService('com://admin/logman.translator');
                $message    = $translator->translate($result->getStatusMessage());
                JFactory::getApplication()->enqueueMessage($translator->translate('Error while adding Activity',
                    array('%message%' => $message)), 'notice');
            }
            // Avoid exceptions from being thrown.
            $context->setError(null);
        }
    }

    public function setPluginWarning()
    {
        if ($this->isDispatched() && !$this->checkPlugin()) {
            $translator = $this->getService('translator')->getTranslator($this->getIdentifier());
            JFactory::getApplication()
                ->enqueueMessage($translator->translate('Please note that LOGman is disabled right now.'));
        }
    }

    /**
     * Purging is happening on backend GET access because doing it
     * when logging stuff would make the request slower for end users
     */
    public function purgeOldActivities()
    {
        $params = JComponentHelper::getParams('com_logman');

        if ($this->canPurge() && $max_age = (int) $params->get('maximum_age')) {
            // Get a clone without the current request
            $controller = $this->getService((string) $this->getIdentifier());

            $end_date = $this->getService('koowa:date')->addDays(-1 * $max_age)->getDate();
            $controller->end_date($end_date)->purge();
        }
    }

    protected function _actionEditPlugin(KCommandContext $context)
    {
        $value = $context->data->enabled;
        $id    = $this->getPluginId();

        if (version_compare(JVERSION, '1.6', '>')) {
            $query = 'UPDATE #__extensions SET enabled = %d WHERE extension_id = %d';
        } else {
            $query = 'UPDATE #__plugins SET published = %d WHERE id = %d';
        }

        $db = JFactory::getDBO();
        $db->setQuery(sprintf($query, $value, $id));

        return $db->query();
    }

    public function getPluginId()
    {
        return ComExtmanInstaller::getExtensionId(array(
            'type' => 'plugin',
            'element' => 'logman',
            'folder' => 'koowa',
        ));
    }

    public function checkPlugin()
    {
        if (version_compare(JVERSION, '1.6', '>')) {
            $query = 'SELECT enabled FROM #__extensions WHERE extension_id = %d';
        } else {
            $query = 'SELECT published FROM #__plugins WHERE id = %d';
        }

        $db = JFactory::getDBO();
        $db->setQuery(sprintf($query, $this->getPluginId()));

        return !!$db->loadResult();
    }
}