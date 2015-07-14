<?php
/**
 * @package     LOGman
 * @copyright   Copyright (C) 2011 - 2013 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */

class ComLogmanViewActivitiesHtml extends ComActivitiesViewActivitiesHtml
{
    public function display()
    {
        $this->user = JFactory::getUser();

        if ($this->getLayout() == 'default')
        {
            $model = $this->getService($this->getModel()->getIdentifier());
    
            $this->assign('packages', $model
                ->distinct(true)
                ->column('package')
                ->getList()
            );

            // Determine if own activities should be greyed out or not.
            if ($this->getModel()->getState()->user == $this->user->id) {
                // Filtering by current logged user => we do not grey out.
                $this->grey_self = false;
            } else {
                // We do grey out.
                $this->grey_self = true;
            }

            /*
             * You would think that Joomla menu already loads the necessary language files.
             * Well it does but after the component has been rendered so we need to do this ourselves
             */
            foreach ($this->packages as $package) {
                $lang = JFactory::getLanguage();
                $component = 'com_'.$package->package;
                $lang->load($component.'.sys', JPATH_BASE, null, false, false)
                ||	$lang->load($component.'.sys', JPATH_ADMINISTRATOR.'/components/'.$component, null, false, false)
                ||	$lang->load($component.'.sys', JPATH_BASE, $lang->getDefault(), false, false)
                ||	$lang->load($component.'.sys', JPATH_ADMINISTRATOR.'/components/'.$component, $lang->getDefault(), false, false);
            }
        } elseif ($this->getLayout() == 'list') {
            $this->view_all = version_compare(JVERSION, '1.6', '<') ? true : $this->user->authorise('core.manage',
                'com_logman');
        } elseif ($this->getLayout() == 'export') {
            $state = $this->getModel()->getState();
            // Cleanup pagination state.
            unset($state->limit);
            unset($state->offset);
            $this->export_url = JRoute::_('index.php?option=com_logman&format=csv&tmpl=component&view=activities&'.http_build_query($state->getData()), false);
        }
    
        return parent::display();
    }
}