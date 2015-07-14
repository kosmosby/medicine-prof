<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
interface ComLogmanActivityMessageStrategyInterface
{
    /**
     * Activity message icon class getter.
     *
     * @param KConfig The configuration object.
     *
     * @return string The icon class of the activity message.
     */
    public function getIcon(KConfig $config);

    /**
     * Activity message text getter.
     *
     * @param KConfig The configuration object.
     *
     * @return string The activity message text.
     */
    public function getText(KConfig $config);

    /**
     * Activity message content getter.
     *
     * May be used to add/pass extra HTML code to the message.
     *
     * @param KConfig The configuration object.
     *
     * @return string The HTML content of the activity message.
     */
    public function getContent(KConfig $config);

    /**
     * Activity message parameters getter.
     *
     * @param KConfig The configuration object.
     *
     * @return array An array containing the activity message parameters.
     */
    public function getParameters(KConfig $config);
}