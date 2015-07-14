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
class ComDocmanControllerBehaviorListPersistable extends ComDocmanControllerBehaviorPersistable
{
    protected function _beforeGet(KCommandContext $context)
    {
        return $this->_beforeBrowse($context);
    }

    protected function _afterGet(KCommandContext $context)
    {
        return $this->_afterBrowse($context);
    }
}
