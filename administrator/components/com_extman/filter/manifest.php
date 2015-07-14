<?php
/**
 * @package     EXTman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComExtmanFilterManifest extends KFilterAbstract
{
    public function validate($value)
    {
        return true;
    }

	public function sanitize($value)
	{
		return $value instanceof SimpleXMLElement ? $value->asXML() : (string)$value;
	}
}