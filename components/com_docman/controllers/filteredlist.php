<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanControllerFilteredlist extends ComDefaultControllerResource
{
    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'model' => 'com://site/docman.model.documents',
            'behaviors' => array('com://site/docman.controller.behavior.list.persistable')
        ));

        parent::_initialize($config);
    }
}
