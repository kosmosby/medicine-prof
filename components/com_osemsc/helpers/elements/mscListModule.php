<?php
/**
 * @version		$Id: category.php 315 2010-01-14 18:49:25Z joomlaworks $
 * @package		K2
 * @author    JoomlaWorks http://www.joomlaworks.gr
 * @copyright	Copyright (c) 2006 - 2010 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_osemsc".DS."define.php");
if (JOOMLA16==true)
{
	jimport('joomla.form.formfield');
	class JFormFieldMembership extends JFormField
	{
	   protected $type = 'Membership';

	   protected function getInput()
	   {
	   	    $name = 'msc_ids';
	   	    $control_name = "jform[params][msc_ids][]";

			require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ose_cpu'.DS.'define.php');
			require_once( OSECPU_B_PATH.DS.'oseregistry'.DS.'oseregistry.php');

			oseRegistry::register('registry','oseregistry');
			oseRegistry::call('registry');
			//oseExit($value);
			oseRegistry::register('msc','membership');
			oseRegistry::call('msc','membership');
			$objs = oseMscTree::getSubTreeDepth(0,0,'obj');

			$option = array();
			$return = '<select class="inputbox" style="width:90%;" multiple="multiple" size="15" name = "'.$control_name.'">';
			if (!is_array($this->value))
			{
				$this->value = array($this->value);
			}
			foreach ( $objs as $obj )
			{
				if($obj->published)
				{

					if (in_array($obj->id, $this->value))
					{
						$selected = "selected";
					}
					else
					{
						$selected = "";
					}
					$return .= "<option value ='$obj->id' $selected>".$obj->title ."</option>";
				}
			}
			$return .="</select>";
			return $return;
		}
	}
}
else
{
	jimport( 'joomla.html.parameter.element' );
	class JElementMscListModule extends JElement
	{
		var	$_name = 'mscListModule';
		function fetchElement($name, $value, &$node, $control_name)
		{
			$db = JFactory::getDBO();
			$query = " SELECT * FROM `#__modules`"
					." WHERE `module` = 'mod_osecustommsclist'"
					;
			$db->setQuery($query);
			
			$list = $db->loadObjectList();
			
			$option = array();
			foreach ( $list as $obj )
			{
				if($obj->published)
				{
					@$option[] = JHTML::_('select.option',  $obj->id, $obj->title );
				}
			}
			$return =  JHTML::_('select.genericlist',  $option, ''.$control_name.'['.$name.'][]', ' class="inputbox" style="width:90%;" size="1"', 'value', 'text', $value );
			return $return;
		}
	}
}