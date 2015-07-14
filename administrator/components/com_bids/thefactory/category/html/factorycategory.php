<?php
/**------------------------------------------------------------------------
thefactory - The Factory Class Library - v 2.0.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
 * @build: 01/04/2012
 * @package: thefactory
 * @subpackage: category
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

abstract class JHtmlFactoryCategory
{ 
    function select($inputname,$html_attribs='',$selectedcat=null,$include_disabled=false,$parents_disabled=false,$include_empty_opt=false,$empty_opt_label=null)
    {
        JModel::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'thefactory'.DS.'category'.DS.'models');
        $catModel= JModel::getInstance('Category','JTheFactoryModel');

        $cat_tree=$catModel->getCategoryTree(1,$include_disabled);
        if (!count($cat_tree))
            return "";
        $options=array();
        if ($include_empty_opt)
            $options[] = JHTML::_('select.option', '',
                         $empty_opt_label?$empty_opt_label:JText::_('FACTORY_ANY_CATEGORY'),
                         'value', 'text');

 		foreach($cat_tree as $category)
        {
//TODO: nr_children nu e intors de model             
            $spacer=str_pad('',($category->depth-1)*3,'-');
            $disabled=$parents_disabled;
			$options[] = JHTML::_('select.option', $category->id, 
                $spacer.stripslashes($category->title),
                'value', 'text',$disabled);
		}
       
		$html_tree = JHTML::_('select.genericlist', $options, $inputname,$html_attribs,'value', 'text',$selectedcat);
		return $html_tree;

    }
}
?>
