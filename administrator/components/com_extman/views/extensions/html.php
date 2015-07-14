<?php
/**
 * @package     EXTman
 * @copyright   Copyright (C) 2011 - 2014 Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.joomlatools.com
 */
class ComExtmanViewExtensionsHtml extends ComKoowaViewHtml
{
    /**
     * Initializes the config for the object
     *
     * Called from {@link __construct()} as a first step of object instantiation.
     *
     * @param   KObjectConfig $config Configuration options
     * @return  void
     */
    protected function _initialize(KObjectConfig $config)
    {
        $config->append(array(
            'template_functions' => array(
                'translateComponentName' => array($this, 'translateComponentName'),
            ),
        ));

        parent::_initialize($config);
    }

    public function translateComponentName($component)
    {
        $language = JFactory::getLanguage();

        $language->load($component.'.sys', JPATH_BASE, null, false, false)
            ||	$language->load($component.'.sys', JPATH_ADMINISTRATOR.'/components/'.$component, null, false, false)
            ||	$language->load($component.'.sys', JPATH_BASE, $language->getDefault(), false, false)
            ||	$language->load($component.'.sys', JPATH_ADMINISTRATOR.'/components/'.$component, $language->getDefault(), false, false);

        return $language->hasKey($component) ? JText::_($component) : $component;
    }
}