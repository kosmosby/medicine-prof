<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanModelStorages extends KModelAbstract
{
    public function __construct(KConfig $config)
    {
        parent::__construct($config);

        $this->_state
            ->insert('container', 'identifier', '')
            ->insert('storage_type', 'identifier', 'file')
            ->insert('storage_path', 'filename', '')
            ;
    }

    public function getItem()
    {
        $identifier = $this->_state->storage_type;

        try {
            if (is_string($identifier) && strpos($identifier, '.') === false) {
                $identifier = 'com://admin/docman.model.'.KInflector::pluralize($identifier);
            }

            $model = $this->getService($identifier);

            if ($this->_state->storage_type == 'file') {
                $model->folder(dirname($this->_state->storage_path))
                      ->name(basename($this->_state->storage_path));
            } else {
                $model->path($this->_state->storage_path);
            }

            $row = $model->container($this->_state->container)
                ->getItem();

        } catch (KServiceIdentifierException $e) {
            throw new KModelException('Invalid identifier: '.$identifier);
        }

        return $row;
    }
}
