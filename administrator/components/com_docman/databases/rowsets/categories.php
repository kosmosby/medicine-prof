<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanDatabaseRowsetCategories extends KDatabaseRowsetDefault
{
    public function getDocumentMap()
    {
        $documents = array();
        $ids = array();

        foreach ($this as $row) {
            $ids[] = $row->id;
            $ids += $row->getDescendants()->getColumn('id');
        }
        $ids = array_unique($ids);

        if ($ids) {
            $documents = $this->getService('com://admin/docman.model.documents')
                ->category($ids)
                ->getList();
        }

        $results = array();
        foreach ($documents as $document) {
            $results[$document->docman_category_id][] = $document;
        }

        return $results;
    }
}
