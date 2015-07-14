<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanModelEntityExtension extends ComExtmanModelEntityExtension
{
    protected function _createFilesContainer()
    {
        $entity = $this->getObject('com:files.model.containers')->slug('docman-files')->fetch();

        if ($entity->isNew())
        {
            $thumbnails = true;

            if (!extension_loaded('gd'))
            {
                $thumbnails = false;
                $translator = $this->getObject('translator');
                JFactory::getApplication()->enqueueMessage($translator->translate('Your server does not have the necessary GD image library for thumbnails.'));
            }

            $extensions = explode(',', 'csv,doc,docx,html,key,keynote,odp,ods,odt,pages,pdf,pps,ppt,pptx,rtf,tex,txt,xls,xlsx,xml,7z,ace,bz2,dmg,gz,rar,tgz,zip,bmp,exif,gif,ico,jpeg,jpg,png,psd,tif,tiff,aac,aif,aiff,alac,amr,au,cdda,flac,m3u,m3u,m4a,m4a,m4p,mid,mp3,mp4,mpa,ogg,pac,ra,wav,wma,3gp,asf,avi,flv,m4v,mkv,mov,mp4,mpeg,mpg,ogg,rm,swf,vob,wmv');

            $entity->create(array(
                'slug' => 'docman-files',
                'path' => 'joomlatools-files/docman-files',
                'title' => 'DOCman',
                'parameters' => array(
                    'allowed_extensions' => $extensions,
                    'allowed_mimetypes' => array("image/jpeg", "image/gif", "image/png", "image/bmp", "application/x-shockwave-flash", "application/msword", "application/excel", "application/pdf", "application/powerpoint", "text/plain", "application/x-zip"),
                    'maximum_size' => 0,
                    'thumbnails' => $thumbnails
                )
            ));
            $entity->save();
        }
        elseif ($this->old_version && version_compare($this->old_version, '2.0.0beta2', '<='))
        {
            // Path encoding got removed in beta3
            $path = $entity->fullpath;
            $rename = array();
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($iterator as $f)
            {
                $name = $f->getFilename();
                if ($name === rawurldecode($name)) {
                    continue;
                }

                $rename[$f->getPathname()] = $f->getPath().'/'.rawurldecode($name);
            }

            foreach ($rename as $from => $to) {
                rename($from, $to);
            }
        }

        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');

        if (!$entity->isNew() && $entity->path)
        {
            $path = JPATH_ROOT.'/'.$entity->path;
            if (!JFolder::exists($path))
            {
                if (!JFolder::create($path)) {
                    JFactory::getApplication()->enqueueMessage(JText::_('Document path cannot be automatically created. Please create the folder structure joomlatools-files/docman-files in your site root.'), 'error');
                }
            }

            if (!JFile::exists($path.'/.htaccess')) {
                $buffer ='DENY FROM ALL';
                JFile::write($path.'/.htaccess', $buffer);
            }

            if (!JFile::exists($path.'/web.config')) {
                $buffer ='<?xml version="1.0" encoding="utf-8" ?>
<system.webServer>
    <security>
        <authorization>
            <remove users="*" roles="" verbs="" />
            <add accessType="Allow" roles="Administrators" />
        </authorization>
    </security>
</system.webServer>';
                JFile::write($path.'/web.config', $buffer);
            }
        }
    }

    protected function _createIconsContainer()
    {
        $entity = $this->getObject('com:files.model.containers')->slug('docman-icons')->fetch();
        $path = 'joomlatools-files/docman-icons';

        if ($entity->isNew())
        {
            $entity->create(array(
                'slug' => 'docman-icons',
                'path' => $path,
                'title' => 'DOCman Icons',
                'parameters' => array(
                    'allowed_extensions' => explode(',', 'bmp,gif,jpeg,jpg,png'),
                    'allowed_mimetypes' => array("image/jpeg", "image/gif", "image/png", "image/bmp"),
                    'maximum_size' => 0,
                    'thumbnails' => true
                )
            ));
            $entity->save();
        }

        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');

        if (!JFolder::exists(JPATH_ROOT.'/'.$path)) {
            JFolder::create(JPATH_ROOT.'/'.$path);
        }
    }

    protected function _createImagesContainer()
    {
        $entity = $this->getObject('com:files.model.containers')->slug('docman-images')->fetch();
        $path = 'joomlatools-files/docman-images';

        if ($entity->isNew())
        {
            $entity->create(array(
                'slug' => 'docman-images',
                'path' => $path,
                'title' => 'DOCman Images',
                'parameters' => array(
                    'allowed_extensions' => explode(',', 'bmp,gif,jpeg,jpg,png'),
                    'allowed_mimetypes'  => array("image/jpeg", "image/gif", "image/png", "image/bmp"),
                    'maximum_size'       => 0,
                    'thumbnails'         => false
                )
            ));

            $entity->save();
        }

        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');

        if (!JFolder::exists(JPATH_ROOT.'/'.$path)) {
            JFolder::create(JPATH_ROOT.'/'.$path);
        }
    }

    /**
     * @return bool
     */
    public function save()
    {
        $result = parent::save();

        if ($result)
        {
            $this->_createFilesContainer();
            $this->_createIconsContainer();
            $this->_createImagesContainer();

            if (file_exists(dirname(__FILE__).'/../../resources/install/mimetypes.sql'))
            {
                $mimetypes = file_get_contents(dirname(__FILE__).'/../../resources/install/mimetypes.sql');

                if ($mimetypes)
                {
                    try
                    {
                        $db = JFactory::getDBO();
                        $queries = $db->splitSql($mimetypes);
                        foreach($queries as $query)
                        {
                            if (trim($query)) {
                                $db->setQuery($query)->execute();
                            }
                        }
                    }
                    catch (Exception $e) {}
                }
            }

            if ($this->event === 'install')
            {
                // Add a rule to authorize Public group to download
                $asset = JTable::getInstance('Asset');
                $asset->loadByName('com_docman');

                $rules = new JAccessRules($asset->rules);
                $rules->mergeAction('com_docman.download', new JAccessRule(array(1 => true)));
                $asset->rules = (string) $rules;

                if ($asset->check()) {
                    $asset->store();
                }

                // Disable finder plugin by default
                $finder_id = $this->getExtensionId(array(
                    'type'    => 'plugin',
                    'element' => 'docman',
                    'folder'  => 'finder',
                ));

                if ($finder_id)
                {
                    $query = sprintf('UPDATE #__extensions SET enabled = 0 WHERE extension_id = %d', $finder_id);
                    JFactory::getDBO()->setQuery($query)->query();
                }
            }

            if ($this->old_version)
            {
                JCache::getInstance('output', array('defaultgroup' => 'com_docman.files'))->clean();

                $this->_migrate();
            }
        }

        return $result;
    }

    protected function _migrate()
    {
        if (version_compare($this->old_version, '2.0.0beta3', '<'))
        {
            // we added a new column to the categories table in beta3
            $query = 'ALTER TABLE `#__docman_categories` ADD COLUMN `access_raw` int(11) NOT NULL default 0 AFTER `access`';
            JFactory::getDBO()->setQuery($query)->query();
        }

        if (version_compare($this->old_version, '2.0.0RC1', '<'))
        {
            // executable.png and text.png is removed from icons in rc1
            $search = '"icon":"executable.png"';
            $replace = '"icon":"default.png"';
            $query = sprintf("UPDATE `#__docman_documents` SET `params` = REPLACE(`params`, '%s', '%s') WHERE `params` LIKE '%s'", $search, $replace, '%'.$search.'%');
            JFactory::getDBO()->setQuery($query)->query();

            $search = '"icon":"text.png"';
            $replace = '"icon":"document.png"';
            $query = sprintf("UPDATE `#__docman_documents` SET `params` = REPLACE(`params`, '%s', '%s') WHERE `params` LIKE '%s'", $search, $replace, '%'.$search.'%');
            JFactory::getDBO()->setQuery($query)->query();

            // http storage is renamed to remote in rc1
            $query = "UPDATE `#__docman_documents` SET `storage_type` = 'remote' WHERE `storage_type` = 'http'";
            JFactory::getDBO()->setQuery($query)->query();

            // cache structure is changed. clean old cache folders
            jimport('joomla.filesystem.folder');
            $folders = JFolder::folders(JPATH_CACHE, '^com_docman');
            foreach ($folders as $folder) {
                JFolder::delete(JPATH_CACHE.'/'.$folder);
            }

            // thumbnail column is now a mediumtext in com_files
            $query = "ALTER TABLE `#__files_thumbnails` MODIFY `thumbnail` MEDIUMTEXT";
            JFactory::getDBO()->setQuery($query)->query();
        }

        if (JComponentHelper::getComponent('com_docman')->id) {
            $this->_migrateMenuItems();
        }

        // Google document viewer and Days for new got moved into menu parameters in RC1
        if (version_compare($this->old_version, '2.0.0RC1', '<'))
        {
            $config = KObjectManager::getInstance()->getObject('com://admin/docman.model.entity.config');
            unset($config->preview_with_gdocs);
            unset($config->days_for_new);
            $config->save();
        }

        // hits field got added in RC2
        $table = $this->getObject('com://admin/docman.database.table.documents');
        if (!isset($table->getSchema()->columns['hits']))
        {
            $query = 'ALTER TABLE `#__docman_documents` ADD COLUMN `hits` int(11) NOT NULL default 0 AFTER `storage_path`';
            JFactory::getDBO()->setQuery($query)->query();
        }

        // category_index got added in 2.0.0
        $indexes = $table->getSchema()->indexes;
        if (!isset($indexes['category_index']))
        {
            $query = 'ALTER TABLE `#__docman_documents` ADD INDEX `category_index` (`docman_category_id`)';
            JFactory::getDBO()->setQuery($query)->query();
        }

        // icons do not have file extensions anymore
        if (version_compare($this->old_version, '2.0.0', '<'))
        {
            $icons = array('archive', 'audio', 'default', 'document', 'folder', 'image', 'pdf', 'spreadsheet', 'video');
            $query_template = "UPDATE `#__docman_%s` SET `params` = REPLACE(`params`, '%s', '%s') WHERE `params` LIKE '%s'";

            foreach ($icons as $icon)
            {
                $search  = '"icon":"'.$icon.'.png"';
                $replace = '"icon":"'.$icon.'"';
                JFactory::getDBO()->setQuery(sprintf($query_template, 'documents', $search, $replace, '%'.$search.'%'))->query();
                JFactory::getDBO()->setQuery(sprintf($query_template, 'categories', $search, $replace, '%'.$search.'%'))->query();
            }
        }

        // can_edit_own and can_delete_own was added in 2.0.0
        if (version_compare($this->old_version, '2.0.0', '<'))
        {
            $config = KObjectManager::getInstance()->getObject('com://admin/docman.model.entity.config');
            $config->can_edit_own = 1;
            $config->can_delete_own = 1;
            $config->save();
        }

        // module parameter structure was changed in 2.0.0
        if (version_compare($this->old_version, '2.0.0', '<')) {
            $this->_migrateModules();
        }
    }

    protected function _migrateModules()
    {
        $table   = KObjectManager::getInstance()->getObject('com://admin/docman.database.table.modules', array('name' => 'modules'));
        $modules = $table->select(array('module' => 'mod_docman_documents'));

        foreach ($modules as $module)
        {
            $parameters = json_decode($module->params);

            if (!$parameters || empty($parameters->page)) {
                continue;
            }

            $page = $parameters->page;

            if (is_array($page))
            {
                if (count($page) === 1) {
                    $page = $page[0];
                }
                elseif (is_array($page)) {
                    $page = '';
                }
            }

            $parameters->page = $page;

            $module->params = json_encode($parameters);
            $module->save();
        }
    }

    protected function _migrateMenuItems()
    {
        $id     = JComponentHelper::getComponent('com_docman')->id;
        $table  = KObjectManager::getInstance()->getObject('com://admin/docman.database.table.menus', array('name' => 'menu'));
        $items  = $table->select(array('component_id' => $id));
        $config = $this->getObject('com://admin/docman.model.entity.config');

        foreach ($items as $item)
        {
            if ($item->menutype === 'main') {
                continue;
            }

            parse_str(str_replace('index.php?', '', $item->link), $query);

            $view = isset($query['view']) ? $query['view'] : null;

            if ($view === 'documents')
            {
                // documents view got renamed to filteredlist in RC1
                $item->link = str_replace('view=documents', 'view=filteredlist', $item->link);

                $view = 'filteredlist';
            }
            elseif ($view === 'category' || $view === 'categories')
            {
                // category and categories view got consolidated into list view in beta3
                $slug = $view === 'categories' ? '' : $query['slug'];

                $item->link = 'index.php?option=com_docman&view=list&slug='.$slug;

                if (!empty($item->params))
                {
                    $params = json_decode($item->params);

                    $params->show_subcategories = '1';

                    if ($view === 'categories') {
                        $params->sort_documents = 'title';
                    }

                    unset($params->show_document_count);

                    $item->params = json_encode($params);
                }

                $view = 'list';
            }

            if (in_array($view, array('document', 'filteredlist', 'list')) && $this->old_version)
            {
                $params = json_decode($item->params);

                // preview_with_gdocs and days_for_new got moved into menu items in 2.0.0RC1
                if (version_compare($this->old_version, '2.0.0RC1', '<'))
                {
                    $params->preview_with_gdocs = $config->preview_with_gdocs;
                    $params->days_for_new = $config->days_for_new;
                }

                // New parameters were added in 2.0.0
                if (version_compare($this->old_version, '2.0.0', '<'))
                {
                    $params->show_document_popular = 1;
                    $params->show_document_hits = 1;
                    $params->hits_for_popular = 100;
                    $params->can_edit_own = '';
                    $params->can_delete_own = '';

                    if ($view === 'list')
                    {
                        $params->show_categories_header = '1';
                        $params->show_documents_header = '1';
                    }
                }

                $item->params = json_encode($params);
            }

            $item->save();
        }
    }

    public function delete()
    {
        $result = parent::delete();

        if ($result)
        {
            $db = JFactory::getDbo();

            /*
             * Sometimes installer messes up and leaves stuff behind. Remove them too when uninstalling
             */
            $query = "DELETE FROM #__menu WHERE link = 'index.php?option=com_docman' AND component_id = 0 LIMIT 1";
            $db->setQuery($query);
            $db->query();

            $db = JFactory::getDBO();
            $db->setQuery('SHOW TABLES LIKE '.$db->quote($db->replacePrefix('#__files_containers')));
            if ($db->loadResult()) {
                $db->setQuery("DELETE FROM `#__files_containers` WHERE `slug` = 'docman-files'");
                $db->query();
                $db->setQuery("DELETE FROM `#__files_containers` WHERE `slug` = 'docman-icons'");
                $db->query();
                $db->setQuery("DELETE FROM `#__files_containers` WHERE `slug` = 'docman-images'");
                $db->query();
            }

            JFactory::getCache()->clean('com_docman');
        }

        return $result;
    }
}
