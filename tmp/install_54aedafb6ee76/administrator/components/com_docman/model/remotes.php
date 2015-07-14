<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanModelRemotes extends KModelAbstract
{
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        $this->getState()
            ->insert('container', 'identifier', '')
            ->insert('path', 'url', null, true) // unique
            ;
    }

    protected function _actionFetch(KModelContext $context)
    {
        return $this->getObject('com://admin/docman.model.entity.remote', array(
            'data' => array(
                'path' => $this->getState()->path
            )
        ));
    }
}
