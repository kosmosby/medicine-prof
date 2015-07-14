<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
abstract class ComLogmanPluginAbstract extends JPlugin implements ComLogmanPluginInterface, ComLogmanPluginInjector
{
    /**
     * @var JRegistry LOGman component settings.
     */
    static private $_params = null;

    /**
     * @var array Dependencies to be injected.
     */
    protected $_dependencies = array();

    /**
     * @see JPlugin::__construct()
     */
    public function __construct(&$subject, $config = array())
    {
        parent::__construct($subject, $config);

        if (is_null(self::$_params)) {
            self::$_params = JComponentHelper::getParams('com_logman');
        }

        $this->inject($this->_dependencies);
    }

    /**
     * @see ComLogmanPluginInterface::save()
     */
    final public function save($activity)
    {
        return KService::get('com://admin/logman.controller.activity')->add($activity);
    }

    /**
     * @see ComLogmanPluginInterface::getParm()
     */
    public function getParameter($name, $default = null)
    {
        return self::$_params->get($name, $default);
    }

    /**
     * Injects behaviors to target identifiers.
     *
     * @param array $dependencies Associative array with target identifiers as keys and arrays
     *                            with behavior identifiers as values.
     *
     * @see ComLogmanPluginInjector::inject()
     */
    final public function inject($dependencies = array())
    {
        foreach ($dependencies as $target => $behaviors) {

            $behaviors = (array) $behaviors;

            foreach ($behaviors as $behavior) {
                KService::setConfig($target, array('behaviors' => array($behavior)));
            }
        }
    }

    /**
     * @see ComLogmanPluginInterface::getActivity()
     *
     * @return array Associative array containing formatted activity data.
     */
    public function getActivity($config)
    {
        $config = new KConfig($config);

        $config->append(array(
            'subject'     => array('type' => 'com'),
            'application' => JFactory::getApplication()->isAdmin() ? 'admin' : 'site'));

        $activity = array();

        $activity['type']     = $config->subject->type;
        $activity['package']  = $config->subject->component;
        $activity['name']     = $config->subject->resource;
        $activity['row']      = $config->subject->id;
        $activity['title']    = $config->subject->title;
        $activity['metadata'] = $config->subject->metadata;

        $activity['status']      = $config->result;
        $activity['action']      = $config->action;
        if ($actor = $config->actor)
        {
            $activity['created_by'] = $actor;
        }
        $activity['application'] = $config->application;

        return $activity;
    }
}