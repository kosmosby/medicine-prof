<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanControllerBehaviorPermissions extends ComDefaultControllerBehaviorExecutable
{
    protected $_context;

    /**
     * Saves the context to a protected variable and runs the checks
     *
     * Always returns true for non-dispatched requests
     *
     * @param                 $name
     * @param KCommandContext $context
     * @return bool
     */
    public function execute($name, KCommandContext $context)
    {
        if (!$this->isDispatched()) {
            return true;
        }

        $this->_context = $context;

        return parent::execute($name, $context);
    }

    /**
     * This will return a list of resources that the controller will act on.
     * An empty array is returned if no resource is specificied. This happens
     * when methods like canEditState are called to determine if the buttons
     * in toolbar should be shown.
     */
    protected function _getList()
    {
        $model = clone $this->getModel();
        $state = $this->getModel()->getState()->getData(true);
        if (empty($state)) {
            return array();
        }

        $model->reset()->set($state);

        return $model->getList();
    }

    public function canAdmin()
    {
        return JFactory::getUser()->authorise('core.admin', 'com_docman') === true;
    }

    public function canManage()
    {
        return JFactory::getUser()->authorise('core.manage', 'com_docman') === true;
    }

    /**
     * Generic authorize handler for controller add actions
     *
     * @return boolean Can return both true or false.
     */
    public function canAdd()
    {
        $result = JFactory::getUser()->authorise('core.create', 'com_docman');

        if ($this->_mixer->getIdentifier()->name === 'file') {
            $result = JFactory::getUser()->authorise('com_docman.upload', 'com_docman');
        }
        elseif ($this->getModel()->getState()->isUnique() || $this->getRequest()->layout === 'form')
        {
            $data = $this->_context->data;
            $name = $this->_mixer->getIdentifier()->name;
            $category = null;
            $category_id = !empty($data->docman_category_id) ? $data->docman_category_id : $this->getRequest()->docman_category_id;

            if ($name === 'document' && $category_id) {
                $category = $this->getService('com://admin/docman.model.categories')->id($category_id)->getItem();
            } elseif ($name === 'list' || $name === 'category') {
                $category = $this->getModel()->getItem();
            }

            if ($category && $category->isAclable()) {
                $result = $category->canPerform('add');
            }
        }

        return (bool) $result;
    }

    /**
     * Generic authorize handler for controller edit actions
     *
     * @return boolean Can return both true or false.
     */
    public function canEdit()
    {
        $result  = JFactory::getUser()->authorise('core.edit', 'com_docman');
        $user_id = JFactory::getUser()->id;

        if (in_array($this->_mixer->getIdentifier()->name, array('category', 'list', 'document')))
        {
            foreach ($this->_getList() as $item)
            {
                if ($item->isAclable()) {
                    $result = $item->canPerform('edit');
                }

                if (!$result) {
                    break;
                }
            }
        }

        return (bool) $result;
    }

    public function canDelete()
    {
        $result  = JFactory::getUser()->authorise('core.edit', 'com_docman');
        $user_id = JFactory::getUser()->id;

        if (in_array($this->_mixer->getIdentifier()->name, array('category', 'list', 'document')))
        {
            foreach ($this->_getList() as $item)
            {
                if ($item->isAclable()) {
                    $result = $item->canPerform('delete');
                }

                if (!$result) {
                    break;
                }
            }
        } elseif ($this->_mixer->getIdentifier()->name === 'file') {
            $result = JFactory::getUser()->authorise('com_docman.upload', 'com_docman');
        }

        return (bool) $result;
    }

    public function canChangeAnything()
    {
        return count(self::getAuthorisedCategories(array('core.create', 'core.edit')))
            || count(self::getAuthorisedDocuments(array('core.create', 'core.edit')));
    }

    // TODO cache the results
    public static function getAuthorisedCategories(array $actions)
    {
        $db    = JFactory::getDbo();
        $user  = JFactory::getUser();
        $query = $db->getQuery(true)
            ->select('c.docman_category_id AS id, a.name AS asset_name')
            ->from('#__docman_categories AS c')
            ->innerJoin('#__assets AS a ON c.asset_id = a.id')
            ->where('c.enabled = 1');

        $db->setQuery($query);
        $all = $db->loadObjectList('id');
        $allowed = array();

        foreach ($all as $category) {
            foreach ($actions as $action) {
                if ($user->authorise($action, $category->asset_name)) {
                    $allowed[] = (int) $category->id;
                }
            }
        }

        return $allowed;
    }

    // TODO: cache the results
    public static function getAuthorisedDocuments(array $actions)
    {
        $db    = JFactory::getDbo();
        $user  = JFactory::getUser();
        $query = $db->getQuery(true)
            ->select('d.docman_document_id AS id, a.name AS asset_name')
            ->from('#__docman_documents AS d')
            ->innerJoin('#__assets AS a ON d.asset_id = a.id')
            ->where('d.enabled = 1');

        $db->setQuery($query);
        $all = $db->loadObjectList('id');
        $allowed = array();

        foreach ($all as $document) {
            foreach ($actions as $action) {
                if ($user->authorise($action, $document->asset_name)) {
                    $allowed[] = (int) $document->id;
                }
            }
        }

        return $allowed;
    }
}
