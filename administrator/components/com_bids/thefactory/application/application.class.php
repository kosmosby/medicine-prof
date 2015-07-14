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
 * @subpackage: application
-------------------------------------------------------------------------*/ 
defined('_JEXEC') or die('Restricted access');

/**
 * defined constants
 *         APP_EXTENSION  - Extension name with com_
 *         APP_PREFIX     - Table prefix used for extension
 *         COMPONENT_VERSION  - Extension Version
 *         SMARTY_DIR         - Smarty folder
 */

class JTheFactoryApplication extends JObject{

	/* the Application Instance Name */
	var $appname = 'default';

	/* the Application Component prefix */
	var $prefix = null;

	/* the Application Instance Configuration */
	var $ini = null;

	/* the Application Framework version */
	var $_version = '1.2.1';
	/* the Application loaded addons */

	var $_addons = array();
    var $_controller = null;
    var $_stopexecution = false;
    
	var $frontpage = 0;

	var $app_path_admin = '';
	var $app_path_front = '';

	/**
	 * Enhances a Instance of an Application in an static
	 * array of applications
	 *
	 * @param $configfile path
	 * @param $name
	 * @return JTheFactoryApplication
	 */
	static function &getInstance( $configfile=null, $front=0 , $name='default' )
	{
		static $instances;
		if (!isset( $instances[$name] ) )
			$instances[$name] = new JTheFactoryApplication( $configfile, $front );

		return $instances[$name];
	}

	function __construct( $configfile=null, $runfront=null )
	{

        if ($runfront!==null)
            $this->frontpage=$runfront;

        if ( !$configfile )
        	$configfile = JPATH_COMPONENT_ADMINISTRATOR.DS.'application.ini';

        if ( !file_exists( $configfile ) )
        {
			$error = JError::raiseError(500, 'Unable to load application configfile: '.$configfile);
			return $error;
        }

		if(function_exists('parse_ini_file'))
		{
			$ini = parse_ini_file($configfile,true);
		}
		else
		{
			jimport('joomla.registry.registry');
			jimport('joomla.filesystem.file');

			$handler =  JRegistryFormat::getInstance('INI');
			$data = $handler->stringToObject( JFile::read($configfile) ,true);

			$ini = JArrayHelper::fromObject($data);
		}

        $this->ini			= $ini;
        $this->appname		= $this->getIniValue('name');
        $this->prefix		= $this->getIniValue('prefix');
        $this->description	= $this->getIniValue('description');
        $this->app_path_admin = JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_'.$this->appname.DS.'thefactory'.DS;
        $this->app_path_front = JPATH_ROOT.DS.'components'.DS.'com_'.$this->appname.DS.'thefactory'.DS;

        if (is_int($this->getIniValue('error_reporting'))) error_reporting(JDEBUG ? E_ALL : $this->getIniValue('error_reporting'));

        if (!defined('APP_EXTENSION')) define('APP_EXTENSION', 'com_'.$this->appname );
        if (!defined('APP_PREFIX')) define('APP_PREFIX', $this->prefix );
        if (!defined('COMPONENT_VERSION')) define('COMPONENT_VERSION', $this->getIniValue('version') );

        $defines=$this->getSection('defines');
        if (count($defines))
            foreach ($defines as $const=>$val)
                if ( !defined($const) ) define($const,$val);

        // LIBRARIES, HELPERS &
        jimport('joomla.application.component.model');
        jimport('joomla.filesystem.folder');
        
        JLoader::register('JTheFactoryHelper',$this->app_path_front.'front.helper.php');
		if ( !$this->frontpage ){
		    JLoader::register('JTheFactoryController',$this->app_path_admin.'library'.DS.'admin.controller.php');
            JLoader::register('JTheFactoryAdminHelper',$this->app_path_admin.'library'.DS.'admin.helper.php');
        }
        
		if ($this->frontpage )
            $modules=JFolder::folders($this->app_path_front,'',false,true);
        else
            $modules=JFolder::folders($this->app_path_admin,'',false,true);

        foreach($modules as $module)
            if ($module && file_exists($module.DS.'register.php'))
            {
                require_once ($module.DS.'register.php');
                $className='JTheFactory'.ucfirst(basename($module)).'Register';
                call_user_func(array($className,'registerModule'),$this);
            }
        

        JLoader::register('JTheFactoryDatabase', $this->app_path_admin .'library'. DS . 'database' . DS . 'admin.database.php');
        // Custom Fields
        if ($this->getIniValue('use_extended_profile'))
        {
	        JLoader::register('JTheFactoryUserProfile',$this->app_path_front.'front.userprofile.php');
        }
        if ($this->getIniValue('use_acl')){
			JLoader::register('JTheFactoryACL',$this->app_path_front.'thefactory'.DS.'front.acl.php');
        }
        if ($this->getIniValue('use_smarty_templates')){
            if (!defined('SMARTY_DIR')) define('SMARTY_DIR', JPATH_COMPONENT_SITE.'/libraries/smarty/libs/');
            JLoader::register('Smarty',SMARTY_DIR . 'Smarty.class.php');
			JLoader::register('JTheFactorySmarty',$this->app_path_front.'front.smarty.php');
			JLoader::register('JTheFactorySmartyView',$this->app_path_front.'front.smartyview.php');
        }
	}

