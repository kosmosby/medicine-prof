<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanDatabaseBehaviorAclable extends KDatabaseBehaviorAbstract
{
    public static $task_map = array(
        'delete'   => 'core.delete',
        'add'      => 'core.create',
        'edit'     => 'core.edit',
        'download' => 'component.download'
    );

    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'priority'   => KCommand::PRIORITY_LOWEST
        ));

        parent::_initialize($config);
    }

    protected function _beforeTableInsert(KCommandContext $context)
    {
        return $this->_beforeTableUpdate($context);
    }

    protected function _beforeTableUpdate(KCommandContext $context)
    {
        if ($this->getMixer()->getIdentifier()->name === 'category' && !$this->isModified('access'))
        {
            // Calculate the access
            if ($this->access_raw == -1) // Inherit
            {
                if ($this->parent_id)
                {
                    $parent = $this->getService('com://admin/docman.model.categories')->id($this->parent_id)->getItem();
                    $this->access = $parent->access;
                } else {
                    $this->access = (int) (JFactory::getConfig()->get('access') || 1);
                }
            } elseif ($this->isModified('access_raw')) {
                $this->access = $this->access_raw;
            }
        }
    }

    protected function _afterTableDelete(KCommandContext $context)
    {
        $asset = JTable::getInstance('Asset', 'JTable', array('dbo' => JFactory::getDbo()));
        $asset->loadByName($this->getAssetName());

        $asset->delete();
    }

    protected function _afterTableInsert(KCommandContext $context)
    {
        return $this->_afterTableUpdate($context);
    }

    protected function _afterTableUpdate(KCommandContext $context)
    {
        $rules = null;

        if (!empty($this->rules)) {
            $rules = new JAccessRules($this->_filterAccessRules($this->rules));
        }

        $parent_id = $this->_getAssetParentId();
        $name = $this->getAssetName();
        $title = $this->_getAssetTitle();

        $asset = JTable::getInstance('Asset', 'JTable', array('dbo' => JFactory::getDbo()));
        $asset->loadByName($name);

        // Check for an error.
        if ($error = $asset->getError()) {
            return false;
        }

        // Specify how a new or moved node asset is inserted into the tree.
        if (empty($asset->id) || $asset->parent_id != $parent_id) {
            $asset->setLocation($parent_id, 'last-child');
        }

        // Prepare the asset to be stored.
        $asset->parent_id = $parent_id;
        $asset->name = $name;
        $asset->title = $title;

        if ($rules instanceof JAccessRules) {
            $asset->rules = (string) $rules;
        }

        if (!$asset->check() || !$asset->store()) {
            return false;
        }

        if ($this->asset_id != $asset->id) {
            $this->asset_id = (int) $asset->id;
        }

        if ($this->isModified('asset_id')) {
            $this->save();
        }
    }

    public function getPermissions()
    {
        $section     = KInflector::singularize($this->getTable()->getIdentifier()->name);
        $component   = 'com_'.$this->getTable()->getIdentifier()->package;
        $actions     = JAccess::getActions($component, $section);
        $permissions = array();

        foreach ($actions as $action) {
            $name = substr($action->name, strrpos($action->name, '.')+1);
            $permissions[$action->name] = $this->canPerform($name);
        }

        $permissions['core.admin']  = $this->canPerform('admin');
        $permissions['core.manage'] = $this->canPerform('manage');

        return $this->getService('com://admin/docman.object.permissions', array('data' => $permissions));
    }

    public function canPerform($action)
    {
        $user      = JFactory::getUser();
        $component = 'com_'.$this->getTable()->getIdentifier()->package;

        if ($user->guest && $action !== 'download') {
            return false;
        }

        // Users can edit/delete their documents no matter what
        if (in_array($action, array('edit', 'delete')) && $this->created_by == $user->id) {
            return true;
        }

        if (in_array($action, array('admin', 'manage'))) {
            $joomla_action = 'core.'.$action;
            $asset_name    = $component;
        } else {
            $joomla_action = isset(self::$task_map[$action]) ? self::$task_map[$action] : $action;
            $joomla_action = str_replace('component.', $component.'.', $joomla_action);
            $asset_name = $this->getAssetName();
        }

        return (bool) $user->authorise($joomla_action, $asset_name);
    }

    public function getAsset()
    {
        return $this->getService('com://admin/docman.model.assets')->name($this->getAssetName())->getItem();
    }

    public function getAssetName()
    {
        $section = KInflector::singularize($this->getTable()->getIdentifier()->name);
        $package = $this->getTable()->getIdentifier()->package;

        return sprintf('com_%s.%s.%d', $package, $section, $this->id);
    }

    protected function _getAssetTitle()
    {
        return $this->title;
    }

    protected function _getAssetParentId()
    {
        $asset_model = $this->getService('com://admin/docman.model.assets');
        $asset_id = 0;

        $name = 'com_'.$this->getTable()->getIdentifier()->package;

        if ($this->getTable()->getIdentifier()->name === 'categories') {
            // TODO: revisit this:
            // If I use $this->getParent() all hell breaks loose because of recursive chains.
            $id_column = $this->getTable()->getIdentityColumn();
            $query = $this->getTable()->getDatabase()->getQuery();
            $query->select('r.ancestor_id')
            ->from('#__'.$this->getTable()->getName().' AS tbl')
            ->join('inner', '#__'.$this->getTable()->getRelationTable().' AS r', 'tbl.'.$id_column.' = r.ancestor_id')
            ->where('r.descendant_id', '=', (int) $this->id)
            ->where('r.level', '=', 1);

            $parent = $this->getTable()->getDatabase()->select($query, KDatabase::FETCH_FIELD);

            if ($parent) {
                $section = KInflector::singularize($this->getTable()->getIdentifier()->name);
                $package = $this->getTable()->getIdentifier()->package;
                $name = sprintf('com_%s.%s.%d', $package, $section, $parent);
            }
        } elseif ($this->getTable()->getIdentifier()->name === 'documents')
        {
            if ($this->docman_category_id) {
                $category_name = null;
                $item = $this->getService('com://admin/docman.model.categories')
                            ->id($this->docman_category_id)->getItem();
                if ($item->isAclable()) {
                    $category_name = $item->getAssetName();
                }

                if (!empty($category_name)) {
                    $name = $category_name;
                }
            }
        }

        $asset_id = $asset_model->name($name)->getItem()->id;

        if (!$asset_id) {
            $asset_id = 1;
        }

        return $asset_id;
    }

    /**
     * Some people thought hardcoding this into JForm was a good idea... So need to copy here
     * @param array $rules
     */
    protected function _filterAccessRules($rules)
    {
        $return = array();
        foreach ((array) $rules as $action => $ids) {
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
