<?php
/**
 * @package     EXTman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComExtmanModelExtensions extends KModelDatabase
{
	public function __construct(KObjectConfig $config)
	{
		parent::__construct($config);

		$this->getState()
            ->insert('top_level', 'boolean', false, false, array(), true)
            ->insert('package', 'cmd', false, false, array(), true) // Used for class instantiation
            ->insert('parent_id', 'int')
            ->insert('event', 'cmd');
	}

    protected function _actionCreate(KModelContext $context)
    {
        if ($this->getState()->package)
        {
            $context->entity->append(array(
                'entity_package' => $this->getState()->package
            ));
        }

        return parent::_actionCreate($context);
    }

	protected function _buildQueryWhere(KDatabaseQueryInterface $query)
	{
		parent::_buildQueryWhere($query);

        $state = $this->getState();

		if ($state->top_level) {
			$query->where('tbl.parent_id = 0');
		}

		if ($state->parent_id) {
			$query->where('tbl.parent_id = :parent_id')->bind(array('parent_id' => $state->parent_id));
		}
	}
}