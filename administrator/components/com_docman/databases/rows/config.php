<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanDatabaseRowConfig extends KDatabaseRowAbstract implements KServiceInstantiatable
{
    /**
     * Joomla asset cache
     *
     * @var JTableAsset
     */
    protected static $_asset;

    /**
     * Result of the getLoginToDownload() method cache
     *
     * @var boolean
     */
    protected static $_login_to_download;

    public function __construct($config = array())
    {
        parent::__construct($config);

        if (!empty($config->auto_load)) {
            $this->load();
        }
    }

    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'auto_load' => true
        ));

        parent::_initialize($config);
    }

    /**
     * Force creation of a singleton
     *
     * @param 	object 	An optional KConfig object with configuration options
     * @param 	object	A KServiceInterface object
     * @return KDatabaseTableInterface
     */
    public static function getInstance(KConfigInterface $config, KServiceInterface $container)
    {
        if (!$container->has($config->service_identifier))
        {
            $classname = $config->service_identifier->classname;
            $instance  = new $classname($config);
            $container->set($config->service_identifier, $instance);
        }

        return $container->get($config->service_identifier);
    }

    public function isNew()
    {
        return false;
    }

    public function getFilesContainer()
    {
        return $this->getService('com://admin/files.model.containers')->slug('docman-files')->getItem();
    }

    public function load()
    {
        $this->setData(JComponentHelper::getParams('com_docman')->toArray());

        $row        = $this->getFilesContainer();
        $parameters = $row->getParameters();

        $this->document_path = $row->path_value;

        foreach (array('thumbnails', 'allowed_extensions', 'maximum_size', 'allowed_mimetypes') as $key) {
            $this->$key = $parameters->$key;
        }

        $this->login_to_download = $this->getLoginToDownload();
    }

    public function getLoginToDownload()
    {
        if (self::$_login_to_download !== null) {
            return self::$_login_to_download;
        }

        $result = null;

        $asset = $this->_getAsset();

        $rules = new JAccessRules($asset->rules);
        $rules = $rules->getData();

        if (!isset($rules['com_docman.download'])) {
            $result = false;
        }

        if ($result !== false)
        {
            $download = $rules['com_docman.download'];

            // 1: Public, 2: Registered, 6: Manager
            if ($download->allow(1) === null && $download->allow(2) === true && $download->allow(6) === true) {
                $result = true;
            } else {
                $result = false;
            }
        }

        self::$_login_to_download = $result;

        return $result;
    }

    public function setLoginToDownload($value)
    {
        $asset = $this->_getAsset();

        $rules = new JAccessRules($asset->rules);
        $rules = $rules->getData();

        $old = $rules['com_docman.download']->getData();

        // 1: Public, 2: Registered, 6: Manager
        if ($value) {
            $data = array(
                '2' => true,
                '6' => true
            );
        } else {
            $data = array(
                '1' => true
            );
        }

        foreach ($old as $group => $permission) {
            if (!in_array($group, array('1', '2', '6'))) {
                $data[$group] = $permission;
            }
        }

        $rules['com_docman.download'] = new JAccessRule($data);
        $rules = new JAccessRules($rules);

        $asset->rules = (string) $rules;

        return $asset->check() && $asset->store();
    }

    protected function _getAsset()
    {
        if (!self::$_asset instanceof JTableAsset) {
            self::$_asset = JTable::getInstance('Asset');
            self::$_asset->loadByName('com_docman');
        }

        return self::$_asset;
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

    public function save()
    {
        // System variables shoulnd't be saved
        foreach (array('_token', 'option', 'action', 'format', 'layout', 'task') as $var) {
            unset($this->_data[$var]);
            unset($this->_modified[$var]);
        }

        if (!empty($this->rules)) {
            $rules	= new JAccessRules($this->_filterAccessRules($this->rules));
            $asset	= JTable::getInstance('asset');

            if (!$asset->loadByName('com_docman')) {
                $root	= JTable::getInstance('asset');
                $root->loadByName('root.1');
                $asset->name = 'com_docman';
                $asset->title = 'com_docman';
                $asset->setLocation($root->id, 'last-child');
            }
            $asset->rules = (string) $rules;

            if (!($asset->check() && $asset->store())) {
                $translator = $this->getService('translator')->getTranslator($this->getIdentifier());
                JFactory::getApplication()->enqueueMessage($translator->translate('Changes to the ACL rules could not be saved.'), 'warning');
            }

            unset($this->_data['rules']);
        } else {
            if ($this->login_to_download != $this->getLoginToDownload()) {
                $this->setLoginToDownload($this->login_to_download);
            }
        }

        if (!empty($this->_data['allowed_extensions']) && is_string($this->_data['allowed_extensions'])) {
            $this->allowed_extensions = explode(',', $this->_data['allowed_extensions']);
        }

        // Auto-set allowed mimetypes based on the extensions
        if (!empty($this->allowed_extensions)) {
            $mimetypes = array_values($this->getService('com://admin/docman.model.mimetypes')
                    ->extension($this->allowed_extensions)
                    ->getList()
                    ->getColumn('mimetype'));
            $this->allowed_mimetypes = array_merge($this->allowed_mimetypes, $mimetypes);
        }

        // If the document path changed try to move the files to their new location
        $row = $this->getFilesContainer();
        $this->_saveDocumentPath($row);

        // These are all going to be saved into com_files
        $data = array();
        foreach (array('thumbnails', 'allowed_extensions', 'maximum_size', 'allowed_mimetypes', 'document_path') as $key => $var) {
            $value = $this->$var;

            if ($var === 'thumbnails') {
                $value = (bool) $value;
            }

            if (!empty($value) || ($value === false || $value === 0 || $value === '0')) {
                $data[$var] = $value;
            }
            unset($this->_data[$var]);
            unset($this->_modified[$var]);
        }
        unset($data['document_path']);

        $row->parameters->setData($data);

        // KDatabaseRow cannot detect changes in the object properties so this is needed.
        $row->parameters = $row->parameters;
        $row->save();

        // Get the jos_extensions row entry for DOCman
        $row = $this->getService('com://admin/components.database.table.extensions', array(
            'name' => 'extensions',
            'identity_column' => 'extension_id'
        ))->select(array('element' => 'com_docman'), KDatabase::FETCH_ROW);

        $registry = new JRegistry();
        $registry->loadArray($this->getData());

        $row->params = $registry->toString();
        $result      = $row->save();

        $this->setStatus($result ? KDatabase::STATUS_UPDATED : KDatabase::STATUS_FAILED);

        return $result;
    }

    protected function _saveDocumentPath(KDatabaseRowInterface $row)
    {
        if (!$this->isModified('document_path')) {
            return;
        }

        $translator = $this->getService('translator')->getTranslator($this->getIdentifier());
        $from       = $this->getFilesContainer()->path_value;
        $path       = trim($this->document_path, '\\/');

        if ($from === $path) {
            return;
        }

        if (!preg_match('#^[0-9A-Za-z_\-\/]+$#', $path)) {
            JFactory::getApplication()->enqueueMessage($translator->translate('Document path can only contain letters, numbers, dash or underscore'), 'error');

            return;
        }

        $db = JFactory::getDBO();
        $query = sprintf("SELECT COUNT(*) FROM #__menu WHERE alias = %s", $db->quote($path));
        if ($db->setQuery($query)->loadResult()) {
            JFactory::getApplication()->enqueueMessage(
                $translator->translate('A menu item on your site uses this path as its alias. In order to ensure that your site works correctly, the document path was left unchanged.'),
                'error'
            );

            return;
        }

        jimport('joomla.filesystem.folder');

        $row->path = $path;
        if ($row->save()) {
            if (JFolder::move(JPATH_ROOT.'/'.$from, JPATH_ROOT.'/'.$path) !== true) {
                JFactory::getApplication()->enqueueMessage(
                    $translator->translate('Changes are saved but you should move existing files manually from folder "%from%" to "%to%" at your site root in order to make existing files visible.',
                        array('%from%' => $from, '%to%' => $path)
                    ), 'warning'
                );
            }
        }
    }

    public function count()
    {
        $count =  count($this->_data);

        return $count;
    }

    public function __get($column)
    {
        $result = parent::__get($column);

        if (in_array($column, array('allowed_extensions', 'allowed_mimetypes')) && !is_array($result)) {
            return array();
        }

        return $result;
    }
}
