<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanDatabaseRowRemote extends KDatabaseRowAbstract
{
    public function __get($column)
    {
        if ($column == 'extension') {
            return pathinfo($this->path, PATHINFO_EXTENSION);
        }

        if ($column == 'filename') {
            return pathinfo($this->path, PATHINFO_BASENAME);
        }

        if ($column == 'fullpath') {
            return $this->path;
        }

        if ($column == 'scheme') {
            return parse_url($this->path, PHP_URL_SCHEME);
        }

        return parent::__get($column);
    }
}
