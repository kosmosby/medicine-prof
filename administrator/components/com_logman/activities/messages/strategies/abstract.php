<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
abstract class ComLogmanActivityMessageStrategyAbstract extends KObject implements ComLogmanActivityMessageStrategyInterface, KServiceInstantiatable
{
    protected $_parameter;

    protected $_db_adapter;

    public function __construct(KConfig $config)
    {
        parent::__construct($config);

        $this->_parameter  = $this->getIdentifier($config->parameter);
        $this->_db_adapter = $config->db_adapter;
    }

    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'db_adapter' => 'koowa:database.adapter.mysqli',
            'parameter'  => 'com://admin/logman.activity.message.translator.parameter.default'));
        parent::_initialize($config);
    }

    public static function getInstance(KConfigInterface $config, KServiceInterface $container)
    {
        // Singleton behavior.
        $identifier = $config->service_identifier;
        $classname  = $identifier->classname;
        $instance   = new $classname($config);
        $container->set($identifier, $instance);
        return $instance;
    }

    /**
     * @see ComLogmanActivityMessageStrategyInterface::getParameters
     */
    final public function getParameters(KConfig $config)
    {
        $text = $config->text;

        if (!is_string($text)) {
            throw new KCommandException('Text must be a string');
        }

        $parameters = array();

        if (preg_match_all('/%(.+?)%/', $text, $matches) !== false) {

            foreach ($matches[1] as $replacement) {

                $method = '_get' . ucfirst($replacement);

                if (method_exists($this, $method)) {

                    $parameter = call_user_func(array($this, $method),
                        new KConfig(array('activity' => $config->activity, 'name' => $replacement)));

                    if (!$parameter instanceof ComLogmanActivityMessageTranslatorParameterInterface) {
                        throw new KException('Wrong parameter object type. ComLogmanActivityMessageTranslatorParameterInterface was expected.');
                    }

                    $parameters[] = $parameter;
                }
            }
        }

        return $parameters;
    }

    /**
     * Activity resource URL getter.
     *
     * @return string The relative un-routed URL.
     */
    protected function _getResourceUrl(ComActivitiesDatabaseRowActivity $activity)
    {
        if (version_compare(JVERSION, '1.6', '<')) {
            $url = 'index.php?option=com_' . $activity->package . '&task=edit&cid[]=' . $activity->row;
        } else {
            $url = 'index.php?option=com_' . $activity->package . '&task=' . $activity->name . '.edit&id=' . $activity->row;
        }

        return $url;
    }

    protected function _getActorUrl($actor_id)
    {
        if (version_compare(JVERSION, '1.6', '<')) {
            $url = 'index.php?option=com_users&view=user&task=edit&cid[]=' . $actor_id;
        } else {
            $url = 'index.php?option=com_users&task=user.edit&id=' . $actor_id;
        }

        return $url;
    }

    /**
     * Checks if the activity resource exists.
     *
     * @param array $config An optional configuration array.
     *
     * @return bool True if it exists, false otherwise.
     */
    protected function _resourceExists($config = array())
    {
        $config = new KConfig($config);

        $activity = $config->activity;

        $db = $this->getService($this->_db_adapter);

        $query = $db->getQuery()->from($config->table)
                 ->where($config->identity_column, '=', $activity->row)->count();

        // Need to catch exceptions here as table may not longer exists.
        try
        {
            $result = $db->select($query, KDatabase::FETCH_FIELD);
        } catch (Exception $e)
        {
            $result = 0;
        }

        return (bool) $result;
    }

    protected function _getParameter($config = array())
    {
        return $this->getService($this->_parameter, KConfig::unbox($config));
    }
}