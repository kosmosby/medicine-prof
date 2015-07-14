<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

/**
 * Behavior that saves parameters in the params column.
 *
 */
class ComDocmanDatabaseBehaviorConfigurable extends KDatabaseBehaviorAbstract
{
    public function getMixableMethods(KObject $mixer = null)
    {
        $methods = array();

        if (isset($mixer->params)) {
            $methods = parent::getMixableMethods($mixer);
        }

        return $methods;
    }

    protected function _afterTableSelect(KCommandContext $context)
    {
        $rowset = $context->data;

        if (is_object($rowset)) {
            if ($rowset instanceof KDatabaseRowAbstract) {
                $rowset = array($rowset);
            }

            foreach ($rowset as $row) {
                if (is_object($row)) {
                    $params = (array) (is_object($row->params) ? $row->params : json_decode($row->params));
                    $row->setData(array(
                        'params' => new KConfig($params)
                    ), false);
                }
            }
        }
    }

    protected function _beforeTableUpdate(KCommandContext $context)
    {
        $this->_convert($context);
    }

    protected function _beforeTableInsert(KCommandContext $context)
    {
        $this->_convert($context);
    }

    protected function _convert(KCommandContext $context)
    {
        $row = $context->data;
        if ($row instanceof KDatabaseRowAbstract) {
            if (!is_object($row->params) && !is_array($row->params)) {
                $row->params = json_decode($row->params, true);
            }

            $params = new KConfig($row->params);
            $row->setData(array(
                'params' => $params->toJson()
            ), false);
        }
    }
}
