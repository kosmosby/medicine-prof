<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanDatabaseBehaviorPermissible extends KDatabaseBehaviorAbstract
{
    public static $task_map = array(
        'delete'   => 'core.delete',
        'add'      => 'core.create',
        'edit'     => 'core.edit',
        'download' => 'component.download'
    );

    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'priority'   => self::PRIORITY_LOWEST,
        ));

        parent::_initialize($config);
    }

    protected function _beforeInsert(KDatabaseContextInterface $context)
    {
        $this->_beforeUpdate($context);
    }

    protected function _beforeUpdate(KDatabaseContextInterface $context)
    {
        $entity = $context->data;

        if ($this->getMixer()->getIdentifier()->name === 'category' && !$entity->isModified('access'))
        {
            // Calculate the access
            if ($entity->access_raw == -1) // Inherit
            {
                if ($entity->parent_id) {
                    $entity->access = $this->getObject('com://admin/docman.model.categories')
                                        ->id($entity->parent_id)->fetch()->getProperty('access');
                } else {
                    $entity->access = (int)(JFactory::getConfig()->get('access') || 1);
                }
            }

            if ($entity->isModified('parent_id') || $entity->isModified('access_raw'))
            {
                if ($entity->access_raw != -1) {
                    $entity->access = $entity->access_raw;
                }

                if (!$entity->isNew())
                {
                    $children = $entity->getDescendants();
                    $excluded_parents = array();

                    foreach ($children as $child)
                    {
                        if ($child->access_raw != -1
                            || array_intersect($child->getParentIds(), $excluded_parents)
                        ) {
                            $excluded_parents[] = $child->id;
                            continue;
                        }

                        $child->access = $entity->access;
                        $child->save();
                    }
                }

            }
        }
    }

    protected function _afterDelete(KDatabaseContextInterface $context)
    {
        $asset = JTable::getInstance('Asset', 'JTable', array('dbo' => JFactory::getDbo()));
        $asset->loadByName($context->data->getAssetName());

        $asset->delete();
    }

    protected function _afterInsert(KDatabaseContextInterface $context)
    {
        return $this->_afterUpdate($context);
    }

    protected function _afterUpdate(KDatabaseContextInterface $context)
    {
        $rules = null;

        if (!empty($context->data->rules)) {
            $rules = new JAccessRules($this->_filterAccessRules($context->data->rules));
        }

        $parent_id = $this->_getAssetParentId($context->data);
        $name      = $this->getAssetName($context->data);
        $title     = $this->_getAssetTitle($context->data);

        $asset = JTable::getInstance('Asset', 'JTable', array('dbo' => JFactory::getDbo()));
        $asset->loadByName($name);

        // Check for an error.
        if ($asset->getError()) {
            return false;
        }

        // Specify how a new or moved node asset is inserted into the tree.
        if (empty($asset->id) || $asset->parent_id != $parent_id) {
            $asset->setLocation($parent_id, 'last-child');
        }

        // Prepare the asset to be stored.
        $asset->parent_id = $parent_id;
        $asset->name      = $name;
        $asset->title     = $title;

        if ($rules instanceof JAccessRules) {
            $asset->rules = (string)$rules;
        } elseif (empty($asset->rules)) {
            $asset->rules = '{}';
        }

        if (!$asset->check() || !$asset->store()) {
            return false;
        }

        if ($context->data->asset_id != $asset->id) {
            $context->data->asset_id = (int)$asset->id;
        }

        if ($context->data->isModified('asset_id')) {
            $this->getTable()->getCommandChain()->disable();
            $context->data->save();
            $this->getTable()->getCommandChain()->enable();
        }
    }

    public function getPermissions()
    {
        $section     = KStringInflector::singularize($this->getTable()->getIdentifier()->name);
        $component   = 'com_' . $this->getTable()->getIdentifier()->package;
        $actions     = JAccess::getActions($component, $section);
        $permissions = array();

        foreach ($actions as $action) {
            $permissions[$action->name] = $this->canPerform(substr($action->name, strrpos($action->name, '.') + 1));
        }

        $permissions['core.admin']  = $this->canPerform('admin');
        $permissions['core.manage'] = $this->canPerform('manage');

        return $permissions;
    }

    public function canPerform($action)
    {
        $user      = $this->getObject('user');
        $component = 'com_' . $this->getTable()->getIdentifier()->package;

        if (!$user->isAuthentic() && $action !== 'download') {
            return false;
        }

        // Users can add/edit/delete their own documents no matter what if allowed in the configuration.
        if (in_array($action, array('add', 'edit', 'delete')) && $this->created_by == $user->getId())
        {
            $parameter = $action === 'delete' ? 'can_delete_own' : 'can_edit_own';
            $page_id   = $this->getObject('request')->query->Itemid;

            if ($this->getObject('com://admin/docman.model.configs')->page($page_id)->fetch()->$parameter) {
                return true;
            }
        }

        if (in_array($action, array('admin', 'manage')))
        {
            $joomla_action = 'core.' . $action;
            $asset_name    = $component;
        }
        else
        {
            $joomla_action = isset(self::$task_map[$action]) ? self::$task_map[$action] : $action;
            $joomla_action = str_replace('component.', $component . '.', $joomla_action);
            $asset_name    = $this->getAssetName();
        }

        return (bool)$user->authorise($joomla_action, $asset_name);
    }

    public function getAsset()
    {
        return $this->getObject('com://admin/docman.model.assets')->name($this->getAssetName())->fetch();
    }

    public function getAssetName(KModelEntityInterface $entity = null)
    {
        $id      = $entity ? $entity->id : $this->id;
        $section = KStringInflector::singularize($this->getTable()->getIdentifier()->name);
        $package = $this->getTable()->getIdentifier()->package;

        return sprintf('com_%s.%s.%d', $package, $section, $id);
    }

    protected function _getAssetTitle(KModelEntityInterface $entity)
    {
        return $entity->title;
    }

    protected function _getAssetParentId(KModelEntityInterface $entity)
    {
        $name        = 'com_' . $this->getTable()->getIdentifier()->package;
        $table       = $this->getTable()->getIdentifier()->name;

        if ($table === 'categories')
        {
            $parent    = $entity->getParent();
            $parent_id = $entity->parent_id ? $entity->parent_id : ($parent ? $parent->id : 0);

            if ($parent_id) {
                $name = sprintf('%s.%s.%d', $name, 'category', $parent_id);
            }
        }
        elseif ($table === 'documents')
        {
            if ($this->docman_category_id)
            {
                $item = $this->getObject('com://admin/docman.model.categories')
                    ->id($this->docman_category_id)
                    ->fetch();

                if ($item->isPermissible()) {
                    $name = $item->getAssetName();
                }
            }
        }

        $asset_id = $this->getObject('com://admin/docman.model.assets')->name($name)->fetch()->getProperty('id');

        if (!$asset_id) {
            $asset_id = 1;
        }

        return $asset_id;
    }

    /**
     * This is hardcoded into JForm so need to copy here
     *
     * @param array $rules
     */
    protected function _filterAccessRules($rules)
    {
        $return = array();
        foreach ((array)$rules as $action => $ids)
        {
            // Build the rules array.
            $return[$action] = array();

            foreach ($ids as $id => $p) {
                if ($p !== '') {
                    $return[$action][$id] = ($p == '1' || $p == 'true') ? true : false;
                }
            }
        }

        return $return;
    }
}
