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

class JTheFactoryFieldsController extends JTheFactoryController
{
    var $name='Fields';
    var $_name='Fields';

    function Edit()
    {
		$id = JRequest::getInt("id");
        
        $cfg= CustomFieldsFactory::getConfig();
		$field = JTable::getInstance('FieldsTable','JTheFactory');

		$parameters_plugins = null;
		
		if($id){
            if (!$field->load($id))
            {
                JError::raiseNotice(101,JText::_("FACTORY_ERROR_LOADING_FIELD")." $id");
                $this->setRedirect("index.php?option=".APP_EXTENSION."&task=fields.listfields");
                return;
            }
        }
        else
            $field->setDefaults();

        $lists=JTheFactoryFieldsHelper::createHTMLObjectsForField($field);
        JHTML::_( 'behavior.mootools' );//load mootools before fields.js
        JHTML::script("administrator/components/".APP_EXTENSION."/thefactory/fields/js/fields.js");
        JHTML::stylesheet("administrator/components/".APP_EXTENSION."/thefactory/fields/css/fields.css");

        if ($field->id && $field->categoryfilter ){
            $model= JModel::getInstance('Fields','JTheFactoryModel');
            $assigned=$model->getAssignedCats($field);
        }else{ //select all by default
    	    $assigned="all";
        }
		$lists->category 	 = JHtml::_('factorycategory.select','parent[]','style="width:200px" multiple size=10'.(($field->categoryfilter)?'':' disabled'),$assigned,true);

        $view=$this->getView('edit');  
        $view->assignRef('lists',$lists);
        $view->assignRef('field',$field);
        $view->display();
    }
    function _SaveField()
    {
        /*
         * @var $field JTheFactoryFieldsTable;
         * @var $field_type FactoryFieldTypes;
         * @var $model JTheFactoryModelFields;
         * */
        $id=JRequest::getInt('id');
        $cat_filter = JRequest::getVar( 'parent', array(), '', 'array' );

        $field = JTable::getInstance('FieldsTable','JTheFactory');
        $model= JModel::getInstance('Fields','JTheFactoryModel');
        $cfg= CustomFieldsFactory::getConfig();

        $field->bind(JRequest::get());
        $paramobj=new JRegistry(JRequest::getVar('params',''));
        $field->params=$paramobj->toString('INI');
        $field->own_table=$cfg['tables'][$field->page];
        if ($field->id){
            //some fields do not change
            /* @var $db JDatabase */
            $db= JFactory::getDbo();
            $db->setQuery("select * from ".$field->getTableName()." where ".$field->getKeyName()."='".$field->id."'");
            $f=$db->loadObject();
            $field->db_name=$f->db_name;
            $field->page=$f->page;
            $field->own_table=$f->own_table;
        }
        $errors=$field->check();
        if (count($errors)){
            $message=JText::_("FACTORY_ERROR_SAVING_FIELDBR");
            $message.=join("<br>",$errors);
            $app= JFactory::getApplication();
            $app->redirect("index.php?option=".APP_EXTENSION."&task=fields.edit&id=$id",$message);
            return;
        }

        if (!$field->store()){
            $message=JText::_("FACTORY_ERROR_SAVING_FIELDBR");
            $app= JFactory::getApplication();
            $app->redirect("index.php?option=".APP_EXTENSION."&task=fields.edit&id=$id",$message);
            return;
        }

        $field_type = CustomFieldsFactory::getFieldType($field->ftype);
        if ($field_type->has_options)
        {
            $field_options=JRequest::getVar('field_option');
            if (count($field_options))
                foreach($field_options as $opt)
                    $field->store_option($opt);
        }

        if ($field->categoryfilter &&  $cfg['has_category'][$field->page])
            $model->setAssignedCats($field,$cat_filter);

        return $field;
    }
    function SaveField()
    {
        self::_SaveField();

        $this->setRedirect("index.php?option=".APP_EXTENSION."&task=fields.listfields",JText::_("FACTORY_FIELD_SAVED"));
    }
    function ApplyField()
    {
        $field=self::_SaveField();
        if ($field)
            $this->setRedirect("index.php?option=".APP_EXTENSION."&task=fields.edit&id=$field->id",JText::_("FACTORY_FIELD_SAVED"));
        else
            $this->setRedirect("index.php?option=".APP_EXTENSION."&task=fields.listfields",JText::_("FACTORY_FIELD_NOT_SAVED"));
                
    }
    function SaveField2New()
    {
        self::_SaveField();
        $this->setRedirect("index.php?option=".APP_EXTENSION."&task=fields.edit",JText::_("FACTORY_FIELD_SAVED"));
        //redirect to new
    }
    function DeleteField()
    {
        $cids=JRequest::getVar('cid');
        if (!is_array($cids)) $cids=array($cids);

        $field_object= JTable::getInstance('FieldsTable','JTheFactory');
        $i=0;        
        foreach($cids as $cid)
            if ($field_object->load($cid)){
                $field_object->delete();
                $i++;   
            }
        
        $this->setRedirect("index.php?option=".APP_EXTENSION."&task=fields.listfields",$i.' '.JText::_("FACTORY_FIELDS_DELETED"));
        
    }
    function ListFields()
    {
        /*
         * @var $model JTheFactoryModelFields;
         * */

        $model= JModel::getInstance('Fields','JTheFactoryModel');
        $filter_page=JRequest::getWord('filter_page');

        $cfg= CustomFieldsFactory::getConfig();

        $filters=array();
        $opts[]=JHtml::_('select.option','',JText::_("FACTORY_ALL"));
        foreach($cfg['pages'] as $page=>$text)
            $opts[]=JHtml::_('select.option',$page,$text);
        $filters['page']=JHtml::_('select.genericlist',$opts,'filter_page',"onchange=this.form.submit()",'value','text',$filter_page);

        $rows= $model->getFields(false,$filter_page);
		$pagination = $model->getPagination();
        
        $view=$this->getView('list');
        $view->assign('filter_html',$filters);
        $view->assignRef('rows',$rows);
        $view->assignRef('pagination',$pagination);
        $view->display();
    }
    function Reorder()
    {
        $field = JTable::getInstance('FieldsTable','JTheFactory');

		foreach ($_REQUEST as $k=>$v){
	        if (substr($k,0,6)=='order_'){
	            $id=substr($k,6);
	            $field->load($id);
	            $field->ordering=$v;
	            $field->store();
	        }
	    }
	    $this->setRedirect('index.php?option='.APP_EXTENSION.'&task=fields.listfields',JText::_('FACTORY_ORDERING_SAVED'));
        
    }
    function SaveOptions()
    {
        /*
         * @var $field JTheFactoryFieldsTable;
         * */
        $format=JRequest::getWord('format','html');
        $id=JRequest::getInt('fieldid');
        $field_options=JRequest::getVar('field_option');

        $field = JTable::getInstance('FieldsTable','JTheFactory');
        if (!$field->load($id))
        {
            $view=$this->getView('edit',$format);
            $view->assignRef('message',JText::_("FACTORY_FIELD_DOES_NOT_EXIST_ID").$id);
            $view->display('error');
            return;
        }

        if (count($field_options))
            foreach($field_options as $opt)
                $field->store_option($opt);

        $this->setRedirect("index.php?option=".APP_EXTENSION."&task=fields.getfieldoptions&format=$format");
        return;
    }
    function saveOption()
    {
        $format=JRequest::getWord('format','html');
        $id=JRequest::getInt('fieldid');
        $optionid=JRequest::getInt('id');
        $optionvalue=JRequest::getVar('optionvalue');

        $field = JTable::getInstance('FieldsTable','JTheFactory');
        if (!$field->load($id))
        {
            $view=$this->getView('edit',$format);
            $view->assignRef('message',JText::_("FACTORY_FIELD_DOES_NOT_EXIST_ID").$id);
            $view->display('error');
            return;
        }

        $field->update_option($optionid,$optionvalue);

        $view=$this->getView('edit',$format);
        $view->assignRef('field',$field);
        $view->display('saveoption');
    }
    function getFieldOptions()
    {
        $format=JRequest::getWord('format','html');
        $id=JRequest::getInt('fieldid');

        $field = JTable::getInstance('FieldsTable','JTheFactory');
        if (!$field->load($id))
        {
            $view=$this->getView('edit',$format);
            $view->assignRef('message',JText::_("FACTORY_FIELD_DOES_NOT_EXIST_ID").$id);
            $view->display('error');
            return;
        }

        $view=$this->getView('edit',$format);
        $view->assignRef('field',$field);
        $view->display('options');
    }
    function DeleteOption()
    {
        $format=JRequest::getWord('format','html');
        $fieldid=JRequest::getInt('fieldid');
        $id=JRequest::getInt('optionid');

        $field = JTable::getInstance('FieldsTable','JTheFactory');
        if (!$field->load($fieldid))
        {
            $view=$this->getView('edit',$format);
            $view->assignRef('message',JText::_("FACTORY_FIELD_DOES_NOT_EXIST_ID").$id);
            $view->display('error');
        }

        if (!$field->del_option($id))
        {
            $view=$this->getView('edit',$format);
            $view->assignRef('message',$field->getDbo()->getErrorMsg());
            $view->display('error');
            return;
        }

        $view=$this->getView('edit',$format);
        $view->assignRef('field',$field);
        $view->display('deleteoption');
    }
    function getFieldtypeParams()
    {
        $format=JRequest::getWord('format','html');

        $fieldid=JRequest::getInt('fieldid');
        $fieldtype=JRequest::getWord('ftype');

        $fieldtypeobj= CustomFieldsFactory::getFieldType($fieldtype);
        $paramsvalues='';

        $field=null;
        if ($fieldid){
            $field = JTable::getInstance('FieldsTable','JTheFactory');
            if (!$field->load($fieldid))
            {
                $view=$this->getView('edit',$format);
                $view->assignRef('message',JText::_("FACTORY_FIELD_DOES_NOT_EXIST_ID").$fieldid);
                $view->display('error');
                return;
            }
            $paramsvalues=$field->params;
        }

        $view=$this->getView('edit',$format);
        $view->assignRef('field',$field);
        $view->assignRef('paramvalues',$paramsvalues);
        $view->assignRef('field_type',$fieldtypeobj);
        $view->display('update_params');
    }
    
}

?>
