<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
interface ComLogmanActivityMessageInterface
{
    /**
     * Activity setter.
     *
     * @param ComActivitiesDatabaseRowActivity The activity instance.
     *
     * @return ComLogmanActivityMessageInterface This.
     */
    public function setActivity(ComActivitiesDatabaseRowActivity $activity);

    /**
     * Activity getter.
     *
     * @return ComActivitiesDatabaseRowActivity The activity instance.
     */
    public function getActivity();

    /**
     * Returns the activity message as a string.
     *
     * @return string The activity message.
     */
    public function __toString();
}