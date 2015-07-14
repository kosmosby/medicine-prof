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
 * @subpackage: custom_fields
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class JTheFactoryFieldsHelper
{
    function createHTMLObjectsForField($field)
    {
		$lists = new JObject();
        $cfg= CustomFieldsFactory::getConfig();
        $lists->curent_type=null;
        $lists->field_options=array();

        $lists->field_types= CustomFieldsFactory::getFieldTypesList();

		$opts = array();
		foreach ( $lists->field_types as $typename )
        {   
            $item= CustomFieldsFactory::getFieldType($typename);
 		    $opts[] =JHTML::_('select.option', $item->class_name, $item->type_name);

		}
        $lists->field_types_html=JHTML::_( 'select.genericlist',
                    $opts, 'ftype', 'class="inputbox"', 
                    "value","text",$field->ftype); 

        $lists->field_pages_categories=$cfg['has_category'];
        $validators= CustomFieldsFactory::getValidatorsList();
    	$opts = array(JHTML::_('select.option', "", JText::_("FACTORY_NONE")));
        foreach($validators as $validator)
        {
                $validatorObj= CustomFieldsFactory::getFieldValidator($validator);
                $opts[]=JHTML::_('select.option', $validatorObj->classname , 
                    JText::_($validatorObj->name));
        }
   		$lists->validate_type = 
                JHTML::_( 'select.genericlist',
                    $opts, 'validate_type', 'class="inputbox"',
                    "value","text",$field->validate_type); 
        
        if($field->ftype)
        {
    		$lists->curent_type = CustomFieldsFactory::getFieldType($field->ftype);
    		if($lists->curent_type->has_options) 
    			$lists->field_options= $field->getOptions();
    		$lists->curent_type_params = $lists->curent_type->get('_params');
            
        }        
        if ($field->id)
        {
            //cannot change page for an existing field
            $lists->field_pages=$cfg['pages'][$field->page];
            
        }else{
            
            $opts=array();
		    foreach($cfg['pages'] as $k => $v)
  				  $opts[]=JHTML::_('select.option', $k, $v);
			
			$lists->field_pages = JHTML::_( 'select.genericlist',
                        $opts,'page','class="inputbox"',
                        "value","text",$field->page);
 
 
        }
		
		$lists->compulsory = JHTML::_("select.booleanlist",'compulsory'," infoyes='".JText::_('FACTORY_COMPULSORY_FIELD')."' infono='".JText::_('FACTORY_OPTIONAL_FIELD')."'" ,$field->compulsory);
		$lists->search 	 = JHTML::_("select.booleanlist",'search','',$field->search);
		$lists->status 	 = JHTML::_("select.booleanlist",'status','',$field->status);
        return $lists;
    }
    function addJSLanguageStrings()
    {
        /* @var JDocument $doc */
        $doc= JFactory::getDocument();
        $doc->addScriptDeclaration(
        "
            var js_lang_fields=Array();
            js_lang_fields['field_must_be_saved']='".JText::_('FACTORY_YOU_MUST_SAVE_THE_FIELD_FIRST')."';
        "
        );
    }
}
?>
