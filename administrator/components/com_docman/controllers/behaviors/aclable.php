<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanControllerBehaviorAclable extends KControllerBehaviorAbstract
{
    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'priority' => KCommand::PRIORITY_HIGHEST
        ));

        parent::_initialize($config);
    }

    /**
     * Converts the default access level for new items to the one in global configuration
     *
     * @param KCommandContext $context
     */
    protected function _afterRead(KCommandContext $context)
    {
        if ($context->result->isNew()) {
            if ($this->getMixer()->getIdentifier()->name === 'document') {
                $context->result->access = -1;
            } elseif ($this->getMixer()->getIdentifier()->name === 'category') {
                $context->result->access = (int) (JFactory::getConfig()->get('access') || 1);
            }
        }
    }

    /**
     * Unsets certain options from the request if the user does not have access to save them.
     *
     * @param KCommandContext $context
     */
    protected function _beforeEdit(KCommandContext $context)
    {
        if (!$this->canAdmin()) {
            unset($context->data->rules);
        }
    }

    protected function _afterEdit(KCommandContext $context)
    {
        if ($this->getMixer()->getIdentifier()->name === 'category')
        {
            // Set the new access for child categories
            $children = $context->result->getDescendants();

            foreach ($children as $child) {
                if ($child->access_raw == -1) {
                    $child->access = $context->result->access;
                    $child->save();
                }
            }
        }

    }

    protected function _beforeAdd(KCommandContext $context)
    {
        return $this->_beforeEdit($context);
    }

}
