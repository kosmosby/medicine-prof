<?php
/**
 * @package     EXTman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class ComExtmanModelEntityExtension extends KModelEntityRow implements KObjectInstantiable
{
   	public static function getInstance(KObjectConfigInterface $config, KObjectManagerInterface $manager)
    {
        $identifier = $config->object_identifier->toArray();
        $koowa_component = false;

        if ($config->data)
        {
            // package comes from the controller add action
            if ($config->data->entity_package) {
                $identifier['package'] = $config->data->entity_package;
            }
            // identifier comes from table->select operation
            elseif ($config->data->identifier)
            {
                try
                {
                    $temporary = new KObjectIdentifier($config->data->identifier);
                    if ($temporary->type === 'com') {
                        $identifier['package'] = $temporary->package;
                    }
                }
                catch (KObjectExceptionInvalidIdentifier $e) {}
            }
            // dependency comes from _installDependencies method in this class
            elseif ($config->data->dependency)
            {
                $type = (string)$config->data->dependency['type'];

                if ($type === 'koowa-component') {
                    $koowa_component = true;
                }
            }

            if ($koowa_component || $config->data->type === 'koowa-component')
            {
                $koowa_component = true;

                $identifier['path'][] = 'extension';
                $identifier['name'] = 'koowa';
            }
        }

        $class = $manager->getClass($identifier);

        if($class === 'KModelEntityDefault' || !class_exists($class)) {
            $class = $koowa_component ? 'ComExtmanModelEntityExtensionKoowa' : 'ComExtmanModelEntityExtension';
        }

        $instance = new $class($config);
        return $instance;
    }

    public function save()
    {
        @ini_set('memory_limit', '256M');

        $this->_setup();

        $result = true;

        if ($this->source) {
            $this->_saveUUID();
        }

        if ($this->type !== 'koowa-component')
        {
            if ($this->install_method === 'discover_install' && $this->dependency) {
                $result = $this->_installFromFilesystem();
            }
            elseif ($this->package) {
                $result = $this->_installFromPackage();
            }
        }


        if ($result)
        {
            if ($this->joomla_extension_id)
            {
                $this->_setCoreExtension(true);

                if ($this->isNew() && $this->type === 'plugin') {
                    $this->_setExtensionStatus(true);
                }
            }

            parent::save();

            if ($this->parent_id) {
                $this->_addDependency();
            }

            // Joomla does not add asset entries when doing discover_install on components
            if ($this->type === 'component' && $this->install_method === 'discover_install') {
                $this->_createAsset($this->name, 1);
            }

            if ($manifest = $this->getManifest())
            {
                if ($manifest->dependencies) {
                    $this->_installDependencies($manifest->dependencies);
                }

                if ($manifest->deleted) {
                    $this->_deleteOldFiles($manifest->deleted);
                }
            }

            // Clear framework cache on each successful install for top level
            if (empty($this->parent_id)) {
                $this->clearCache();
            }
        }

        return $result;
    }

    public function delete()
    {
        $this->_setCoreExtension(false);

        $client_id = $this->type == 'component' ? 1 : 0;
        $result    = $this->getInstaller()->uninstall($this->type, $this->joomla_extension_id, $client_id);

        if ($result) {
            $result = parent::delete();
        }

        if ($result)
        {
            // Uninstall dependencies (if they are dependent to this extension only)
            foreach ($this->getDependents() as $dependency)
            {
                $count = $this->getObject('com://admin/extman.model.dependencies')->dependent_id($dependency->dependent_id)->count();

                if ($count === 1) {
                    $extension = $this->getObject('com://admin/extman.model.extensions')->id($dependency->dependent_id)->fetch();
                    $extension->delete();
                }

                $dependency->delete();
            }
        }

        return $result;
    }

    public function clearCache()
    {
        // Joomla does not clean up its plugins cache for us
        JCache::getInstance('callback', array(
            'defaultgroup' => 'com_plugins',
            'cachebase'    => JPATH_ADMINISTRATOR . '/cache'
        ))->clean();

        JFactory::getCache('com_koowa.tables', 'output')->clean();
        JFactory::getCache('com_koowa.templates', 'output')->clean();

        // Clear APC opcode cache
        if (extension_loaded('apc'))
        {
            apc_clear_cache();
            apc_clear_cache('user');
        }
    }

    public function getInstaller()
    {
        return new ComExtmanInstaller();
    }

    public function getManifest()
    {
        if (is_string($this->manifest)) {
            $this->manifest = simplexml_load_string($this->manifest);
        }

        return $this->manifest;
    }

    public function getDependents()
    {
        return $this->getObject('com://admin/extman.model.dependencies')->extman_extension_id($this->id)->fetch();
    }

    protected function _installFromPackage()
    {
        $installer = $this->getInstaller();
        $result    = $installer->install($this->package);

        if ($result)
        {
            if (is_string($this->identifier)) {
                $this->_data['identifier'] = new KObjectIdentifier($this->identifier);
            }

            $this->joomla_extension_id = self::getExtensionId(array(
                'type'      => $this->type,
                'element'   => $this->type == 'plugin' ? $this->identifier->name : $this->identifier->package,
                'folder'    => $this->type == 'plugin' ? $this->identifier->package : '',
                'client_id' => $this->identifier->domain === 'admin' || $this->type == 'component' ? 1 : 0
            ));
        }
        else
        {
            $this->setStatusMessage($installer->getInstallerError());
            $this->setStatus(KDatabase::STATUS_FAILED);
        }

        return $result;
    }

    protected function _installFromFilesystem()
    {
        $installer = $this->getInstaller();
        $installer->loadAllAdapters();

        $discovered = $installer->discover();

        $dependency   = $this->dependency;
        $extension_id = $this->getExtensionId($dependency);

        if ($extension_id)
        {
            $instance = JTable::getInstance('extension');
            $instance->load($extension_id);
        }
        else {
            $instance = $this->_findExtension($discovered, $dependency);

            if (!$instance) {
                throw new RuntimeException('Unable to find the dependent extension');
            }

            $instance->store();
        }

        $result = $installer->discover_install($instance->extension_id);

        if ($result)
        {
            if (is_string($this->identifier)) {
                $this->_data['identifier'] = new KObjectIdentifier($this->identifier);
            }

            $this->joomla_extension_id = self::getExtensionId(array(
                'type'      => $this->type,
                'element'   => $this->type == 'plugin' ? $this->identifier->name : $this->identifier->package,
                'folder'    => $this->type == 'plugin' ? $this->identifier->package : '',
                'client_id' => $this->identifier->domain === 'admin' || $this->type == 'component' ? 1 : 0
            ));
        }
        else
        {
            $this->setStatusMessage($installer->getInstallerError());
            $this->setStatus(KDatabase::STATUS_FAILED);
        }

        return $result;
    }

    protected function _findExtension($extensions, $search)
    {
        $type      = $search['type'];
        $element   = $search['element'];
        $folder    = isset($search['folder']) ? $search['folder'] : '';
        $client_id = isset($search['client_id']) ? $search['client_id'] : 0;

        if ($type == 'component') {
            $client_id = 1;
        }

        $return = null;

        foreach ($extensions as $extension)
        {
            if ($extension->type == (string) $type &&
                $extension->element == (string) $element &&
                $extension->folder == (string) $folder &&
                $extension->client_id == (int) $client_id
            ) {
                $return = $extension;
                break;
            }
        }

        return $return;
    }

    protected function _installDependencies($dependencies)
    {
        if (!is_object($dependencies)) {
            return false;
        }

        foreach ($dependencies->dependency as $dependency)
        {
            $properties = array(
                'dependency'     => $dependency,
                'install_method' => $this->install_method,
                'parent_id'      => $this->id
            );

            if ($this->install_method !== 'discover_install') {
                $properties['package'] = $this->source.'/'.(string)$dependency;
            }

            $this->getObject('com://admin/extman.model.entity.extension', array('data' => $properties))
                 ->save();
        }

        return true;
    }

    protected function _addDependency()
    {
        $data = array(
            'extman_extension_id' => $this->parent_id,
            'dependent_id' => $this->id
        );

        $entity = $this->getObject('com://admin/extman.model.dependencies')->setState($data)->fetch();

        if ($entity->isNew()) {
            return $entity->create($data)->save();
        }

        return true;
    }

    protected function _createAsset($name, $parent_id = 1)
    {
        $asset = JTable::getInstance('Asset');

        if (!$asset->loadByName($name))
        {
            $asset->name  = $name;
            $asset->title = $name;
            $asset->parent_id = $parent_id;
            $asset->rules = '{}';
            $asset->setLocation(1, 'last-child');

            return $asset->check() && $asset->store();
        }

        return true;
    }

    protected function _deleteOldFiles($node)
    {
        if (!is_object($node)) {
            return false;
        }

        foreach ($node->file as $file)
        {
            $path = JPATH_ROOT.'/'.(string)$file;

            if (file_exists($path)) {
                JFile::delete($path);
            }
        }

        foreach ($node->folder as $folder)
        {
            $path = JPATH_ROOT.'/'.(string)$folder;

            if (file_exists($path)) {
                JFolder::delete($path);
            }
        }

        return true;
    }

	protected function _setCoreExtension($value = true)
	{
		$value = (int) $value;
        $query = "UPDATE #__extensions SET protected = {$value}"
            . " WHERE extension_id = ".(int) $this->joomla_extension_id
            . " LIMIT 1";

		return JFactory::getDBO()->setQuery($query)->query();
	}

    protected function _setExtensionStatus($status)
    {
        $query = sprintf('UPDATE #__extensions SET enabled = %d WHERE extension_id = %d', $status, $this->joomla_extension_id);

        return JFactory::getDBO()->setQuery($query)->query();
    }

    protected function getPropertyJoomlatoolsUserId()
    {
        $result = null;

        if (!isset($this->_data['joomlatools_user_id']) && $this->type == 'component' && $this->parent_id == 0)
        {
            $component = $this->name;
            if (substr($component, 0, 4) !== 'com_') {
                $component  = 'com_'.$component;
            }

            $result = JComponentHelper::getParams($component)->get('joomlatools_user_id');

            $this->_data['joomlatools_user_id'] = $result;
        }

        return $result;
    }

    protected function setPropertyPackage($value)
    {
        $this->_data['package'] = $value;

        $this->_setup();

        return $value;
    }

    protected function setPropertyDependency($value)
    {
        $this->_data['dependency'] = $value;

        $this->_setup();

        return $value;
    }

    protected function _setup()
    {
        $this->_setupManifest();

        if ($this->manifest instanceof SimpleXMLElement)
        {
            $this->identifier = (string) $this->manifest->identifier;
            $this->name       = (string) $this->manifest->name;
            $this->version    = (string) $this->manifest->version;

            $existing = $this->getTable()->select(array('identifier' => $this->identifier), KDatabase::FETCH_ROW);

            if ($this->old_version === null && !$existing->isNew())
            {
                $this->setStatus(KDatabase::STATUS_FETCHED);

                $this->id                  = $existing->id;
                $this->old_version         = $existing->version;
                $this->joomlatools_user_id = $existing->joomlatools_user_id;
            }

            if (!$this->type) {
                $this->type = $this->_detectType();
            }
        }

        return true;
    }

    protected function _setupManifest()
    {
        if ($this->install_method === 'discover_install' && $this->dependency)
        {
            $dependency = $this->dependency;

            if (empty($dependency['type']) || empty($dependency['element'])) {
                throw new RuntimeException('Dependency type and element properties are required for discover_install to work');
            }

            $this->type = (string)$dependency['type'];
            $element    = (string)$dependency['element'];
            $folder     = isset($dependency['folder']) ? $dependency['folder'] : '';
            $client_id  = isset($dependency['client_id']) ? $dependency['client_id'] : 0;

            $path       = $this->_getManifestPath($this->type, $element, $folder, $client_id);

            if (!JFile::exists($path)) {
                return false;
            }

            $this->manifest = simplexml_load_file($path);
        }
        elseif ($this->package)
        {
            $installer = $this->getInstaller();
            $installer->setPath('source', $this->package);
            $installer->getManifest();

            if (!JFile::exists($installer->getPath('manifest'))) {
                return false;
            }

            $this->manifest = simplexml_load_file($installer->getPath('manifest'));
        }

        return true;
    }

    protected function _getManifestPath($type, $element, $folder = '', $client_id = 0)
    {
        if ($type == 'plugin' || $type == 'component') {
            $client_id = 0;
        }

        $base = JPATH_ROOT.($client_id == 1 ? '/administrator' : '');

        switch ($type) {
            case 'component':
                $path = sprintf('administrator/components/com_%1$s/%1$s.xml', $element);
                break;

            case 'module':
                $path = sprintf('modules/%1$s/%1$s.xml', $element);
                break;

            case 'plugin':
                $path = sprintf('plugins/%1$s/%2$s/%2$s.xml', $folder, $element);
                break;

            default:
                return false;
        }

        return $base.'/'.$path;
    }

    protected function _detectType()
    {
        static $type_map = array(
            'com'  => 'component',
            'mod'  => 'module',
            'plg'  => 'plugin'
        );

        try
        {
            $identifier = new KObjectIdentifier($this->identifier);
            $type       = $identifier->type;
        }
        catch (KObjectExceptionInvalidIdentifier $e) {
            $type = 'com';
        }

        $type = $type_map[$type];

        return $type;
    }

    /**
     * Stores the UUID in the package into the extensions table params column
     */
    protected function _saveUUID()
    {
        $uuid = $this->_getUUID($this->source);

        if ($uuid && $this->joomlatools_user_id != $uuid && !$this->_isLocal())
        {
            $component = $this->name;
            if (substr($component, 0, 4) !== 'com_') {
                $component  = 'com_'.$component;
            }

            $params = JComponentHelper::getParams($component);
            $params->set('joomlatools_user_id', $uuid);

            $componentId = JComponentHelper::getComponent($component)->id;

            $db    = JFactory::getDbo();
            $query = "UPDATE #__extensions SET params = ".$db->quote($params->toString())." WHERE `extension_id` = ". (int) $componentId;

            $db->setQuery($query)->query();

            $this->_data['joomlatools_user_id'] = $uuid;

            $this->user_id_saved = true;
        }
    }

    /**
     * Gets the subscriber's UUID from the package and removes it afterwards
     *
     * @param $package string Package path
     * @return null|string
     */
    protected function _getUUID($package)
    {
        $uuid = null;
        $file = $package.'/resources/install/.subscription';

        if(JFile::exists($file))
        {
            $uuid = trim(JFile::read($file));

            JFile::delete($file);
        }

        return $uuid;
    }

    protected function _isLocal()
    {
        $isLocal = false;
        $ip      = @$_SERVER['REMOTE_ADDR'];

        if(!empty($ip))
        {
            $isLoopback = preg_match('/^localhost$|^127(?:\.[0-9]+){0,2}\.[0-9]+$|^(?:0*\:)*?:?0*1$/', $ip) ? true : false;

            if(!$isLoopback)
            {
                $result  = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE);
                $isLocal = $result === false ? true : false;
            }
            else $isLocal = true;
        }

        return $isLocal;
    }

    public static function getExtensionId($extension)
    {
        $type    = (string)$extension['type'];
        $element = (string)$extension['element'];
        $folder  = isset($extension['folder']) ? (string)$extension['folder'] : '';
        $cid     = isset($extension['client_id']) ? (int) $extension['client_id'] : 0;

        if ($type == 'component') {
            $cid = 1;
        }

        if ($type == 'component' && substr($element, 0, 4) !== 'com_') {
            $element = 'com_'.$element;
        } elseif ($type == 'module' && substr($element, 0, 4) !== 'mod_') {
            $element = 'mod_'.$element;
        }

        $query = "SELECT extension_id FROM #__extensions
				WHERE type = '$type' AND element = '$element' AND folder = '$folder' AND client_id = '$cid'
				LIMIT 1";

        return JFactory::getDBO()->setQuery($query)->loadResult();
    }

}
