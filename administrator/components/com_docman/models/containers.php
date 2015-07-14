<?php
/**
 * @version     $Id$
 * @package     Nooku_Components
 * @subpackage  Files
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net).
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.nooku.org
 */

/**
 * Containers Model Class
 *
 * @author      Ercan Ozkaya <http://nooku.assembla.com/profile/ercanozkaya>
 * @package     Nooku_Components
 * @subpackage  Files
 */
class ComDocmanModelContainers extends ComFilesModelContainers
{
    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'table' => 'com://admin/files.database.table.containers'
        ));

        parent::_initialize($config);
    }

    public function getItem()
    {
        $result = parent::getItem();

        if ($result && $result->slug === 'docman-files')
        {
            $page = JFactory::getApplication()->getMenu()->getActive();

            if ($page && $page->params->get('upload_folder'))
            {
                $folder = $page->params->get('upload_folder');
                $result->path = $result->path.'/'.trim($folder, '/');
            }
        }

        return $result;
    }
}
