<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

/**
 * Used by the node controller to change document paths after moving files
 */
class ComDocmanControllerBehaviorMovable extends KControllerBehaviorAbstract
{
    protected $_path_cache = array();

    protected function _beforeMove(KControllerContextInterface $context)
    {
        $entities = $this->getModel()->fetch();

        foreach ($entities as $entity)
        {
            $entity->setProperties($context->request->data->toArray());

            $from = $entity->path;
            $to   = $entity->destination_path;

            if (is_dir($entity->fullpath))
            {
                $from .= '/';
                $to   .= '/';
            }

            $this->_path_cache[$from] = $to;
        }
    }

    /**
     * Updates attached documents of the moved files
     *
     * Uses a database update query directly since moving folders might mean updating hundreds of rows.
     *
     * @param KControllerContextInterface $context
     */
    protected function _afterMove(KControllerContextInterface $context)
    {
        $table = $this->getObject('com://admin/docman.model.documents')->getTable();

        $base_query = $this->getObject('lib:database.query.update')
            ->table($table->getName())
            ->where("storage_type = :type")->bind(array('type' => 'file'));

        foreach ($this->_path_cache as $from => $to)
        {
            $query = clone $base_query;
            $query->bind(array('from' => $from, 'to' => $to));

            if (substr($from, -1) === '/') // Move folder
            {
                $query->values('storage_path = REPLACE(storage_path, :from, :to)')
                      ->where('storage_path LIKE :filter')->bind(array('filter' => $from.'%'));
            }
            else // Move file
            {
                $query->values('storage_path = :to')
                      ->where('storage_path = :from');
            }

            $table->getAdapter()->update($query);
        }
    }
}