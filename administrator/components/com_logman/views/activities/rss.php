<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComLogmanViewActivitiesRss extends KViewTemplate
{
    protected function _initialize(KConfig $config)
    {
        $config->append(array(
            'layout'   => 'rss',
            'template' => 'rss',
            'mimetype' => 'application/rss+xml',
            'data'     => array(
                'update_period'    => 'hourly',
                'update_frequency' => 1
            )
        ));
    
        parent::_initialize($config);
    }

    public function display()
    {
        $model = $this->getModel();
        
        $this->assign('state', $model->getState())
            ->assign('activities', $model->getList())
            ->assign('total',	$model->getTotal())
            
            ->assign('sitename', JFactory::getConfig()->getValue('config.sitename'))
            ->assign('base_url', JURI::base())
            ->assign('language', JFactory::getLanguage()->getTag());
        
        return parent::display();
    }
    
    public function setLayout($layout)
    {
        //Don't allow to change the layout
        return parent::setLayout($this->_layout);
    }
}