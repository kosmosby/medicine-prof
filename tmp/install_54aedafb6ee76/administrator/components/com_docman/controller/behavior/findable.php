<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2012 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanControllerBehaviorFindable extends ComKoowaControllerBehaviorFindable
{
    /**
     * Initializes the options for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   KObjectConfig $config Configuration options
     * @return void
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'entity' => 'document',
        ));

        parent::_initialize($config);
    }

    /**
     * Only add new items to the index if they have a frontend link
     *
     * @param KControllerContextInterface $context
     */
    protected function _afterAdd(KControllerContextInterface $context)
    {
        $name = $this->getMixer()->getIdentifier()->name;

        if ($name === $this->_entity && $this->getModel()->findPage($context->result)) {
            parent::_afterAdd($context);
        }
    }
}
