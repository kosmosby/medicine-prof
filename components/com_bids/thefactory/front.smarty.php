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
 * @subpackage: smarty 
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');


class JTheFactorySmarty extends Smarty
{
    function __construct()
    {
        $Itemid=JRequest::getInt('Itemid');
        
        $my = JFactory::getUser();

//        if (JDEBUG) $this->debugging= true;

		$this->assign('ROOT_HOST',JURI::root());
		$this->assign('Itemid',$Itemid);
		$this->assign('option',JRequest::getWord('option'));
		$this->assign('task',JRequest::getCmd( 'task'));
		$this->assign('controller',JRequest::getCmd( 'controller'));
		$this->assign('is_logged_in',($my->id)?"1":"0");
		$this->assign('joomlauser',$my);
        if (class_exists('JTheFactoryHelper')){
            $cfg = JTheFactoryHelper::getConfig();
            $this->assign('cfg',$cfg);
        }

        
		$this->register_modifier('translate',array($this,'smarty_translate'));
		$this->register_modifier('t',array($this,'smarty_translate'));
        $this->register_function('jtext', array($this,'smarty_function_jtext'));
        
		$this->register_function('infobullet',array($this, 'smarty_infobullet'));
        $this->register_function('positions',array($this,'smarty_positions'));

        $this->register_function('init_behavior',array($this,'smarty_init_behavior'));
        $this->register_function('import_js_file',array($this,'smarty_import_js_file'));
        $this->register_function('import_css_file',array($this,'smarty_import_css_file'));
        $this->register_block('import_js_block',array($this,'smarty_import_js_block'));
        $this->register_block('import_css_block',array($this,'smarty_import_css_block'));

		$this->register_function('startpane',array($this,'smarty_startpane'));
		$this->register_function('starttab',array($this,'smarty_starttab'));
		$this->register_function('endpane',array($this,'smarty_endpane'));
		$this->register_function('endtab',array($this,'smarty_endtab'));


		$this->template_dir= JPATH_COMPONENT_SITE.DS.'templates'.DS.'default'.DS;
		$this->compile_dir=AUCTION_TEMPLATE_CACHE;
         
        parent::__construct();
    }
	function display( $tpl_name, $cache_id = null, $compile_id = null){
		$task = JRequest::getCmd('task');
		JTheFactoryEventsHelper::triggerEvent('onBeforeDisplay',array($task,$this));
		if(!file_exists($this->template_dir.$tpl_name)){
            $tpl_name=JPATH_COMPONENT_SITE.DS.'templates'.DS.'default'.DS.$tpl_name;
        }
        if ($title=$this->get_template_vars('page_title'))
        {
            $doc = JFactory::getDocument();
            $doc->setTitle($title);
        }
        if (!$this->get_template_vars('template_file')){
            $template_file=$tpl_name;
            if (substr($template_file,0,2)=='t_') $template_file=substr($template_file,2);
            $template_file=preg_replace('#\.[^.]*$#', '', $template_file);
            self::assign('template_file',$template_file);
        }
		parent::display($tpl_name, $cache_id = null, $compile_id = null);
        JTheFactoryEventsHelper::triggerEvent('onAfterDisplay');
	}
	function _smarty_include($params){
		$tpl_inc = $params['smarty_include_tpl_file'];
		if(!file_exists($this->template_dir.$tpl_inc)){
            $tpl_name=JPATH_COMPONENT_SITE.DS.'templates'.DS.'default'.DS.$tpl_inc;
    		$params['smarty_include_tpl_file'] = $tpl_name;
        }
		parent::_smarty_include($params);
	}
    function smarty_infobullet($params, &$smarty) {
        $res="";
        if(!empty($params['text'])) {
        	JHTML::_('behavior.tooltip'); //load the tooltip behavior
            $res=JHTML::_('tooltip', JText::_($params['text']));
        }
        return $res;
    }
    function smarty_translate(){

        $args = func_get_args();
        if(count($args)==1) {
            return JText::_($args[0]);
        }

        return call_user_func_array( array('JText','sprintf'), $args );
    }
    function smarty_positions($params, &$smarty) {
        if(!isset($params['position']) || empty($params['position']))
            return null;
        if(!isset($params['item']) || empty($params['item']))
            return null;
        $item=$params['item'];
        $page=(isset($params['page']))?$params['page']:"";
        $catfield=null;
        if (is_callable(array($item,'getCategoryField')))
            $catfield=$item->getCategoryField(); //make sure proper object is passed
        if (is_object($item)) $item=get_object_vars($item);

        $position=$params['position'];
        $template_file=$smarty->get_template_vars('template_file');
        JTheFactoryHelper::modelIncludePath('positions');
        $model= JModelLegacy::getInstance('Positions','JTheFactoryModel');
        $fields=$model->getFieldsForPosition($template_file,$position);       
        $result="";

        $fieldObj= JTable::getInstance('FieldsTable','JTheFactory');
        JTheFactoryHelper::modelIncludePath('fields');
        $fieldsmodel= JModelLegacy::getInstance('Fields','JTheFactoryModel');
        
        foreach($fields as $field)
            if (isset($item[$field->db_name])&&$page==$field->page)
            {
                $fieldObj->bind($field);
                if ($catfield && $fieldObj->categoryfilter && !$fieldsmodel->hasAssignedCat($fieldObj,$item->$catfield))
                    continue;
                    
                $ftype= CustomFieldsFactory::getFieldType($field->ftype);
                $field_label=$field->name;

                $field_html=$ftype->getTemplateHTML($fieldObj,$item[$field->db_name]);
                
                $result.="<div id='{$field->db_name}'><label class='custom_field'>{$field_label}</label>:&nbsp;{$field_html}</div>";
            }
        return $result;
    }

