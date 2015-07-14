<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanModelConfigs extends KModelAbstract
{
    public function getData()
    {
        return $this->getItem();
    }

    public function getItem()
    {
        if (!$this->_item) {
            $this->_item = $this->getService('com://admin/docman.database.row.config');
        }

        return parent::getItem();
    }
}
