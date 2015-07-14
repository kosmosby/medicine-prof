<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanParameterDefault extends KObjectDecorator
{
    public function __get($key)
    {
        return $this->getObject()->get($key);
    }

    public function __set($key, $value)
    {
        $this->getObject()->set($key, $value);
    }

    public function set($property, $value = null)
    {
        $arguments = func_get_args();

        return $this->__call('set', $arguments);
    }

    public function get($property = null, $default = null)
    {
        $arguments = func_get_args();

        return $this->__call('get', $arguments);
    }

}
