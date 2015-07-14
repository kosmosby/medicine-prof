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
 * @subpackage: installer
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.archive');
jimport('joomla.filesystem.path');

class TheFactoryInstaller
{
    var $cbplugins=null;
    var $plugins=null;
    var $modules=null;
    var $menus=null;
    var $joomfish=null;
    var $message=null;
    
    var $SQL=null;
    
    var $errors=null;
    var $warnings=null;
    
    var $_extension=null;
    var $_adapter=null;
    var $_componentid=null;
    var $_sourcepath=null;
    var $version=null;
    var $versionprevious=null;
    var $extension_message=null;
    
    function __construct($extensionname,$adapter)
    {
        
        $this->_extension=$extensionname;
        $this->_adapter=$adapter;
        $this->_sourcepath=$adapter->getParent()->getPath('source').DS.'components'.DS.$extensionname.DS.'installer';
        
        
        // Try extending time
        @set_time_limit( 240 );
        
        if ( version_compare( phpversion(), '5.0.0', '<' ) ) {
        	$this->warnings[]="PHP Version is pretty old. Consider upgrading it!";
        }
        
        //try to increase the memory limit step by step        
        $memMax=trim( @ini_get( 'memory_limit' ) );
        if ( $memMax ) {
        	$last			=	strtolower( $memMax{strlen( $memMax ) - 1} );
        	switch( $last ) {
        		case 'g':
        			$memMax	*=	1024;
        		case 'm':
        			$memMax	*=	1024;
        		case 'k':
        			$memMax	*=	1024;
        	}
        	if ( $memMax < 16000000 ) {
        		@ini_set( 'memory_limit', '16M' );
        	}
        	if ( $memMax < 32000000 ) {
        		@ini_set( 'memory_limit', '32M' );
        	}
        	if ( $memMax < 48000000 ) {
        		@ini_set( 'memory_limit', '48M' );		
        	}
        }
        
        @ignore_user_abort( true );

        $this->version=$this->getCurrentVersion();
        $this->versionprevious=$this->getPreviousVersion();
    }
    function getCurrentVersion()
    {
   	    $configfile = $this->_adapter->getParent()->getPath('source').DS.'administrator'.DS.'components'.DS.$this->_extension.DS.'application.ini';

        if ( !file_exists( $configfile ) )
        {
			return null;
        }

		if(function_exists('parse_ini_file'))
		{
			$ini = parse_ini_file($configfile,true);
		}
		else
		{
			jimport('joomla.registry.registry');
			jimport('joomla.filesystem.file');

			$handler = JRegistryFormat::getInstance('INI');
			$data = $handler->stringToObject( JFile::read($configfile) ,true);

			$ini = JArrayHelper::fromObject($data);
		}
        return $ini['extension']['version'];
    }
    function getPreviousVersion()
    {
   	    $configfile = JPATH_ADMINISTRATOR.DS.'components'.DS.$this->_extension.DS.'application.ini';

        if ( !file_exists( $configfile ) )
        {
			return null;
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
        return $ini['extension']['version'];
    }
    function AddMenuItem($menuname,$menuitemtitle, $itemalias, $link, $access=1,$params=null)
    {
        if (!isset($this->menus[$menuname]))
            $this->menus[$menuname]=array();

        $this->menus[$menuname][]=array(
            'name'=>$menuitemtitle,
            'alias'=>$itemalias,
            'link'=>$link,
            'access'=>$access,
            'params'=>$params
        );
        
    }
    function AddSQLStatement($sql)
    {
        $this->SQL[]=$sql;
    }
    function AddSQLFromFile($filename)
    {
        $filename=$this->_sourcepath.DS. "$filename";
        if (!file_exists($filename)) return;
        $contents = fread( fopen(  $filename, 'r' ), filesize($filename ) );
        
		$db = JFactory::getDBO();
		$sql=$db->splitSql($contents);
        if (count($this->SQL))
            $this->SQL=array_merge($this->SQL,$sql);
        else
            $this->SQL=$sql;
    }
    
    function AddModule($module_name,$module_title,$module_params='')
    {
        $this->modules[]=array(
            'name'=>$module_name,
            'title'=>$module_title,
            'params'=>$module_params
        );
        
    }
    function AddJoomfish($xmlfile)
    {
        $this->joomfish[]=$xmlfile;
    }
    function AddPlugin($plugin_name,$plugin_title,$plugin_type,$plugin_params)
    {
        $this->plugins[]=array(
            'name'=>$plugin_name,
            'title'=>$plugin_title,
            'type'=>$plugin_type,
            'params'=>$plugin_params
        );
    }
    
    function AddCBPlugin($plugintitle,$tabtitle,$pluginname,$classname)
    {
        $this->cbplugins[]=array(
            'title'=>$plugintitle,
            'tab'=>$tabtitle,
            'name'=>$pluginname,
            'class'=>$classname
            
        );
    }
    function AddMessage($msg)
    {
        $this->message[]=$msg;
    }
    function AddMessageFromFile($filename)
    {
        $filename=$this->_sourcepath.DS. "$filename";
        if (!file_exists($filename)) return;
        $contents = fread( fopen(  $filename, 'r' ), filesize($filename ) );
        $this->message[]=$contents;
        
    }
    
    function install()
    {
        $database = JFactory::getDBO();
        //Install Menus
    	$database->setQuery("SELECT extension_id FROM #__extensions where element ='".$this->_extension."'");
    	$this->_componentid = $database->LoadResult();
        
        if (count($this->menus))
        {
           	require_once(dirname(__FILE__).DS.'helper'.DS.'menu.php');
            foreach($this->menus as $menuname=>$menuitems)
            {
                $menu=new TheFactoryInstallerMenuHelper();
                $menu->title=$menuname;
                $menu->componentid=$this->_componentid;
                $j=0;
                foreach($menuitems as $item)
                {
                    $menu->AddMenuItem(
                        $item['name'],
                        $item['alias'],
                        $item['link'],
                        $j++,
                        $item['access'],
                        $item['params']
                    );
                }
                $this->extension_message[]=$menu->storeMenu();
            }
        }

        //Install CB Plugins
        if (count($this->cbplugins))
        {
	       $database->setQuery("select * from #__extensions where `element`='com_comprofiler'");
       	   $comprofiler = $database->loadResult();
	       if(count($comprofiler)<=0){
        		$this->extension_message[]="<div>";
		        $this->extension_message[]="<h2>Community Builder not detected</h2> <br>";
		        $this->extension_message[]="</div>";
	       }else{
       	        require_once(dirname(__FILE__).DS.'helper'.DS.'cbplugin.php');
                $cbpluginhelper=new TheFactoryInstallerCBPluginHelper($this->_sourcepath.DS.'cb_plug');
                foreach($this->cbplugins as $plugin)
                {
                    $this->extension_message[]=$cbpluginhelper->InstallCBPlugin(
                        $plugin['title'],
                        $plugin['tab'],
                        $plugin['name'],
                        'plug_'.JFolder::makeSafe($plugin['name']),
                        $plugin['class']
                    );    
                }
	       }
        }
        //Install SQL 
        if (count($this->SQL))
        {
            foreach($this->SQL as $sql)
            {
                if (trim($sql)){ //empty queries
                    $database->setQuery($sql);
                    if (!$database->query())
                        $this->errors[]=$database->_errorMsg;
                }
                
            }
            
        }
                
        if ($this->message)
        {
            $this->extension_message[]="<table width='100%'>";
            foreach($this->message as $message)
            {
                $this->extension_message[]="<tr>";
                $this->extension_message[]="<td><div>{$message}</div></td>";
                $this->extension_message[]="</tr>";
            }
            $this->extension_message[]="</table>";
        }
        
        
        
    }
    
    function upgrade()
    {
        $database = JFactory::getDBO();
        
        //Install CB Plugins
        if (count($this->cbplugins))
        {
	       $database->setQuery("select * from #__extensions where `element`='com_comprofiler'");
       	   $comprofiler = $database->loadResult();
	       if(count($comprofiler)<=0){
        		$this->extension_message[]="<div>";
		        $this->extension_message[]="<h2>Community Builder not detected</h2> <br>";
		        $this->extension_message[]="</div>";
	       }else{
       	        require_once(dirname(__FILE__).DS.'helper'.DS.'cbplugin.php');
                $cbpluginhelper=new TheFactoryInstallerCBPluginHelper($this->_sourcepath.DS.'cb_plug');
                foreach($this->cbplugins as $plugin)
                {
                    $this->extension_message[]=$cbpluginhelper->InstallCBPlugin(
                        $plugin['title'],
                        $plugin['tab'],
                        $plugin['name'],
                        'plug_'.$plugin['name'],
                        $plugin['class']
                    );    
                }
	       }
        }

        $update_class=$this->_sourcepath.DS.'upgrade'.DS.'upgrade.php';
        if (JFile::exists($update_class))
        {
            require_once($this->_sourcepath.DS.'upgrade'.DS.'upgrade.php');
            JTheFactoryUpgrade::upgrade($this->versionprevious);
        }
        
        $update_sql=$this->_sourcepath.DS.'upgrade'.DS.'upgrade_all.sql';
        if (JFile::exists($update_sql))
            $this->AddSQLFromFile($update_sql);

        $update_sql='upgrade'.DS.'upgrade_'.$this->versionprevious.'.sql';
        $this->AddSQLFromFile($update_sql);
                
        //Install SQL 
        if (count($this->SQL))
        {
            foreach($this->SQL as $sql)
            {
                $database->setQuery($sql);
                if (!$database->query())
                    $this->errors[]=$database->_errorMsg;
                
            }
            
        }
                
        if ($this->message)
        {
            $this->extension_message[]="<table width='100%'>";
            foreach($this->message as $message)
            {
                $this->extension_message[]="<tr>";
                $this->extension_message[]="<td><div>{$message}</div></td>";
                $this->extension_message[]="</tr>";
            }
            $this->extension_message[]="</table>";
        }
        
    }

}
?>
