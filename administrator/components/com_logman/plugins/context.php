<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

/**
 * LOGman context plugin.
 *
 * Provides a context list for activities you want to log in $_context.
 * Context that are not part of this list will be ignored.
 *
 * By default it saves the id and title columns of your data using _getDefaultSubject().
 * If you want to save additional data or your column names are customized,
 * you should provide a custom getter with the name of _getPackageResourceSubject()
 *
 * For saving additional data you should supply a metadata key in the array you return from your custom getter.
 *
 * For examples of use, please check PlgLogmanContent and PlgLogmanExtension.
 */
abstract class ComLogmanPluginContext extends ComLogmanPluginAbstract
{
    /**
     * A list of contexts for which activities will be logged.
     *
     * @var array
     */
    protected $_contexts = array();

    /**
     * Context based activity data getter.
     *
     * @see ComLogmanPluginInterface::getActivity()
     *
     * @param array Associative array containing context and event data.
     *
     * @return array|null The activity data, null if invalid/excluded context is given.
     */
    final public function getActivity($config)
    {
        $config = new KConfig($config);

        $context = $config->context;

        $parts = explode('.', $context);

        if (count($parts) == 2 && in_array($context, $this->_contexts)) {
            list($type, $package) = explode('_', $parts[0]);
            $method = '_get' . ucfirst($package) . ucfirst($parts[1]) . 'Subject';

            if (!method_exists($this, $method)) {
                $method = '_getDefaultSubject';
            }

            $subject = call_user_func(array($this, $method), $config->data);

            // Insert component and resource info.
            $subject['component'] = str_replace('com_', '', $parts[0]);
            $subject['resource']  = $parts[1];

            $config->subject = $subject;

            $activity = parent::getActivity($config);
        } else {
            $activity = null;
        }

        return $activity;
    }

    /**
     * Default subject data getter.
     *
     * Assumes that subject id and title maps to data id and title respectively.
     *
     * @param object $data The event data.
     *
     * @return array The subject data.
     */
    protected function _getDefaultSubject($data)
    {
        return array('id' => $data->id, 'title' => $data->title);
    }
}