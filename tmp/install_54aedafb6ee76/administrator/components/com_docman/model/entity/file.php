<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanModelEntityFile extends ComFilesModelEntityFile
{
    /**
     * Returns all documents pointing to this file
     *
     * @param bool $count If true, it will only return a file count
     */
    public function getLinkedDocuments($count = false)
    {
        $table = $this->getObject('com://admin/docman.database.table.documents');
        $query = array('storage_type' => 'file', 'storage_path' => $this->path);

        return $count ? $table->count($query) : $table->select($query);
    }

    public function getProperty($column)
    {
        if ($column == 'scheme') {
            return 'file';
        }

        return parent::getProperty($column);
    }
}
