<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanDatabaseRowExtension extends ComExtmanDatabaseRowExtension
{
    protected function _createFilesContainer()
    {
        $row = $this->getService('com://admin/files.model.containers')->slug('docman-files')->getItem();
        if ($row->isNew()) {
            $thumbnails = true;
            if (!extension_loaded('gd') && !extension_loaded('imagick')) {
                JFactory::getApplication()->enqueueMessage('Your server does not have necessary image libraries for thumbnails. You need either GD or Imagemagick installed to make thumbnails visible.');
                $thumbnails = false;
            }

            $max_size = $this->convertToBytes(ini_get('upload_max_filesize'));
            $extensions = explode(',', 'csv,doc,docx,html,key,keynote,odp,ods,odt,pages,pdf,pps,ppt,pptx,rtf,tex,txt,xls,xlsx,xml,7z,ace,bz2,dmg,gz,rar,tgz,zip,bmp,exif,gif,ico,jpeg,jpg,png,psd,tif,tiff,aac,aif,aiff,alac,amr,au,cdda,flac,m3u,m3u,m4a,m4a,m4p,mid,mp3,mp4,mpa,ogg,pac,ra,wav,wma,3gp,asf,avi,flv,m4v,mkv,mov,mp4,mpeg,mpg,ogg,rm,swf,vob,wmv');

            $row->setData(array(
                'path' => 'joomlatools-files/docman-files',
                'title' => 'DOCman',
                'parameters' => json_encode((object) array(
                    'allowed_extensions' => $extensions,
                    'allowed_mimetypes' => array("image/jpeg", "image/gif", "image/png", "image/bmp", "application/x-shockwave-flash", "application/msword", "application/excel", "application/pdf", "application/powerpoint", "text/plain", "application/x-zip"),
                    'maximum_size' => $max_size,
                    'thumbnails' => $thumbnails
                ))
            ));
            $row->save();
            $row->slug = 'docman-files';
            $row->save();

            jimport('joomla.filesystem.folder');
            jimport('joomla.filesystem.file');

            $path = JPATH_ROOT.'/'.$row->path_value;
            if (!JFolder::exists($path)) {
                if (!JFolder::create($path)) {
                    JFactory::getApplication()->enqueueMessage(JText::_('Document path cannot be automatically created. Please create the folder structure joomlatools-files/docman-files in your site root.'), 'error');
                }
            }

            if (!JFile::exists($path.'/.htaccess')) {
                file_put_contents($path.'/.htaccess', 'DENY FROM ALL');
            }
        }
        elseif ($this->old_version && version_compare($this->old_version, '2.0.0beta2', '<='))
        {
            // Path encoding got removed in beta3
            $path = $row->path;
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
    }

    protected function _createIconsContainer()
    {
        $row = $this->getService('com://admin/files.model.containers')->slug('docman-icons')->getItem();
        $path = 'joomlatools-files/docman-icons';

        if ($row->isNew()) {
            $row->setData(array(
                'path' => $path,
                'title' => 'DOCman Icons',
                'parameters' => json_encode((object) array(
                    'allowed_extensions' => explode(',', 'bmp,gif,jpeg,jpg,png'),
                    'allowed_mimetypes' => array("image/jpeg", "image/gif", "image/png", "image/bmp"),
                    'maximum_size' => $this->convertToBytes(ini_get('upload_max_filesize')),
                    'thumbnails' => true
                ))
            ));
            $row->save();
            $row->slug = 'docman-icons';
            $row->save();
        } else {
            $row->parameters->thumbnails = true;
            // KDatabaseRow cannot detect changes in the object properties so this is needed.
            $row->parameters = $row->parameters;
            $row->save();
        }

        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');

        if (!JFolder::exists(JPATH_ROOT.'/'.$path)) {
            JFolder::create(JPATH_ROOT.'/'.$path);
        }
    }

    protected function _createImagesContainer()
    {
        $row = $this->getService('com://admin/files.model.containers')->slug('docman-images')->getItem();
        $path = 'joomlatools-files/docman-images';

        if ($row->isNew()) {
            $row->setData(array(
            'path' => $path,
            'title' => 'DOCman Images',
            'parameters' => json_encode((object) array(
                'allowed_extensions' => explode(',', 'bmp,gif,jpeg,jpg,png'),
                'allowed_mimetypes' => array("image/jpeg", "image/gif", "image/png", "image/bmp"),
                'maximum_size' => $this->convertToBytes(ini_get('upload_max_filesize')),
                'thumbnails' => false
            ))
            ));
            $row->save();
            $row->slug = 'docman-images';
            $row->save();
        }

        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');

        if (!JFolder::exists(JPATH_ROOT.'/'.$path)) {
            JFolder::create(JPATH_ROOT.'/'.$path);
        }
    }

    protected function _removeMenuEntry()
    {

    }

    /**
     * @return bool
     */
    public function save()
    {
        $result = parent::save();

        if ($result) {
            $this->_createFilesContainer();
            $this->_createIconsContainer();
            $this->_createImagesContainer();

            if (file_exists(dirname(__FILE__).'/../../install/mimetypes.sql')) {
                $query = file_get_contents(dirname(__FILE__).'/../../install/mimetypes.sql');
                if ($query) {
                    $db = JFactory::getDBO();
                    $db->setQuery($query);
                    $db->queryBatch(false);
                }
            }

            // Remove com_files from the menu table
            $db = JFactory::getDBO();
            $db->setQuery("SELECT id FROM #__menu WHERE link = 'index.php?option=com_files'");
            $id = $db->loadResult();
            if ($id) {
                $table = JTable::getInstance('menu');
                $table->bind(array('id' => $id));
                $table->delete();
            }

            // Add a rule to authorize Public group to download
            if ($this->event === 'install') {
                $asset = JTable::getInstance('Asset');
                $asset->loadByName('com_docman');

                $rules = new JAccessRules($asset->rules);
                $rules->mergeAction('com_docman.download', new JAccessRule(array(1 => true)));
                $asset->rules = (string) $rules;

                if ($asset->check()) {
                    $asset->store();
                }

                unset($asset);

                $asset = JTable::getInstance('Asset');
                $asset->loadByName('com_docman');

                $rules = new JAccessRules($asset->rules);
                $rules->mergeAction('com_docman.upload', new JAccessRule(array(6 => true, 2 => true)));
                $asset->rules = (string) $rules;

                if ($asset->check()) {
                    $asset->store();
                }
            }

            if ($this->old_version) {
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
            $query = 'ALTER TABLE `#__docman_categories` ADD COLUMN `access_raw` int(11) NOT NULL default -1 AFTER `access`';
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
            $config = KService::get('com://admin/docman.database.row.config');
            unset($config->preview_with_gdocs);
            unset($config->days_for_new);
            $config->save();
        }

    }

    protected function _migrateMenuItems()
    {
        $id = JComponentHelper::getComponent('com_docman')->id;
        $table = KService::get('com://admin/docman.database.table.menus', array('name' => 'menu'));
        $items = $table->select(array('component_id' => $id));

        // Google document viewer and Days for new got moved into menu parameters in RC1
        if (version_compare($this->old_version, '2.0.0RC1', '<'))
        {
            $config = $this->getService('com://admin/docman.database.row.config');
            $preview_with_gdocs  = $config->preview_with_gdocs;
            $days_for_new        = $config->days_for_new;
        }

        foreach ($items as $item)
        {
            if ($item->menutype === 'main') {
                continue;
            }

            parse_str(str_replace('index.php?', '', $item->link), $query);

            $view = $query['view'];

            if (isset($preview_with_gdocs) && in_array($view, array('document', 'documents', 'category', 'categories', 'filteredlist', 'list')))
            {
                $params = json_decode($item->params);

                $params->preview_with_gdocs = $preview_with_gdocs;
                $params->days_for_new = $days_for_new;

                $item->params = json_encode($params);
            }

            if ($view === 'documents')
            {
                // documents view got renamed to filteredlist in RC1
                $item->link = str_replace('view=documents', 'view=filteredlist', $item->link);
            }
            elseif ($view === 'category' || $view === 'categories')
            {
                // category and categories view got consolidated into list view in beta3
                $slug = $view === 'categories' ? '' : $query['slug'];

                $item->link = 'index.php?option=com_docman&view=list&slug='.$slug;

                if (!empty($item->params)) {
                    $params = json_decode($item->params);

                    $params->show_subcategories = '1';
                    $params->show_documents = '1';

                    if ($view === 'categories') {
                        $params->sort_documents = 'title';
                    }

                    unset($params->show_document_count);

                    $item->params = json_encode($params);
                }
            }

            $item->save();
        }
    }

    public function convertToBytes($value)
    {
        $keys = array('k', 'm', 'g');
        $last_char = strtolower(substr($value, -1));
        $value = (int) $value;

        if (in_array($last_char, $keys)) {
            $value *= pow(1024, array_search($last_char, $keys)+1);
        }

        return $value;
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
