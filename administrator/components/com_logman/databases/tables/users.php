<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComLogmanDatabaseTableUsers extends KDatabaseTableDefault
{
	public function __construct(KConfig $config)
	{
	    parent::__construct($config);

	    $this->getColumn('email')->unique    = true;
	    $this->getColumn('username')->unique = true;
	}

    protected function _initialize(KConfig $config)
	{
		$config->append(array(
			'name'				=> 'users',
			'base' 				=> 'users',
			'column_map'		=> array(
				'users_user_id'		=> 'id',
			)
		));

		parent::_initialize($config);
	}
}