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
 * @subpackage: library
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class JTheFactoryController extends JController
{
    var $name;
    var $_name;
    var $modulename;
    var $basepath;
    
	function __construct($modulename=null)
	{
        $this->modulename=$modulename;
        if(!$modulename)
            $this->modulename=$this->name;
        
        $MyApp= JTheFactoryApplication::getInstance();
        $this->basepath=$MyApp->app_path_admin.strtolower($this->modulename);
        $config=array(
            'view_path'=>$this->basepath.DS."views"
        );
        parent::__construct($config);
        
        $this->loadLanguage();
        
        $this->registerClass('toolbar');
        $this->registerClass('helper');
        $this->registerClass('submenu');
        if (file_exists($this->basepath.DS."models"))
            JTheFactoryHelper::modelIncludePath($this->modulename);
        if (file_exists($this->basepath.DS."tables"))
            JTheFactoryHelper::tableIncludePath($this->modulename);
    }
    function getClassName($type)
    {
        return 'JTheFactory'.ucfirst($this->name).ucfirst($type);        
    }
    function registerClass($type)
    {
        $filename=$this->basepath.DS.strtolower($type).DS.strtolower($this->name).'.php';
        if (file_exists($filename))
            JLoader::register($this->getClassName($type),$filename);
        else
            JLoader::register($this->getClassName($type),$this->basepath.DS.'helper'.DS.strtolower($type).'.php');
        
    }
    function loadLanguage()
    {
       $lang=JFactory::getLanguage();
       $lang->load('thefactory.'.strtolower($this->modulename));
        
    }
    function execute($task)
    {
        if (strpos($task,'.')!==FALSE) //task=controller.task?
        {
            $task=explode('.',$task);
            $task=$task[1];
        }
        if (class_exists($this->getClassName('toolbar')) && is_callable(array($this->getClassName('toolbar'),'display')))
            call_user_func(array($this->getClassName('toolbar'),'display'),$task);            
        return parent::execute($task);
    }
    function getView( $name = '', $type = 'html', $prefix = '', $config = array() )
    {
        if ($name)
            $config['template_path']=$this->basepath.DS."views".DS.strtolower($name).DS."tmpl";
        else
            $config['template_path']=$this->basepath.DS."views".DS.strtolower($this->modulename).DS."tmpl";
        
        return parent::getView($name,$type,'JTheFactoryView'.ucfirst($this->modulename),$config);
    }

}
