<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComLogmanTemplateHelperActivity extends KTemplateHelperDefault implements KServiceInstantiatable
{
    public static function getInstance(KConfigInterface $config, KServiceInterface $container)
    {
        if (!$container->has($config->service_identifier)) {
            //Create the singleton
            $classname = $config->service_identifier->classname;
            $instance  = new $classname($config);
            $container->set($config->service_identifier, $instance);
        }

        return $container->get($config->service_identifier);
    }

    public function ip($config = array())
    {
        $config = new KConfig($config);

        $row = $config->row;

        $ip    = htmlspecialchars($row->ip);
        $link  = 'index.php?option=com_logman&view=activities&ip=' . $ip;
        $title = $this->translate('Filter by IP');

        return '<a title="' . $title . '" href="' . $link . '">' . $ip . '</a>';
    }

    public function message($config = array())
    {
        $config = new KConfig($config);

        $config->append(array(
            'message'     => 'com://admin/logman.activity.message.default',
            'formatted'   => true,
            'escape_html' => false
        ));

        $message = $this->getService($config->message,
            array_merge($config->toArray(), array('activity' => $config->row)));

        return (string) $message;
    }

    public function when($config = array())
    {
        $config = new KConfig($config);

        $config->append(array('humanize' => false, 'format' => '%H:%M:%S'));

        $activity = $config->row;

        $helper = $this->getService('com://admin/logman.template.helper.date');

        if ($config->humanize) {
            $time = $helper->humanize(array(
                'date' => $activity->created_on));
        } else {
            $time = $helper->format(array(
                'date'   => $activity->created_on,
                'format' => $config->format));
        }

        return $time;
    }
}