<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanViewCategoryHtml extends ComDocmanViewHtml
{
    protected function _fetchData(KViewContext $context)
    {
        parent::_fetchData($context);

        $context->data->parent          = $this->getModel()->fetch()->getParent();
        $context->data->default_access = $this->getObject('com://admin/docman.model.viewlevels')
            ->id((int) (JFactory::getConfig()->get('access') || 1))
            ->fetch();

        $category = $context->data->category;
        $ignored_parents = array();

        if ($category->id) {
            $ignored_parents[] = $category->id;
            foreach ($category->getDescendants() as $descendant) {
                $ignored_parents[] = $descendant->id;
            }
        }

        $context->data->ignored_parents = $ignored_parents;
    }
}
