<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanControllerDownload extends ComDocmanControllerDocument
{
    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'model' => 'documents',
            'view'  => 'download'
        ));

        parent::_initialize($config);
    }
}
