<?php
/**
 * @package     EXTman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComExtmanDatabaseTableExtensions extends KDatabaseTableAbstract
{
	protected function _initialize(KObjectConfig $config)
	{
		$config->append(array(
			'behaviors'  => 'creatable',
			'filters'    => array(
				'manifest'   => 'com://admin/extman.filter.manifest',
				'identifier' => 'com://admin/extman.filter.identifier'
			)
		));

		parent::_initialize($config);
	}
}