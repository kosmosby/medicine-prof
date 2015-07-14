<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
class PlgLogmanUsers extends ComLogmanPluginAbstract
{
    /**
     * @var JUser A copy of the current user object instance to be shared among events.
     */
    protected $_user;


    /**
     * @var JUser An instance of the current group object to be shared among events.
     */
    protected $_group = null;

    /**
     * J!1.5 user login event handler.
     *
     * @param $user
     * @param $options
     */
    public function onLoginUser($user, $options)
    {
        // Same as J!2.5
        $this->onUserLogin($user, $options);
    }

    /**
     * J!2.5 user login event handler.
     *
     * @param $user
     * @param $options
     */
    public function onUserLogin($user, $options)
    {
        // Check if login events should be logged.
        if ($this->getParameter('log_login_events')) {
            $user = JUser::getInstance($user['username']);

            $activity = $this->getActivity(array(
                'subject' => array(
                    'component' => 'users',
                    'resource'  => 'user',
                    'id'        => $user->id,
                    'title'     => $user->name),
                'action'  => 'login',
                'actor'   => $user->id,
                'result'  => 'logged in'));

            $this->save($activity);
        }
    }

    /**
     * J!1.5 user logout event handler.
     *
     * @param $user
     * @param $options
     */
    public function onLogoutUser($user, $options)
    {
        // Same as J!2.5
        $this->onUserLogout($user, $options);
    }

    /**
     * J!2.5 user logout event handler.
     *
     * @param $user
     * @param $options
     */
    public function onUserLogout($user, $options)
    {
        if ($this->getParameter('log_login_events')) {
            // On J!1.5, user is always logged out after a delete call. In this case we are better
            // using the cached user object.
            $user = $this->_user ? $this->_user : JFactory::getUser($user['id']);

            $current = JFactory::getUser();

            if ($current->id == $user->id) {
                // User is logging himself out.
                $actor = $user->id;
            } else {
                // User if being logged out by someone else.
                $actor = $current->id;
            }

            $activity = $this->getActivity(array(
                'subject' => array(
                    'component' => 'users',
                    'resource'  => 'user',
                    'id'        => $user->id,
                    'title'     => $user->name),
                'action'  => 'logout',
                'actor'   => $actor,
                'result'  => 'logged out'
            ));

            $this->save($activity);
        }
    }

    /**
     * J!1.5 before user delete event handler.
     *
     * @param $user
     */
    public function onBeforeDeleteUser($user)
    {
        // Same as J!2.5
        return $this->onUserBeforeDelete($user);
    }

    /**
     * J!2.5 before user delete event handler.
     *
     * @param $user
     */
    public function onUserBeforeDelete($user)
    {
        // Keep a copy of the user to be used on the after delete event.
        $this->_user = clone JFactory::getUser($user['id']);
    }

    /**
     * After user group save event handler.
     *
     * @param $context
     * @param $data
     * @param $isNew
     */
    public function onUserAfterSaveGroup($context, $data, $isNew)
    {
        $activity = $this->getActivity(array(
            'subject' => array(
                'component' => 'users',
                'resource'  => 'group',
                'id'        => $data->id,
                'title'     => $data->title),
            'action'  => $isNew ? 'add' : 'edit'));

        $this->save($activity);
    }

    /**
     * Before user group delete event handler.
     *
     * @param $group_properties
     */
    public function onUserBeforeDeleteGroup($group_properties)
    {
        // Store a copy of the group instance for future use.
        $group = JTable::getInstance('Usergroup', 'JTable');
        $group->load($group_properties['id']);
        $this->_group = $group;
    }

    /**
     * After user group delete event handler.
     *
     * @param $group_properties
     * @param $mysterious_arg
     * @param $error
     */
    public function onUserAfterDeleteGroup($group_properties, $mysterious_arg, $error)
    {
        if (!$error) {
            $activity = $this->getActivity(array(
                'subject' => array(
                    'component' => 'users',
                    'resource'  => 'group',
                    'id'        => $this->_group->id,
                    'title'     => $this->_group->title),
                'action'  => 'delete'));

            $this->save($activity);
        }
    }

    /**
     * J!1.5 after user store (save) event handler.
     *
     * @param $user
     * @param $isNew
     * @param $success
     * @param $msg
     */
    public function onAfterStoreUser($user, $isNew, $success, $msg)
    {
        // Same as J!2.5
        return $this->onUserAfterSave($user, $isNew, $success, $msg);
    }

    /**
     * J!2.5 after user save event handler.
     *
     * @param $user
     * @param $isNew
     * @param $success
     * @param $msg
     */
    public function onUserAfterSave($user, $isNew, $success, $msg)
    {
        if ($success) {
            $activity = $this->getActivity(array(
                'subject' => array(
                    'component' => 'users',
                    'resource'  => 'user',
                    'id'        => $user['id'],
                    'title'     => $user['name']),
                'action'  => $isNew ? 'add' : 'edit'));

            $this->save($activity);
        }
    }

    /**
     * J!1.5 after user delete event handler.
     *
     * @param      $user
     * @param null $success
     * @param null $msg
     */
    public function onAfterDeleteUser($user, $success = null, $msg = null)
    {
        // Same as J!2.5
        $this->onUserAfterDelete($user, $success, $msg);
    }

    /**
     * J!2.5 after user delete event handler.
     *
     * @param $user
     * @param $success
     * @param $msg
     */
    public function onUserAfterDelete($user, $success, $msg)
    {
        if ($success) {
            $activity = $this->getActivity(array(
                'subject' => array(
                    'component' => 'users',
                    'resource'  => 'user',
                    'id'        => $this->_user->id,
                    'title'     => $this->_user->name),
                'action'  => 'delete'));

            $this->save($activity);
        }
    }
}