<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

/**
 * Document controller permissions
 */
class ComDocmanControllerPermissionDocument extends ComDocmanControllerPermissionAbstract
{
    public function canAdd()
    {
        $result = parent::canAdd();

        // FIXME: layout=form is not used anymore
        if ($this->getModel()->getState()->isUnique() || $this->getRequest()->query->layout === 'form')
        {
            $data        = $this->getRequest()->data;
            $category_id = !empty($data->docman_category_id) ? $data->docman_category_id : $this->getRequest()->query->get('docman_category_id', 'int');

            if ($category_id)
            {
                $category = $this->getObject('com://admin/docman.model.categories')->id((int)$category_id)->fetch();
                if ($category && $category->isPermissible()) {
                    $result = $category->canPerform('add');
                }
            }
        }

        return (bool) $result;
    }
}