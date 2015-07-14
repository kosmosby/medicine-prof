<?php
/**
 * @version     4.0 +
 * @package     Open Source Excellence Central Processing Units
 * @author      Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
 * @author      Created on 17-May-2011
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @Copyright Copyright (C) 2010- Open Source Excellence (R)
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
defined('_JEXEC') or die(";)");
if(!defined('OSEREGISTER_DEFAULT_PATH'))
{
	define('OSEREGISTER_DEFAULT_PATH', dirname(dirname(__FILE__)));
}
require_once(dirname(__FILE__).DS.'oseMethods.php');
require_once(dirname(__FILE__).DS.'oseConfig.php');
jimport('joomla.version');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
if(!class_exists("oseRegistry"))
{
	class oseRegistry
	{
		function __construct()
		{}
		function __toString()
		{
			return get_class($this).' extend Registry';
		}
		private static function readXMLfile($xmlName, $xml)
		{

			$_default= OSEREGISTER_DEFAULT_PATH.DS.$xmlName;
			$_custom= JPATH_SITE;
			
			$files= $xml->files->file;
			if(empty($files))
			{
				return;
			}
			
			for($i= 0; $i<COUNT($files); $i++)
			{
				$custom_path= $_custom.DS.$files[$i];
				$default_path= $_default.DS.$files[$i];
				// Need to Re Define the Directory Seperator?
				
				if(JFile :: exists($custom_path))
				{
					require_once($custom_path);
				}
				else
				{
					if(JFile :: exists($default_path))
					{
						require_once($default_path);
					}
				}
			}
			$folders= $xml->folders->folder;
			if(empty($folders))
			{
				return;
			}
			for ($i = 0; $i<COUNT($folders); $i++)
			{
				$custom_path= $_custom.DS.$folders[$i];
				$default_path= $_default.DS.$folders[$i];
				if(JFolder :: exists($custom_path))
				{
					$dirFiles= JFolder :: files($custom_path, '.php', true, true);
					foreach($dirFiles as $dirFile)
					{
						require_once($dirFile);
					}
				}
				else
				{
					if(JFolder :: exists($default_path))
					{
						$dirFiles= JFolder :: files($default_path, '.php', true, true);
						foreach($dirFiles as $dirFile)
						{
							require_once($dirFile);
						}
					}
				}	
			}	
			return true;
		}
		private static function readXMLclass($xmlName, $xml)
		{
			$_default= OSEREGISTER_DEFAULT_PATH.DS.$xmlName;
			$_custom= JPATH_SITE;
			
			$classes= $xml->classes;
			
			if(empty($classes))
			{
				return;
			}
			
			for($i=0;$i<COUNT($classes); $i++)
			{
				$custom_path= $default_path= $_default.DS.$xmlName.'.php';
				// if custom, load custom, else load default
				if(JFile :: exists($custom_path))
				{
					require_once($custom_path);
				}
			}
			return true;
		}
		private static function readXMLfileLegacy($xmlName, $xml)
		{
			$document= $xml->document;
			// default file path & custom file path
			$_default= OSEREGISTER_DEFAULT_PATH.DS.$xmlName;
			$_custom= JPATH_SITE;
			$files= $document->getElementByPath('files');
			if(empty($files))
			{
				return;
			}
			$files= $files->children();
			foreach($files as $file)
			{
				$attr= $file->attributes();
				if(empty($attr['path']))
				{
					$custom_path= $default_path= $_default.DS.$file->data();
				}
				else
				{
					$custom_path= $_custom.DS.$attr['path'].DS.$file->data();
					$default_path= $_default.DS.$attr['path'].DS.$file->data();
				}
				// Need to Re Define the Directory Seperator?
				if($file->name() == 'file')
				{
					// if custom, load custom, else load default
					if(JFile :: exists($custom_path))
					{
						require_once($custom_path);
					}
					else
					{
						if(JFile :: exists($default_path))
						{
							require_once($default_path);
						}
					}
				}
				elseif($file->name() == 'folder')
				{
					// if custom, load custom, else load default
					if(JFolder :: exists($custom_path))
					{
						$dirFiles= JFolder :: files($custom_path, '.php', true, true);
						foreach($dirFiles as $dirFile)
						{
							require_once($dirFile);
						}
					}
					else
					{
						if(JFolder :: exists($custom_path))
						{
							$dirFiles= JFolder :: files($default_path, '.php', true, true);
							foreach($dirFiles as $dirFile)
							{
								require_once($dirFile);
							}
						}
					}
				}
			}
			return true;
		}
		private static function readXMLclassLegacy($xmlName, $xml)
		{
			$document= $xml->document;
			static $version;
			if(empty($version))
			{
				$version= new JVersion();
				$version= substr($version->getShortVersion(), 0, 3);
			}
			// default file path & custom file path
			if($version == '1.5')
			{
				$_default= OSEREGISTER_DEFAULT_PATH.DS.$xmlName;
			}
			elseif($version == '1.6')
			{
				$_default= OSEREGISTER_DEFAULT_PATH.DS.$xmlName;
			}
			else
			{
				$_default= OSEREGISTER_DEFAULT_PATH.DS.$xmlName;
			}
			$_custom= JPATH_SITE;
			$files= $document->getElementByPath('classes');
			if(empty($files))
			{
				oseExit('XML Error!');
			}
			$files= $files->children();
			foreach($files as $file)
			{
				$attr= $file->attributes();
				// Need to Re Define the Directory Seperator?
				$custom_path= $_custom.DS.$attr['path'];
				$default_path= $_default.DS.$attr['path'];
				if(empty($attr['path']))
				{
					$file_data= $file->data();
					if(empty($file_data))
					{
						$custom_path= $default_path= $_default.DS.$xmlName.'.php';
					}
					else
					{
						$custom_path= $default_path= $_default.DS.$file_data.'.php';
					}
				}
				// if custom, load custom, else load default
				if(JFile :: exists($custom_path))
				{
					require_once($custom_path);
				}
				else
				{
					if(JFile :: exists($default_path))
					{
						require_once($default_path);
					}
					else
					{
						return false;
					}
				}
			}
			return true;
		}
		public static function register($option, $className)
		{
			if(empty($option) || empty($className))
			{
				oseExit('OSE Registry Error Occurs');
			}
			$session= JFactory :: getSession();
			$items= $session->get('oseClass', array());
			$items[$option]= $className;
			$session->set('oseClass', $items);
			return true;
		}
		public static function call($option, $params= array())
		{
			static $instance;
			$session= JFactory :: getSession();
			$_class= $session->get('oseClass', array());
			// session value?
			if(empty($_class[$option]))
			{
				if(in_array($session->getState(), array('expired', 'destroyed')) && class_exists('plgSystemJfusion'))
				{
					$uri= & JURI :: getInstance();
					//add a variable to ensure refresh
					$link= $uri->toString();
					$mainframe= JFactory :: getApplication();
					$mainframe->redirect($link);
				}
				else
				{
					$t= debug_backtrace(false);
					foreach($t as $d)
					{
						echo $d['file'].' '.$d['function'].' line:'.$d['line'];
						echo "<br \>";
					}
					oseExit('No class of '.$option.' is loaded! To avoid critical error, proccess aborted');
				}
			}
			//
			$className= $_class[$option];
			//$xmlName = strtolower($className);
			$xmlName= $className;
			$filePath= OSEREGISTER_DEFAULT_PATH.DS.$xmlName.DS.$xmlName.'.xml';
			if(!JFile :: exists($filePath))
			{
				oseExit("OSE Registry Fails to Load the Object:{$className}!");
			}
			if (JOOMLA16==false)
			{
				$xml= JFactory :: getXMLParser('simple');
				$xml->loadFile($filePath);			
				self :: readXMLfileLegacy($xmlName, $xml);
				self :: readXMLclassLegacy($xmlName, $xml);
			}
			else
			{
				$xml= JFactory :: getXML($filePath);
				self :: readXMLfile($xmlName, $xml);
				self :: readXMLclass($xmlName, $xml);
			}		
			// get the file path;

			// class exists?
			$_class[$option]= self :: autoLoad($_class[$option]);
			//$session->set('oseClass',$_class);
			$instance[$option]=(!isset($instance[$option])) ? null : $instance[$option];
			if(!($instance[$option] instanceof $_class[$option]))
			{
				$instance[$option]= new $_class[$option]($params);
			}
			if(method_exists($instance[$option], 'getInstanceByVersion'))
			{
				return call_user_func(array($instance[$option], 'getInstanceByVersion'), $params);
			}
			else
			{
				return $instance[$option];
			}
		}
		private static function autoLoad($className)
		{
			$_className=(!class_exists($className)) ? 'ose'.$className : $className;
			if(!class_exists($_className))
			{
				oseExit('Class '.$_className.' Does Not Exists! For Not Having A Fatal Error Later, The Proccess Aborted');
			}
			return $_className;
		}
		public static function quickCall($name)
		{
			self :: register($name, $name);
			return self :: call($name);
		}
		public static function quickRequire($option)
		{
			static $instance;
			$session= JFactory :: getSession();
			$_class= $session->get('oseClass', array());
			// session value?
			if(empty($_class[$option]))
			{
				// return fasle;
				oseExit('No Class Loaded! For Not Having A Fatal Error Later, The Proccess Aborted');
			}
			//
			$className= $_class[$option];
			//$xmlName = strtolower($className);
			$xmlName= $className;
			$filePath= OSEREGISTER_DEFAULT_PATH.DS.$xmlName.DS.$xmlName.'.xml';
			if(!JFile :: exists($filePath))
			{
				oseExit('OSE Registry Fails to Load the Object!');
			}
			if (JOOMLA16==false)
			{
				$xml= JFactory :: getXMLParser('simple');
				$xml->loadFile($filePath);			
				self :: readXMLfileLegacy($xmlName, $xml);
				self :: readXMLclassLegacy($xmlName, $xml);
			}
			else
			{
				$xml= JFactory :: getXML($filePath);
				self :: readXMLfile($xmlName, $xml);
				self :: readXMLclass($xmlName, $xml);
			}
		}
		static function registerApp($app)
		{
			$session= JFactory :: getSession();
			$item= $session->get('oseApp', null);
			$item= $app;
			$session->set('oseApp', $item);
		}
		function getCurrentApp($app= null)
		{
			$session= JFactory :: getSession();
			$item= $session->get('oseApp', $app);
			return $item;
		}
		
		public static function directCall($folder,$params = array())
		{
			$xmlName = $folder;
			$filePath = OSEREGISTER_DEFAULT_PATH.DS.$xmlName.DS.$xmlName.'.xml';
			$className = $folder;
			if(!JFile::exists($filePath))
			{
				oseExit("OSE Registry Fails to Load the Object:{$className}!");
			}
		    if (JOOMLA16==false)
			{
				$xml= JFactory :: getXMLParser('simple');
				$xml->loadFile($filePath);			
				self :: readXMLfileLegacy($xmlName, $xml);
				self :: readXMLclassLegacy($xmlName, $xml);
			}
			else
			{
				$xml= JFactory :: getXML($filePath);
				self :: readXMLfile($xmlName, $xml);
				self :: readXMLclass($xmlName, $xml);
			}
		}
	}
}
