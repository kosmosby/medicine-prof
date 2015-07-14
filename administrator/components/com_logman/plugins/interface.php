<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

/**
 * LOGman plugin interface.
 */
interface ComLogmanPluginInterface
{
    /**
     * Adds an activity row.
     *
     * @param mixed $activity The activity data.
     *
     * @return object The activity row.
     */
    public function save($activity);

    /**
     * Parameter getter.
     *
     * @param      $name    The name of the setting parameter.
     * @param null $default The default value.
     *
     * @return mixed The parameter or default value.
     */
    public function getParameter($name, $default = null);

    /**
     * Activity data getter.
     *
     * Provides formatted activity data given event data.
     *
     * @param $config May contain event data, context, etc.
     *
     * @return mixed The activity data.
     */
    public function getActivity($config);
}