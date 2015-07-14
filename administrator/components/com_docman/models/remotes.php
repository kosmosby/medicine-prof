<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanModelRemotes extends KModelAbstract
{
    public function __construct(KConfig $config)
    {
        parent::__construct($config);

        $this->_state
            ->insert('container', 'identifier', '')
            ->insert('path', 'url', null, true) // unique
            ;
    }

    public function getItem()
    {
        if (!isset($this->_item)) {
            $this->_item	= $this->getService('com://admin/docman.database.row.remote', array(
                'data' => array(
                    'path' => $this->_state->path
                )));
        }

        return parent::getItem();
    }
}
