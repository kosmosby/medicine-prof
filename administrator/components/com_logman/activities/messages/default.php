<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
class ComLogmanActivityMessageDefault extends KObject implements KServiceInstantiatable, ComLogmanActivityMessageInterface
{
    protected $_translator;

    protected $_formatted;

    protected $_escape_html;

    protected $_icon;

    protected $_activity;

    protected $_strategy;

    static protected $_strategy_map;

    public function __construct(KConfig $config)
    {
        parent::__construct($config);

        $this->setActivity($config->activity);
        $this->_translator  = $this->getService($config->translator);
        $this->_formatted   = $config->formatted;
        $this->_escape_html = $config->escape_html;
        $this->_icon        = $config->icon;

        if (!$config->strategy) {
            $this->_strategy = $this->_findStrategy();
        } else {
            $this->_strategy = $config->strategy;
        }
    }

    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'icon'        => true,
            'formatted'   => true,
            'escape_html' => false,
            'translator'  => 'com://admin/logman.activity.message.translator.default'));
        parent::_initialize($config);
    }

    public static function getInstance(KConfigInterface $config, KServiceInterface $container)
    {
        $identifier = clone $config->service_identifier;
        $activity   = $config->activity;

        if ($identifier->name != $activity->package) {

            $identifier->name = $activity->package;

            if (file_exists($identifier->filepath)) {
                // Use package activity message instead.
                return KService::get($identifier, $config->toArray());
            }
        }

        $classname = $config->service_identifier->classname;
        return new $classname($config);
    }

    public function setActivity(ComActivitiesDatabaseRowActivity $activity)
    {
        $this->_activity = $activity;
    }

    public function getActivity()
    {
        return $this->_activity;
    }

    public function setStrategy($strategy)
    {
        if (is_string($strategy)) {
            if (strpos($strategy, '.') === false) {
                $identifier       = clone $this->getIdentifier();
                $identifier->path = array('activity', 'message', 'strategy');
                $identifier->name = $strategy;
            } else {
                $identifier = $strategy;
            }

            $strategy = $this->getIdentifier($identifier);
        }

        if ($strategy instanceof KServiceIdentifier) {
            $strategy = $this->getService($strategy);
        }

        if (!$strategy instanceof ComLogmanActivityMessageStrategyInterface) {
            throw new KException('Activity messsage strategies must implement ComLogmanActivityMessageStrategyInterface.');
        }

        $this->_strategy = $strategy;

        return $this;
    }

    /**
     * Strategy identifier getter.
     *
     * @return string The strategy identifier for the current activity.
     */
    protected function _findStrategy()
    {
        $activity = $this->getActivity();

        /* Fallback logic:
         * 1. Strategy object for the package within its filesystem.
         * 2. Stategy object for the package within LOGman.
         * 3. Default strategy object.
         */
        if (!isset(self::$_strategy_map[$activity->package])) {
            $identifiers = array(
                'com://admin/' . $activity->package . '.activity.message.strategy.' . $activity->package,
                'com://admin/logman.activity.message.strategy.' . $activity->package,
                'com://admin/logman.activity.message.strategy.default');

            foreach ($identifiers as $identifier) {
                $instance = new KServiceIdentifier($identifier);
                if (file_exists($instance->filepath)) {
                    break;
                }
            }
            // Keep track of the package strategy map.
            self::$_strategy_map[$activity->package] = $identifier;
        } else {
            $identifier = self::$_strategy_map[$activity->package];
        }

        return $identifier;
    }

    public function getStrategy()
    {
        if (!$this->_strategy instanceof ComLogmanActivityMessageStrategyInterface) {
            $this->setStrategy($this->_strategy);
        }
        return $this->_strategy;
    }

    public function getTranslator()
    {
        return $this->_translator;
    }

    public function __toString()
    {
        $activity = $this->getActivity();

        $text = $this->getStrategy()->getText(new KConfig(array('activity' => $activity)));

        $text = $this->getTranslator()
            ->message($text,
                $this->getStrategy()->getParameters(new KConfig(array(
                    'text'     => $text,
                    'activity' => $activity))));

        $text = '<span class="text">' . $text . '</span>';

        if ($this->_escape_html) {
            $text = htmlspecialchars($text);
        }

        if (!$this->_formatted) {
            $text = strip_tags($text);
        }

        // Append icon (if any).
        if (($icon = $this->_icon) && $this->_formatted && !$this->_escape_html) {
            $message = '<i class="' . $this->getStrategy()
                ->getIcon(new KConfig(array('activity' => $activity))) . '"></i>&nbsp;' . $text;

            if ($content = $this->getStrategy()->getContent(new KConfig(array('activity' => $activity)))) {
                $message .= $content;
            }
        }

        return $message;
    }
}