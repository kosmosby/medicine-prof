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

class CustomFieldsFactory
{
    static function &getFieldType($typename)
    {
        static $instances;
        
        if (!isset($instances[$typename]))
        {
            $field_class="FieldType_".$typename;
            if (!class_exists($field_class))
                require_once(dirname(__FILE__).DS.'..'.DS.'plugins'.DS.'type'.DS.strtolower($typename).'.php' );
            $instances[$typename]=new $field_class;
        }
        return $instances[$typename];
    }
    
    static function &getFieldValidator($validatorname)
    {
        static $instances;
        
        if (!isset($instances[$validatorname]))
        {
            $field_class="FieldValidator_".$validatorname;
            if (!class_exists($field_class))
                require_once(dirname(__FILE__).DS.'..'.DS.'plugins'.DS.'validators'.DS.strtolower($validatorname).'.php' );
            $instances[$validatorname]=new $field_class;
        }
        return $instances[$validatorname];
        
    }
    static function &getConfig()
    {
        static $instance;
        
        if (!isset($instance)){
            $instance=array();
			$MyApp= JTheFactoryApplication::getInstance();
            
            $instance['table_prefix']=$MyApp->getIniValue('table_prefix', 'custom-fields');
            
            $instance['pages']=array();
            $instance['tables']=array();
            $instance['pk']=array();
            $instance['aliases']=array();
            
			$pages 		= explode(",", $MyApp->getIniValue('pages', 'custom-fields'));
			$page_names = explode(",", $MyApp->getIniValue('page_names', 'custom-fields'));
			$tables 	= explode(",", $MyApp->getIniValue('tables', 'custom-fields'));
			$pk 		= explode(",", $MyApp->getIniValue('pk', 'custom-fields'));
			$aliases 	= explode(",", $MyApp->getIniValue('aliases', 'custom-fields'));
            $pages_with_category= explode(",", $MyApp->getIniValue('pages_with_category', 'custom-fields'));
            
            $i=0;
            foreach($pages as $page)
            {
				$instance['pages'][$page] 	= $page_names[$i];
				$instance['tables'][$page] = $tables[$i];
				$instance['pk'][$page]  = $pk[$i];
				$instance['aliases'][$tables[$i]] = $aliases[$i];
				$instance['has_category'][$page] = in_array($page,$pages_with_category);
                $i++;
			}
        }
        return $instance;
    }
    static function &getFieldObject($db_name)
    {
        static $instances;
        
        if (!isset($instances[$db_name]))
        {
            $cfg= self::getConfig();
            
            $db =  JFactory::getDBO();
            $q = "SELECT * FROM #__".$cfg['table_prefix']."_fields WHERE db_name = '{$db_name}';";
            $db->setQuery($q);
            $instances[$db_name]= $db->loadObject(); 
        }
        
        return $instances[$db_name];
    }
    static function _filterField($field_list,$page)
    {
        $page_fields=array();
        foreach($field_list as $field)
            if ($field->page==$page)
                $page_fields[]=$field;
        return $page_fields;
    }
    static function &getFieldsList($page=null)
    {
        static $instance;
        if (!isset($instance)){
            $cfg= self::getConfig();
            
            $db =  JFactory::getDBO();
            $db->setQuery("SELECT * FROM #__".$cfg['table_prefix']."_fields WHERE `status`= 1 order by `ordering`");
            $instance=$db->loadObjectList();
        }        
        if (!$page || !count($instance))
            return $instance;
        else
            {
                $res=self::_filterField($instance,$page);
                return $res;
            }
    }
    static function &getSearchableFieldsList($page=null)
    {
        static $instance;
        if (!isset($instance)){
            $cfg= self::getConfig();
            
            $db =  JFactory::getDBO();
            $db->setQuery("SELECT * FROM #__".$cfg['table_prefix']."_fields WHERE `search` = 1 and `status`= 1 order by `ordering`");
            $instance=$db->loadObjectList();
        }
        if (!$page || !count($instance))
            return $instance;
        else{
            $res=self::_filterField($instance,$page);
            return $res;
        }

    }
    static function &getValidatorsList()
    {
        static $validators;
        if (!isset($validators)){
    		jimport('joomla.filesystem.folder');
            $validators=JFolder::files(dirname(__FILE__).DS.'..'.DS.'plugins'.DS.'validators','\.php$');
            $validators=preg_replace('/\.php$/','',$validators);
        }        
        return $validators;
        
    }
    static function &getFieldTypesList()
    {
        static $ftypes;
        if (!isset($ftypes)){
    		jimport('joomla.filesystem.folder');
            $ftypes=JFolder::files(dirname(__FILE__).DS.'..'.DS.'plugins'.DS.'type','\.php$');
            $ftypes=preg_replace('/\.php$/','',$ftypes);
        }        
        return $ftypes;
        
    }
    static function getPagesList()
    {
		$MyApp= JTheFactoryApplication::getInstance();
        $pages 		= explode(",", $MyApp->getIniValue('pages', 'custom-fields'));
        return $pages;
    }
    static function addValidatorJS($validatorname)
    {
        static $instances;
        if (!$validatorname) 
            return;
        if (isset($instances[$validatorname]))
            return;
        $validator= self::getFieldValidator($validatorname);
        $js=$validator->validateJS();
        if ($js){
            $doc= JFactory::getDocument();
            $doc->addScriptDeclaration($js);
        }
    }
}

?>
