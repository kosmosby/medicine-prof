<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.file');

class ComDocmanFilterPath extends KFilterPath
{
    /**
     * Also validate using JFilter::makeSafe
     *
     * @param	scalar	Value to be validated
     * @return bool True when the variable is valid
     */
    protected function _validate($value)
    {
        $result = parent::_validate($value);

        $result = $result && JFile::makeSafe($value) === $value;

        return $result;
    }

    /**
     * Sanitize a value
     *
     * @param	mixed	Value to be sanitized
     * @return string
     */
    protected function _sanitize($value)
    {
        $value = parent::_sanitize($value);

        return JFile::makeSafe($value);
    }
}
