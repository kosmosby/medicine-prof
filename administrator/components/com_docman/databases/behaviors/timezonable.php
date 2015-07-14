<?php
/**
 * @package     DOCman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

/**
 * Behavior that converts dates to UTC before saving
 *
 */
class ComDocmanDatabaseBehaviorTimezonable extends KDatabaseBehaviorAbstract
{
    protected $_fields = array();

    public function __construct(KConfig $config)
    {
        parent::__construct($config);

        if ($config->fields) {
            $this->setFields(KConfig::unbox($config->fields));
        }
    }

    public function setFields(array $fields)
    {
        $this->_fields = $fields;

        return $this;
    }

    public function getFields()
    {
        return $this->_fields;
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
        if (empty($this->_fields) || !array_intersect($this->_fields, $this->getModified())) {
            return;
        }

        $row = $context->data;
        foreach ($row->getData(true) as $field => $value) {
            if (in_array($field, $this->_fields)) {
                $row->$field = $this->_convertToUTC($value);
            }
        }
    }

    protected function _convertToUTC($value)
    {
        $return = '';

        if (intval($value) > 0) {
            // Get the user timezone setting defaulting to the server timezone setting.
            $offset = JFactory::getUser()->getParam('timezone', JFactory::getConfig()->get('offset'));

            // Return a MySQL formatted datetime string in UTC.
            $return = JFactory::getDate($value, $offset)->toSql();
        }

        return $return;
    }
}
