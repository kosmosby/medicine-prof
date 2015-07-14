<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanModelFiles extends ComFilesModelFiles
{
    public function __construct(KConfig $config)
    {
        parent::__construct($config);

        $this->_state->insert('paths', 'com://admin/files.filter.path');
    }
}
