<?php
/**
 * @package    DOCman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComDocmanControllerFilteredlist extends ComKoowaControllerModel
{
    public function __construct(KObjectConfig $config)
    {
        parent::__construct($config);

        if ($this->isDispatched())
        {
            $this->addBehavior('com://site/docman.controller.behavior.filterable', array(
                'vars' => array(
                    'category', 'created_by', 'sort'
                )
            ));
        }
    }

    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'formats' => array('json', 'rss'),
            'model'   => 'com://site/docman.model.documents'
        ));

        parent::_initialize($config);
    }

    protected function _actionRender(KControllerContextInterface $context)
    {
        $result = false;

        if($this->execute('browse', $context) !== false)
        {
            //Do not call parent. Parent will re-execute.
            $result = KControllerView::_actionRender($context);
        }

        return $result;
    }
}
