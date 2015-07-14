<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanModelConfigs extends KModelAbstract
{
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->getState()->insert('page', 'int');
    }

    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'state' => 'com://admin/docman.model.config.state',
        ));

        parent::_initialize($config);
    }

    protected function _actionFetch(KModelContext $context)
    {
        $item = clone $this->getObject('com://admin/docman.model.entity.config');

        // Override can_edit_own and can_delete_own from menu parameters
        if (JFactory::getApplication()->isSite())
        {
            $parameters = null;

            if ($page = $this->getState()->page) {
                $parameters = JFactory::getApplication()->getMenu()->getItem($page)->params;
            }
            elseif ($page = JFactory::getApplication()->getMenu()->getActive()) {
                $parameters = $page->params;
            }

            if ($parameters)
            {
                if ($parameters->get('can_edit_own') != '') {
                    $item->can_edit_own = $parameters->get('can_edit_own');
                }

                if ($parameters->get('can_delete_own') != '') {
                    $item->can_delete_own = $parameters->get('can_delete_own');
                }
            }
        }

        return $item;
    }
}
