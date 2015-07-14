<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanViewBehaviorNavigatable extends KViewBehaviorAbstract
{
    protected function _afterRender(KViewContext $context)
    {
        $params = $this->getParameters();
        $query  = $this->getActiveMenu()->query;

        if (isset($query['layout']) && in_array($query['layout'], array('tree', 'treetable'))
            && $this->getMixer()->getLayout() !== 'form'
        )
        {
            $state    = $this->getModel()->getState();
            $selected = null;

            if ($this->getMixer()->getName() === 'document') {
                $selected = $this->getModel()->fetch()->docman_category_id;
            }
            else {
                $selected = $this->getModel()->fetch()->id;
            }

            $data = array(
                'state' => array(
                    'enabled'       => $state->enabled,
                    'access'        => $state->access,
                    'current_user'  => $this->getObject('user')->getId(),
                    'page'          => $state->page,
                    'sort'          => $params->sort_categories
                ),
                'selected' => $selected
            );

            $context->result = $this->getTemplate()
                ->loadFile('com://site/docman.tree.default.html')
                ->render($data);
        }
    }
}