    function smarty_init_behavior($params, &$smarty) {
        if (!empty($params['type'])) {
            $type = $params['type'];
        }else
            $type='modal';
        JHTML::_('behavior.' . $type);
    }
    function smarty_import_js_file($params, &$smarty) {
        if (empty($params['url']))
            return;
        $url=$params['url'];
        if (stripos($url,'http://')===FALSE && stripos($url,'https://')===FALSE )
            $url=JURI::root().'components/'.APP_EXTENSION.'/js/'.$url;
        $doc = JFactory::getDocument();
        $doc->addScript($url);
        if ($doc->getType() == 'raw')
            return "<script type=\"text/javascript\" src=\"" .$url. "\"></script>";
    }

    function smarty_import_css_file($params, &$smarty) {
        if (empty($params['url']))
            return;
        $url=$params['url'];
        if (stripos('http://',$url)===FALSE && stripos('https://',$url)===FALSE )
            $url=JURI::root().'components/'.APP_EXTENSION.'/css/'.$url;
        $doc = JFactory::getDocument();
        $doc->addStyleSheet($url);
        if ($doc->getType() == 'raw')
            return "<link href=\"" . $url . "\" rel=\"stylesheet\" type=\"text/css\">";
    }

    function smarty_import_js_block($params, $content, &$smarty, &$repeat) {
        if ($repeat)
            return; //ignore opening tag
        $doc = JFactory::getDocument();
        $doc->addScriptDeclaration($content);
        if ($doc->getType() == 'raw')
            return "<script type=\"text/javascript\">$content</script>";
    }

    function smarty_import_css_block($params, $content, &$smarty, &$repeat) {
        if ($repeat)
            return; //ignore opening tag
        $doc = JFactory::getDocument();
        $doc->addStyleDeclaration($content);
        if ($doc->getType() == 'raw')
            return "<style type=\"text/css\">$content</style>";
    }
    function smarty_startpane($params) {

        JHtml::_('behavior.framework');
        return JHtml::_('tabs.start',$params['id']);
    }
    function smarty_endpane() {
        return JHtml::_('tabs.end');
    }
    function smarty_starttab($params) {

        return JHtml::_('tabs.panel', JText::_($params['text']), $params['paneid']);
    }
    function smarty_endtab() {
    	return;
    }
    function smarty_function_jtext($params, &$smarty){

        $text = isset($params['text']) ? strtoupper($params['text']) : null;
        if(!$text) {
            return;
        }

        $params['text'] = JText::_($text);

        if(count($params)>1) {
            return call_user_func_array('sprintf', $params);
        }

        return $params['text'];
    }

}




?>
