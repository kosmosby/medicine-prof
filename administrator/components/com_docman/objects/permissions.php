<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanObjectPermissions extends KObjectArray
{
    public function __call($method, $arguments)
    {
        if (substr($method, 0, 3) === 'can') {
            $parts = KInflector::explode($method);
            array_shift($parts);
            array_unshift($parts, 'core');
            $permission = implode('.', $parts);

            return $this[$permission];
        }

        return parent::__call($method, $arguments);
    }
}
