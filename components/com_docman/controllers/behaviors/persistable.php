<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

/**
 * Specialized Persistable class to use browse events for list views
 *
 * List views do not use controller browse events and gather their data from the model in the views.
 * So we rewire the events to before and after get events in this class.
 */
class ComDocmanControllerBehaviorPersistable extends KControllerBehaviorPersistable
{
    /**
     * Unset these keys before saving the state to the session
     * @var array
     */
    protected static $_unset = array(
        'page',
        'page_conditions',
        'current_user',
        'access',
        'access_raw',
        'enabled',
        'published'
    );

    /**
     * Saves the model state in the session
     *
     * @param 	KCommandContext		The active command context
     * @return 	void
     */
    protected function _afterBrowse(KCommandContext $context)
    {
        $model  = $this->getModel();
        $state  = $model->get();

        foreach (self::$_unset as $key) {
            unset($state[$key]);
        }

        // Built the session identifier based on the action
        $identifier  = $model->getIdentifier().'.'.$context->action;

        //Prevent unused state information from being persisted
        KRequest::set('session.'.$identifier, null);

        //Set the state in the session
        KRequest::set('session.'.$identifier, $state);
    }
}
