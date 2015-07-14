<?php

defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');

class JFormFieldGalleryList extends JFormField
{
	protected $type = 'GalleryList';

	protected function getInput()
	{
        $galleries_plugins[] = JHTML::_('select.option', 'scrollgallery', 'Scroll Gallery');
        $galleries_plugins[] = JHTML::_('select.option', 'lytebox', 'Lytebox');
        $galleries_plugins[] = JHTML::_('select.option', 'slider', 'Picture Slider');
        return JHTML::_("select.genericlist", $galleries_plugins,$this->name,"" ,'value', 'text',$this->value);
	}
}
