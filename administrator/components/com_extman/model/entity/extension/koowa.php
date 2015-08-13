<?php
/**
 * @package     EXTman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComExtmanModelEntityExtensionKoowa extends ComExtmanModelEntityExtension
{
    public function save()
    {
        $this->_setup();

        $result = true;

        if ($this->install_method === 'discover_install' && $this->dependency) {
            $result = $this->_installFromFilesystem();
        }
        elseif ($this->package) {
            $result = $this->_installFromPackage();
        }

        $path = JPATH_ROOT.'/libraries/koowa/components/com_'.$this->name;

        $this->getObject('object.bootstrapper')->registerComponent($this->name, $path, 'koowa');

        if ($result) {
            parent::save();

            if ($this->parent_id) {
                $this->_addDependency();
            }
        }

        return $result;
    }

    public function delete()
    {
        $result = $this->_uninstallKoowaComponent();

        if ($result) {
            $result = KDatabaseRowAbstract::delete();
        }

        return $result;
    }

    protected function _setupManifest()
    {
        if ($this->install_method === 'discover_install') {
            return parent::_setupManifest();
        }
        elseif ($this->package)
        {
            $koowa_manifests = JFolder::files($this->package, 'koowa-component\.xml$', true, true);

            if (!empty($koowa_manifests))
            {
                $this->type = 'koowa-component';
                $this->manifest = simplexml_load_file($koowa_manifests[0]);
            }
        }

        return true;
    }

    protected function _getManifestPath($type, $element, $folder = '', $client_id = 0)
    {
        return JPATH_ROOT.sprintf('/libraries/koowa/components/com_%s/koowa-component.xml', $element);
    }

    protected function _installFromFilesystem()
    {
        return $this->_installFromPackage();
    }

    protected function _installFromPackage()
    {
        if (!$this->manifest) {
            return false;
        }

        $folders = $this->_getKoowaComponentDirectories();

        if ($this->install_method !== 'discover_install')
        {

            // new structure
            if (file_exists($this->package.'/koowa-component.xml'))
            {
                $map = array(
                    $this->package                     => JPATH_ROOT.'/'.$folders['code'],
                    $this->package.'/resources/assets' => JPATH_ROOT.'/'.$folders['media']
                );
            }
            else {
                $map = array(
                    $this->package.'/'.$folders['code']  => JPATH_ROOT.'/'.$folders['code'],
                    $this->package.'/'.$folders['media'] => JPATH_ROOT.'/'.$folders['media'],
                );
            }

            foreach ($map as $from => $to)
            {
                $temp   = $to.'_tmp';

                if (!JFolder::exists($from)) {
                    continue;
                }

                if (JFolder::exists($temp)) {
                    JFolder::delete($temp);
                }

                JFolder::copy($from, $temp);

                if (JFolder::exists($to)) {
                    JFolder::delete($to);
                }

                JFolder::move($temp, $to);
            }
        }

        $sql = JPATH_ROOT.'/'.$folders['code'].'/resources/install/install.sql';
        if (JFile::exists($sql))
        {
            $file = JFile::read($sql);

            $queries = JInstallerHelper::splitSql($file);

            $db = JFactory::getDbo();

            foreach ($queries as $query)
            {
                $query = trim($query);

                if ($query != '' && $query{0} != '#')
                {
                    $db->setQuery($query);
                    if (!$db->execute())
                    {
                        $this->setStatus(KDatabase::STATUS_FAILED);
                        $this->setStatusMessage('Unable to run the database queries during Koowa component install');

                        return false;
                    }
                }
            }
        }

        return true;
    }

    protected function _uninstallKoowaComponent()
    {
        $folders = $this->_getKoowaComponentDirectories();
        $sql = JPATH_ROOT.'/'.$folders['code'].'/resources/install/uninstall.sql';

        if (JFile::exists($sql))
        {
            $file = JFile::read($sql);

            $queries = JInstallerHelper::splitSql($file);
            $db = JFactory::getDbo();
            foreach ($queries as $query)
            {
                $query = trim($query);

                if ($query != '' && $query{0} != '#')
                {
                    $db->setQuery($query);

                    if (!$db->execute())
                    {
                        $this->setStatus(KDatabase::STATUS_FAILED);
                        $this->setStatusMessage('Unable to run the database queries during Koowa component uninstall');

                        return false;
                    }
                }
            }
        }

        foreach ($folders as $folder)
        {
            if (JFolder::exists(JPATH_ROOT.'/'.$folder)) {
                JFolder::delete(JPATH_ROOT.'/'.$folder);
            }
        }

        return true;
    }

    protected function _getKoowaComponentDirectories()
    {
        return array(
            'code'  => 'libraries/koowa/components/com_'.$this->name,
            'media' => 'media/koowa/com_'.$this->name
        );
    }
}