	function getSection($section)
	{
	    return isset($this->ini[$section])?$this->ini[$section]:null;
	}

	function getIniValue($key,$section='extension')
	{
        return isset($this->ini[$section][$key])?$this->ini[$section][$key]:null;
	}
    
    /**
     * JTheFactoryApplication::Initialize()
     *  loads some "magic" files like defines.php,loads the main helper file (APP_PREFIX.php)
     *  loads other helpers (through helper::loadHelperclasses)
     *  loads the needed Controller
     *  adds htmlelements and formelements to path (admin)
     *  adds tables to paths
     * 
     * @return void
     */
    function Initialize($task=null)
    {
        jimport('joomla.application.component.controller');
        if (!$this->frontpage){
            JFormHelper::addFieldPath(JPATH_COMPONENT_ADMINISTRATOR.DS.'formelements');   
            
        }
        JHtml::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'htmlelements');
        JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');
        
        if (file_exists(JPATH_COMPONENT_SITE.DS.'defines.php'))
            require_once(JPATH_COMPONENT_SITE.DS.'defines.php');

        if (file_exists(JPATH_COMPONENT_SITE.DS.'helpers'.DS.strtolower($this->appname).'.php'))
            require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.strtolower($this->appname).'.php');
        $class_name=ucfirst($this->appname).'Helper';
        call_user_func(array($class_name,'loadHelperClasses'));

        JTheFactoryEventsHelper::triggerEvent('onBeforeExecuteTask',array(&$this->_stopexecution));

        if ($this->_stopexecution) return;        
        if (!$task) $task = JRequest::getCmd('task');

        $this->_controller=JTheFactoryHelper::loadController($task ); //Try to load Framework controllers
        if (!$this->_controller)
        {
            $controllerClass = JRequest::getWord('controller');
            if (!$controllerClass && strpos($task,'.')!==FALSE) //task=controller.task?
            {
                $task=explode('.',$task);
                $controllerClass=$task[0];
            }
            if ($controllerClass)
            {
                $path = JPATH_COMPONENT.DS.'controllers'.DS.basename($controllerClass).'.php';
                file_exists($path) ? require_once($path) : JError::raiseError(500, JText::_('ERROR_CONTROLLER_NOT_FOUND'));
                $controllerClass='J'.ucfirst($this->appname).($this->frontpage?"":"Admin").'Controller'.$controllerClass;
                $this->_controller=new $controllerClass;
            } else {
                $this->_controller = JControllerLegacy::getInstance( 'J'.ucfirst($this->appname).($this->frontpage?"":"Admin") );
            }
        }
    }

    function dispatch($task=null)
    {
        if ($this->_stopexecution) return;
        if (!$this->_controller) return;
        if (!$task) $task = JRequest::getCmd('task');

        if (strpos($task,'.')!==FALSE) //task=controller.task?
        {
            $task=explode('.',$task);
            $task=$task[1];
        }
        $this->_controller->execute($task);

        JTheFactoryEventsHelper::triggerEvent('onAfterExecuteTask',array($this->_controller));

        $this->_controller->redirect();

    }
    
}
