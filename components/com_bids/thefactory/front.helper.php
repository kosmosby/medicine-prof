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
 * @subpackage: 
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');


class JTheFactoryHelper extends JObject
{
    static function &getConfig()
    {
        static $config;

        if ($config) return $config;

        $MyApp= JTheFactoryApplication::getInstance();
        $configfile=$MyApp->getIniValue('option_file');
        $classname=ucfirst(APP_PREFIX)."Config";
        require_once(JPATH_SITE.DS.'components'.DS.APP_EXTENSION.DS.$configfile);

        $config=new $classname();

        return $config;
    }

	static function str_clean(& $string ) {
		
		$string = strtolower($string);
		
		$customToReplace = array();
		$customReplacements = array();
		$string = str_replace($customToReplace,$customReplacements,$string);

		$aToReplace = array(" ","/","&","�","�","�","!","$","%","@","?","#","(",")","+","*",":",";","'","\"");
		$aReplacements = array("-","-","and","");
		$string = str_replace($aToReplace,$aReplacements,$string);

		$string=preg_replace('/[^\x{032}-\x{07F}]/','-',$string);
		
		return $string;
	}
	static function modelIncludePath($framework_module)
    {
        $MyApp= JTheFactoryApplication::getInstance();
        JModelLegacy::addIncludePath($MyApp->app_path_admin.strtolower($framework_module).DS.'models');
    }
    static function tableIncludePath($framework_module)
   {
       $MyApp= JTheFactoryApplication::getInstance();
       JTable::addIncludePath($MyApp->app_path_admin.strtolower($framework_module).DS.'tables');
   }
   static function loadController($task)
   {
        //atentie la Integration sunt mai multe controllere
        if (strpos($task,'.')!==FALSE) //task=controller.task?
        {
            $task=explode('.',$task);
            $module=$task[0];
            $controllerName='JTheFactory'.ucfirst($module).'Controller';
            $taskName=$task[1];

            if (class_exists($controllerName))
            {
                $controller=new $controllerName;
                return $controller;
            }
            $MyApp= JTheFactoryApplication::getInstance();
            if ($MyApp->frontpage){
                $controllerfile=$MyApp->app_path_front.strtolower($module).DS.'controllers'.DS.strtolower($module).'.php';
            }else{
                $controllerfile=$MyApp->app_path_admin.strtolower($module).DS.'controllers'.DS.strtolower($module).'.php';
            }

            if (file_exists($controllerfile))
            {
                require_once($controllerfile);
                if (class_exists($controllerName))
                {
                    $controller=new $controllerName;
                    return $controller;
                }
            }
            
        }
		return false;
   }
	/**
	 * Reads a Remote HTTP file using Curl or fopen
	 * Retruns a string containg the URL content
	 * or false if an error occured 
	 *
	 * @param string $uri the url that needs to be read
	 * @param int $timeout timeout for the connect operation
	 * @return mixed returns the string read or false if failed
	 */
    function remote_read_url( $uri ,$timeout=30) {

        if ( function_exists('curl_init') ){
            $handle = curl_init();

            curl_setopt ($handle, CURLOPT_URL, $uri);
            curl_setopt ($handle, CURLOPT_MAXREDIRS,5);
            curl_setopt ($handle, CURLOPT_AUTOREFERER, 1);
            @curl_setopt ($handle, CURLOPT_FOLLOWLOCATION ,1); //not in safe mode
            curl_setopt ($handle, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt ($handle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt ($handle, CURLOPT_TIMEOUT, $timeout);

            $buffer = @curl_exec($handle);

            curl_close($handle);
            return $buffer;
        } else  if ( ini_get('allow_url_fopen') ) {
            $fp = @fopen( $uri, 'r' );
            if ( !$fp ) return false;
         	stream_set_timeout($fp, $timeout);
            $linea = '';
            while( $remote_read = fread($fp, 4096) )
                $linea .= $remote_read;
       		$info = stream_get_meta_data($fp);
            fclose($fp);
        	if ($info['timed_out']) return false;
            return $linea;
        } else {
           return false;
        }
    }

   
}
