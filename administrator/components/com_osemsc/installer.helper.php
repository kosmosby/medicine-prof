<?php
/**
  * @version     5.0 +
  * @package        Open Source Membership Control - com_osemsc
  * @subpackage    Open Source Access Control - com_osemsc
  * @author        Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
  * @author        Created on 15-Nov-2010
  * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
  *
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
  *  @Copyright Copyright (C) 2010- Open Source Excellence (R)
*/
defined('_JEXEC') or die("Direct Access Not Allowed");
class oseInstallerHelper
{
	var $backendPath;
	var $frontendPath;
	var $successStatus;
	var $failedStatus;
	var $notApplicable;
	var $totalStep;
	var $pageTitle;
	var $verifier;
	var $dbhelper;
	var $template;
	var $component;
	var $frontendCPUPath;
	var $backendCPUPath;
	function __construct()
	{
		jimport('joomla.application.component.controller');
		jimport('joomla.application.component.model');
		jimport('joomla.installer.installer');
		jimport('joomla.installer.helper');
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.archive');
		jimport('joomla.filesystem.path');
		$this->component= 'com_osemsc';
		$this->com_title= OSEMSCTITLE. '-'. OSEMSCVERSION;
		$this->backendPath= JPATH_ROOT.DS.'administrator'.DS.'components'.DS.$this->component.DS;
		$this->frontendPath= JPATH_ROOT.DS.'components'.DS.$this->component.DS;
		$this->frontendCPUPath= JPATH_ROOT.DS.'components'.DS.'com_ose_cpu'.DS;
		$this->backendCPUPath= JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_ose_cpu'.DS;
		$this->successStatus= '<div style="float:left;">.....&nbsp;</div><div style="color:#009900;">'.JText :: _('Installation completed').'</div><div style="clear:both;"></div>';
		$this->failedStatus= '<div style="float:left;">.....&nbsp;</div><div style="color:red;">'.JText :: _('Installation failed').'</div><div style="clear:both;"></div>';
		$this->notApplicable= '<div style="float:left;">.....&nbsp;</div><div>'.JText :: _('Installation not applicable').'</div><div style="clear:both;"></div>';
		$this->totalStep= 5;
		require_once(dirname(__FILE__).DS.'installer.template.php');
		$this->verifier= new oseInstallerVerifier();
		$this->template= new oseInstallerTemplate();
	}
	function install()
	{
		//check php version
		$installedPhpVersion= floatval(phpversion());
		$supportedPhpVersion= 5.1;
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.$this->component.DS.'installer.template.php');
		$step= JRequest :: getVar('step', '', 'post');
		$helper= new oseInstallerHelper;
		if($installedPhpVersion < $supportedPhpVersion)
		{
			$html= oseInstallerHelper :: getErrorMessage(101, $installedPhpVersion);
			$status= false;
			$nextstep= 0;
			$title= JText :: _('OSE Installer for').' '.$this->com_title;
			$install= 1;
			$substep= 0;
		}
		else
		{
			if(!empty($step))
			{
				$progress= $helper->installSteps($step);
				$html= $progress->message;
				$status= $progress->status;
				$nextstep= $progress->step;
				$title= $progress->title;
				$install= $progress->install;
				$substep= isset($progress->substep) ? $progress->substep : 0;
			}
			else
			{
				$nextstep= 1;
				$verifier= new oseInstallerVerifier();
				$imageTest= $verifier->testImage();
				$template= new oseInstallerTemplate();
				$html= $template->getHTML('welcome', $imageTest);
				$status= true;
				$title= JText :: _('OSE Installer for').' '.$this->com_title;
				$install= 1;
				$substep= 0;
			}
		}
		$this->template->cInstallDraw($html, $nextstep, $title, $status, $install, $substep);
		return;
	}
	function installSteps($step= 1)
	{
		$db= JFactory :: getDBO();
		switch($step)
		{
			case 1 :
				//check requirement
				$status= $this->checkRequirement(2);
				break;
			case 2 :
				//install backend system
				$status= $this->installBackend(3);
				break;
			case 3 :
				//install ajax system
				$status= $this->installCOMCPU(4);
				break;
			case 4 :
				//install frontend system
				$status= $this->installFrontend(5);
				break;
			case 5 :
				//install template
				$status= $this->prepareDatabase(6);
				break;
			case 6 :
			case 'UPDATE_DB' :
				//prepare database
				$status= $this->updateDatabase(7);
				break;
			case 7 :
				$status= $this->installPlugin(8);
				break;
			case 8 :
				$status= $this->installViews(9);
				break;
			case 9 :
				$status= $this->clearInstallation(100);
				break;
			case 100 :
				//show success message
				$status= $this->installationComplete(0);
				break;
			default :
				$status= new stdClass();
				$status->message= $this->getErrorMessage(0, '0a');
				$status->step= '-99';
				$status->title= JText :: _('OSE INSTALLER');
				$status->install= 1;
				break;
		}
		return $status;
	}
	function checkRequirement($step)
	{
		$status= true;
		$this->pageTitle= JText :: _('Checking Requirements');
		$html= '';
		$html .= '<div style="width:300px; float:left;">'.JText :: _('BACKEND ARCHIVE').'</div>';
		if(!$this->verifier->checkFileExist($this->backendPath.'admin.zip'))
		{
			$html .= $this->failedStatus;
			$status= false;
			$errorCode= '1a';
		}
		else
		{
			$html .= $this->successStatus;
		}
		$html .= '<div style="width:300px; float:left;">'.JText :: _('OSE CPU Backend ARCHIVE').'</div>';
		if(!$this->verifier->checkFileExist($this->backendPath.'com_cpu_admin.zip'))
		{
			$html .= $this->failedStatus;
			$status= false;
			$errorCode= '1b';
		}
		else
		{
			$html .= $this->successStatus;
		}
		$html .= '<div style="width:300px; float:left;">'.JText :: _('OSE CPU FRONTEND ARCHIVE').'</div>';
		if(!$this->verifier->checkFileExist($this->backendPath.'com_cpu_site.zip'))
		{
			$html .= $this->failedStatus;
			$status= false;
			$errorCode= '1b';
		}
		else
		{
			$html .= $this->successStatus;
		}
		$html .= '<div style="width:300px; float:left;">'.JText :: _('OSE Membership ARCHIVE for OSE CPU').'</div>';
		if(!$this->verifier->checkFileExist($this->backendPath.'cpuMSC.zip'))
		{
			$html .= $this->failedStatus;
			$status= false;
			$errorCode= '1b';
		}
		else
		{
			$html .= $this->successStatus;
		}
		$html .= '<div style="width:300px; float:left;">'.JText :: _('OSE FRONTEND ARCHIVE').'</div>';
		if(!$this->verifier->checkFileExist($this->frontendPath.'site.zip'))
		{
			$html .= $this->failedStatus;
			$status= false;
			$errorCode= '1c';
		}
		else
		{
			$html .= $this->successStatus;
		}
		$html .= '<div style="width:300px; float:left;">'.JText :: _('OSE CORE PLUGIN ARCHIVE').'</div>';
		if(!$this->verifier->checkFileExist($this->backendPath.'ose_plugins.zip'))
		{
			$html .= $this->failedStatus;
			$status= false;
			$errorCode= '1e';
		}
		else
		{
			$html .= $this->successStatus;
		}
		if($status)
		{
			$autoSubmit= $this->getAutoSubmitFunction();
			//$form = $this->getInstallForm(2);
			$message= $autoSubmit.$html;
		}
		else
		{
			$errorMsg= $this->getErrorMessage(1, $errorCode);
			$message= $html.$errorMsg;
			$step= $step -1;
		}
		$drawdata= new stdClass();
		$drawdata->message= $message;
		$drawdata->status= $status;
		$drawdata->step= $step;
		$drawdata->title= JText :: _('OSE CHECKING REQUIREMENT');
		$drawdata->install= 1;
		return $drawdata;
	}
	function getAutoSubmitFunction()
	{
		ob_start();
?>
		<script type="text/javascript">
		var i=3;

		function countDown()
		{
			if(i >= 0)
			{
				document.getElementById("timer").innerHTML = i;
				i = i-1;
				var c = window.setTimeout("countDown()", 1000);
			}
			else
			{
				document.getElementById("div-button-next").removeAttribute("onclick");
				document.getElementById("input-button-next").setAttribute("disabled","disabled");
				document.forms["installform"].submit();
			}
		}

		window.addEvent('domready', function() {
			countDown();
		});

		</script>
		<?php

		$autoSubmit= ob_get_contents();
		@ ob_end_clean();
		return $autoSubmit;
	}
	function installBackend($step)
	{
		$html= '';
		$html .= '<div style="width:300px; float:left;">'.JText :: _('OSE BACKEND INSTALLATION').'</div>';
		$zip= $this->backendPath.'admin.zip';
		$destination= $this->backendPath;
		if($this->extractArchive($zip, $destination))
		{
			$html .= $this->successStatus;
			$autoSubmit= $this->getAutoSubmitFunction();
			$message= $autoSubmit.$html;
			$status= true;
		}
		else
		{
			$html .= $this->failedStatus;
			$errorMsg= $this->getErrorMessage($step, $step);
			$message= $html.$errorMsg;
			$status= false;
			$step= $step -1;
		}
		$html .= '<div style="width:300px; float:left;">'.JText :: _('English language file installation').'</div>';
		if($this->installLanguage('back'))
		{
			$html .= $this->successStatus;
			$autoSubmit= $this->getAutoSubmitFunction();
			//$form = $this->getInstallForm(5);
			$message= $autoSubmit.$html;
			$status= true;
		}
		else
		{
			$html .= $this->failedStatus;
			$errorMsg= $this->getErrorMessage(4, '4');
			$message= $html.$errorMsg;
			$status= false;
			$step= $step -1;
		}
		$html .= '<div style="width:300px; float:left;">'.JText :: _('System English language file installation').'</div>';
		if($this->installLanguage('backsys'))
		{
			$html .= $this->successStatus;
			$autoSubmit= $this->getAutoSubmitFunction();
			//$form = $this->getInstallForm(5);
			$message= $autoSubmit.$html;
			$status= true;
		}
		else
		{
			$html .= $this->failedStatus;
			$errorMsg= $this->getErrorMessage(4, '4');
			$message= $html.$errorMsg;
			$status= false;
			$step= $step -1;
		}
		$drawdata= new stdClass();
		$drawdata->message= $message;
		$drawdata->status= $status;
		$drawdata->step= $step;
		$drawdata->title= JText :: _('OSE BACKEND INSTALLATION');
		$drawdata->install= 1;
		return $drawdata;
	}
	function installCOMCPU($step)
	{
		$html= '';
		$html .= '<div style="width:300px; float:left;">'.JText :: _('OSE CPU BACKEND INSTALLATION').'</div>';
		$zip= $this->backendPath.'com_cpu_admin.zip';
		$destination= JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ose_cpu'.DS;
		if($this->extractArchive($zip, $destination))
		{
			$html .= $this->successStatus;
			$autoSubmit= $this->getAutoSubmitFunction();
			$message= $autoSubmit.$html;
			$status= true;
		}
		else
		{
			$html .= $this->failedStatus;
			$errorMsg= $this->getErrorMessage(2, '2');
			$message= $html.$errorMsg;
			$status= false;
			$step= $step -1;
		}
		jimport('joomla.filesystem.file');
		if (JFile::exists($this->frontendCPUPath.'extjs'.DS.'init'))
		{
			JFile::delete($this->frontendCPUPath.'extjs'.DS.'init');
		}
		$html .= '<div style="width:300px; float:left;">'.JText :: _('OSE CPU FRONTEND INSTALLATION').'</div>';
		$zip= $this->backendPath.'com_cpu_site.zip';
		$destination= JPATH_SITE.DS.'components'.DS.'com_ose_cpu'.DS;
		if($this->extractArchive($zip, $destination))
		{
			$html .= $this->successStatus;
			$autoSubmit= $this->getAutoSubmitFunction();
			$message= $autoSubmit.$html;
			$status= true;
		}
		else
		{
			$html .= $this->failedStatus;
			$errorMsg= $this->getErrorMessage(2, '2');
			$message= $html.$errorMsg;
			$status= false;
			$step= $step -1;
		}
		$zip= $this->backendPath.'cpuMSC.zip';
		$destination= JPATH_ADMINISTRATOR.DS.'components'.DS.'com_ose_cpu'.DS;
		$html .= '<div style="width:300px; float:left;">'.JText :: _('OSE CPU Extended INSTALLATION').'</div>';
		if($this->extractArchive($zip, $destination))
		{
			$html .= $this->successStatus;
			$autoSubmit= $this->getAutoSubmitFunction();
			$message= $autoSubmit.$html;
			$status= true;
		}
		else
		{
			$html .= $this->failedStatus;
			$errorMsg= $this->getErrorMessage(2, '2');
			$message= $html.$errorMsg;
			$status= false;
			$step= $step -1;
		}
		$drawdata= new stdClass();
		$drawdata->message= $message;
		$drawdata->status= $status;
		$drawdata->step= $step;
		$drawdata->title= JText :: _('OSE INSTALLING Central Processing Units');
		$drawdata->install= 1;
		return $drawdata;
	}
	function installFrontend($step)
	{
		$html= '';
		$html .= '<div style="width:300px; float:left;">'.JText :: _('OSE Frontend Installation').'</div>';
		$zip= $this->frontendPath.'site.zip';
		$destination= $this->frontendPath;
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		if (JFile::exists($this->frontendPath.'assets'.DS.'company_logo'))
		{
			JFile::delete($this->frontendPath.'assets'.DS.'company_logo');
		}
		if (JFile::exists($this->frontendPath.'assets'.DS.'msc_logo'))
		{
			JFile::delete($this->frontendPath.'assets'.DS.'msc_logo');
		}
		if (JFile::exists($this->frontendPath.'assets'.DS.'tmpl_image'))
		{
			JFile::delete($this->frontendPath.'assets'.DS.'tmpl_image');
		}

		if($this->extractArchive($zip, $destination))
		{
			$html .= $this->successStatus;
			$autoSubmit= $this->getAutoSubmitFunction();
			//$form = $this->getInstallForm(5);
			$message= $autoSubmit.$html;
			$status= true;
		}
		else
		{
			$html .= $this->failedStatus;
			$errorMsg= $this->getErrorMessage(4, '4');
			$message= $html.$errorMsg;
			$status= false;
			$step= $step -1;
		}
		// Language ;
		$html .= '<div style="width:300px; float:left;">'.JText :: _('English language file installation').'</div>';
		if($this->installLanguage('front'))
		{
			$html .= $this->successStatus;
			$autoSubmit= $this->getAutoSubmitFunction();
			//$form = $this->getInstallForm(5);
			$message= $autoSubmit.$html;
			$status= true;
		}
		else
		{
			$html .= $this->failedStatus;
			$errorMsg= $this->getErrorMessage(4, '4');
			$message= $html.$errorMsg;
			$status= false;
			$step= $step -1;
		}
		// Menu Patch ;
		$html .= '<div style="width:300px; float:left;">'.JText :: _('Menu Patch file installation').'</div>';
		if($this->installMenuPatch())
		{
			$html .= $this->successStatus;
			$autoSubmit= $this->getAutoSubmitFunction();
			//$form = $this->getInstallForm(5);
			$message= $autoSubmit.$html;
			$status= true;
		}
		else
		{
			$html .= $this->failedStatus;
			$errorMsg= $this->getErrorMessage(4, '4');
			$message= $html.$errorMsg;
			$status= false;
			$step= $step -1;
		}
		// Module Patch ;
		$html .= '<div style="width:300px; float:left;">'.JText :: _('Module Patch file installation').'</div>';
		if($this->installModulePatch())
		{
			$html .= $this->successStatus;
			$autoSubmit= $this->getAutoSubmitFunction();
			//$form = $this->getInstallForm(5);
			$message= $autoSubmit.$html;
			$status= true;
		}
		else
		{
			$html .= $this->failedStatus;
			$errorMsg= $this->getErrorMessage(4, '4');
			$message= $html.$errorMsg;
			$status= false;
			$step= $step -1;
		}
		$drawdata= new stdClass();
		$drawdata->message= $message;
		$drawdata->status= $status;
		$drawdata->step= $step;
		$drawdata->title= JText :: _('INSTALLING FRONTEND SYSTEM');
		$drawdata->install= 1;
		return $drawdata;
	}
	function installPlugin($step)
	{
		$html= '';
		jimport('joomla.filesystem.file');
		$html .= '<div style="width:300px; float:left;">'.JText :: _('EXTRACTING PLUGIN').'</div>';
		$pluginFolder= $this->backendPath.'osePlugins';
		if(!JFolder :: exists($pluginFolder))
		{
			JFolder :: create($pluginFolder);
		}
		$zip= $this->backendPath.'ose_plugins.zip';
		$destination= $pluginFolder;
		if($this->extractArchive($zip, $destination))
		{
			$html .= $this->successStatus;
			$installer= JInstaller :: getInstance();
			$installer->setOverwrite(true);
			$installResult= true;
			$destination= array();
			$destination[]= 'plugin_oseAffiliate';
			$destination[]= 'plugin_osecontent';
			$destination[]= 'plugin_osemscaec';
			$destination[]= 'plugin_oserouter';
			$destination[]= 'plugin_oseuser';
			$destination[]= 'plugin_oseMscGoogleAnalytics';
			$destination[]= 'plugin_osefacebook';
			foreach($destination as $dest)
			{
				$installResult= $installer->install($pluginFolder.DS.$dest.DS);
				$html .= '<div style="width:300px; float:left;">'.JText :: _('INSTALLING PLUGIN ')." ".$dest.'</div>';
				if($installResult == false)
				{
					$html .= $this->failedStatus;
					$errorMsg= $this->getErrorMessage($step, $step);
					$message= $html.$errorMsg;
					$status= false;
					$step= $step -1;
				}
				else
				{
					if ($dest=='plugin_oseuser')
					{
						$db= JFactory :: getDBO();
						if (JOOMLA16==true)
						{
							$query = "UPDATE `#__extensions` SET `ordering` = '999' WHERE `element` ='oseuser';";
						}
						else
						{
							$query = "UPDATE `#__plugins` SET `ordering` =  '999' WHERE `element` ='oseuser';";
						}
						$db->setQuery($query);
						$db->query();
					}
					$html .= $this->successStatus;
					$autoSubmit= $this->getAutoSubmitFunction();
					//$form = $this->getInstallForm(5);
					$message= $autoSubmit.$html;
					$status= true;
				}
			}
		}
		else
		{
			$html .= $this->failedStatus;
			$errorMsg= $this->getErrorMessage($step, $step);
			$message= $html.$errorMsg;
			$status= false;
			$step= $step -1;
		}
		$drawdata= new stdClass();
		$drawdata->message= $message;
		$drawdata->status= true;
		$drawdata->step= $step;
		$drawdata->title= JText :: _('INSTALLING PLUGINS');
		$drawdata->install= 1;
		return $drawdata;
	}
	function prepareDatabase($step)
	{
		$html= '';
		$html .= '<div style="width:300px; float:left;">'.JText :: _('Creating Database').'</div>';
		$queryResult= $this->installSQL();
		if($queryResult == true)
		{
			$html .= $this->successStatus;
			$autoSubmit= $this->getAutoSubmitFunction();
			//$form = $this->getInstallForm(7);
			$message= $autoSubmit.$html;
			$status= true;
		}
		else
		{
			$html .= $this->failedStatus;
			$errorMsg= $this->getErrorMessage(6, $queryResult);
			$message= $html.$errorMsg;
			$status= false;
			$step= $step -1;
		}
		$drawdata= new stdClass();
		$drawdata->message= $message;
		$drawdata->status= $status;
		$drawdata->step= $step;
		$drawdata->title= JText :: _('PREPARING DATABASE');
		$drawdata->install= 1;
		return $drawdata;
	}
	function UpdateDatabase($step)
	{
		$html= '';
		$html .= '<div style="width:300px; float:left;">'.JText :: _('Fix Database Integrity').'</div>';
		$queryResult= $this->fixIntegrity();
		if($queryResult == true)
		{
			$html .= $this->successStatus;
			$autoSubmit= $this->getAutoSubmitFunction();
			//$form = $this->getInstallForm(7);
			$message= $autoSubmit.$html;
			$status= true;
		}
		else
		{
			$html .= $this->failedStatus;
			$errorMsg= $this->getErrorMessage(7, $queryResult);
			$message= $html.$errorMsg;
			$status= false;
			$step= $step -1;
		}
		$drawdata= new stdClass();
		$drawdata->message= $message;
		$drawdata->status= $status;
		$drawdata->step= $step;
		$drawdata->title= JText :: _('Fixing Database Integrity');
		$drawdata->install= 1;
		return $drawdata;
	}
	function extractArchive($source, $destination)
	{
		// Cleanup path
		$destination= JPath :: clean($destination);
		$source= JPath :: clean($source);
		$result= JArchive :: extract($source, $destination);
		if($result === false)
		{
			return false;
		}
		else
		{
			return true;
			//if (JFile::delete($source))
			//{
			//   return true;
			//}
		}
	}
	function fixIntegrity()
	{
		$db= JFactory :: getDBO();
		// ACL Info;
		$fields= OsemscHelper::getDBFields('#__osemsc_acl');
		if(isset($fields['#__osemsc_acl']['r']))
		{
			$query= "ALTER TABLE `#__osemsc_acl` DROP INDEX `name` , ADD UNIQUE `title` ( `title` , `alias` )";
			$db->setQuery($query);
			$db->query();
			
			$query= "ALTER TABLE `#__osemsc_acl` DROP `r`";
			$db->setQuery($query);
			if(!$db->query())
			{
				$result['text'][]= "Error Migrating MSC Core Table";
				return false;
			}
		}
		if(isset($fields['#__osemsc_acl']['c']))
		{
			$query= "ALTER TABLE `#__osemsc_acl` DROP `c`";
			$db->setQuery($query);
			if(!$db->query())
			{
				$result['text'][]= "Error Migrating MSC Core Table";
				return false;
			}
		}
		if(isset($fields['#__osemsc_acl']['u']))
		{
			$query= "ALTER TABLE `#__osemsc_acl` DROP `u`";
			$db->setQuery($query);
			if(!$db->query())
			{
				$result['text'][]= "Error Migrating MSC Core Table";
				return false;
			}
		}
		if(isset($fields['#__osemsc_acl']['restricted']))
		{
			$query= "ALTER TABLE `#__osemsc_acl` DROP `restricted`";
			$db->setQuery($query);
			if(!$db->query())
			{
				$result['text'][]= "Error Migrating MSC Core Table";
				return false;
			}
		}
		if(isset($fields['#__osemsc_acl']['name']))
		{
			$query= "ALTER TABLE `#__osemsc_acl` CHANGE  `name`  `title` varchar(100) NOT NULL;";
			$db->setQuery($query);
			if(!$db->query())
			{
				$result['text'][]= "Error Migrating MSC Core Table";
				return false;
			}
		}
		if(!isset($fields['#__osemsc_acl']['parent_id']))
		{
			$query= "ALTER TABLE `#__osemsc_acl` ADD  `parent_id` int(11) NOT NULL default '0';";
			$db->setQuery($query);
			if(!$db->query())
			{
				$result['text'][]= "Error Migrating MSC Core Table";
				return false;
			}
		}
		if(!isset($fields['#__osemsc_acl']['showtitle']))
		{
			$query= "ALTER TABLE `#__osemsc_acl` ADD  `showtitle` tinyint(1) NOT NULL default '0' COMMENT '1 for Only show the title in memberlist. Use it in children membership.';";
			$db->setQuery($query);
			if(!$db->query())
			{
				$result['text'][]= "Error Migrating MSC Core Table";
				return false;
			}
		}
		if(!isset($fields['#__osemsc_acl']['lft']))
		{
			$query= "ALTER TABLE `#__osemsc_acl` ADD `lft` int(11) NOT NULL default '1';";
			$db->setQuery($query);
			if(!$db->query())
			{
				$result['text'][]= "Error Migrating MSC Core Table";
				return false;
			}
		}
		if(!isset($fields['#__osemsc_acl']['rgt']))
		{
			$query= "ALTER TABLE `#__osemsc_acl` ADD `rgt` int(11) NOT NULL default '2';";
			$db->setQuery($query);
			if(!$db->query())
			{
				$result['text'][]= "Error Migrating MSC Core Table";
				return false;
			}
		}
		if(!isset($fields['#__osemsc_acl']['leaf']))
		{
			$query= "ALTER TABLE `#__osemsc_acl` ADD  `leaf` int(1) NOT NULL default '1';";
			$db->setQuery($query);
			if(!$db->query())
			{
				$result['text'][]= "Error Migrating MSC Core Table";
				return false;
			}
		}
		if(!isset($fields['#__osemsc_acl']['level']))
		{
			$query= "ALTER TABLE `#__osemsc_acl` ADD  `level` int(11) NOT NULL default '0';";
			$db->setQuery($query);
			if(!$db->query())
			{
				$result['text'][]= "Error Migrating MSC Core Table";
				return false;
			}
		}
		if(!isset($fields['#__osemsc_acl']['published']))
		{
			$query= "ALTER TABLE `#__osemsc_acl` ADD  `published` tinyint(1) NOT NULL default '1';";
			$db->setQuery($query);
			if(!$db->query())
			{
				$result['text'][]= "Error Migrating MSC Core Table";
				return false;
			}
		}
		if(!isset($fields['#__osemsc_acl']['params']))
		{
			$query= "ALTER TABLE `#__osemsc_acl` ADD  `params` text;";
			$db->setQuery($query);
			if(!$db->query())
			{
				$result['text'][]= "Error Migrating MSC Core Table";
				return false;
			}
		}
		if(isset($fields['#__osemsc_acl']['hp_id']))
		{
			$query= "ALTER TABLE `#__osemsc_acl` CHANGE `hp_id` `menuid` int(11) default NULL;";
			$db->setQuery($query);
			if(!$db->query())
			{
				$result['text'][]= "Error Migrating MSC Core Table";
				return false;
			}
		}
		if(!isset($fields['#__osemsc_acl']['alias']))
		{
			$query= "ALTER TABLE `#__osemsc_acl` ADD  `alias` varchar(100) NOT NULL;";
			$db->setQuery($query);
			if(!$db->query())
			{
				$result['text'][]= "Error Migrating MSC Core Table";
				return false;
			}
		}
		if(!isset($fields['#__osemsc_acl']['image']))
		{
			$query= "ALTER TABLE `#__osemsc_acl` ADD  `image` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
			$db->setQuery($query);
			if(!$db->query())
			{
				$result['text'][]= "Error Migrating MSC Core Table";
				return false;
			}
		}
		if(isset($fields['#__osemsc_acl']['ordering']))
		{
			$query= "ALTER TABLE `#__osemsc_acl` CHANGE  `ordering`  `ordering` SMALLINT( 3 ) NOT NULL DEFAULT '1';";
			$db->setQuery($query);
			if(!$db->query())
			{
				$result['text'][]= "Error Migrating MSC Core Table";
				return false;
			}
		}
		// Configuration Table;
		$fields= OsemscHelper::getDBFields('#__osemsc_configuration');
		if(empty($fields) || !isset($fields['#__osemsc_configuration']['type']))
		{
			if(!empty($fields))
			{
				$query= "DROP TABLE `#__osemsc_configuration`";
				$db->setQuery($query);
				if(!$db->query())
				{
					$result['text'][]= "Error Migrating MSC Core Table";
					return false;
				}
			}
			$query= "CREATE TABLE IF NOT EXISTS `#__osemsc_configuration` (
															  `id` int(11) NOT NULL auto_increment,
															  `key` text,
															  `value` text,
															  `type` varchar(20) NOT NULL,
															  `default` text NOT NULL,
															  PRIMARY KEY  (`id`)
															) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
			$db->setQuery($query);
			if(!$db->query())
			{
				$result['text'][]= "Error Migrating MSC Core Table";
				return false;
			}
			$query= "SELECT count(*) FROM `#__osemsc_configuration`";
			$db->setQuery($query);
			$result= $db->loadResult();
			if(empty($result))
			{
				$query= "INSERT INTO `#__osemsc_configuration` (`id`, `key`, `value`, `type`, `default`) VALUES
																		(18, 'is_member_mode_customized', '0', 'global', ''),
																		(19, 'customized_member_mode', '', 'global', ''),
																		(20, 'manual_renew_mode', 'renew', 'global', ''),
																		(21, 'manual_to_automatic_mode', 'extend', 'global', ''),
																		(22, 'gmap_key', '', 'global', ''),
																		(23, 'payment_system', '0', 'payment', ''),
																		(24, 'cc_testmode', '1', 'payment', ''),
																		(25, 'auto_login', '1', 'member', ''),
																		(26, 'admin_group', '25', 'email', ''),
																		(27, 'default_reg_email', '15', 'email', ''),
																		(28, 'member_mode', '0', 'global', ''),
																		(29, 'sendReg2Admin', '1', 'email', ''),
																		(30, 'sendWel2Admin', '1', 'email', ''),
																		(31, 'sendCancel2Admin', '1', 'email', ''),
																		(32, 'sendExp2Admin', '1', 'email', ''),
																		(33, 'sendReceipt2Admin', '1', 'email', ''),
																		(34, 'register_form', 'onestep', 'register', ''),
																		(35, 'onestep_payment_mode', 'm', 'register', ''),
																		(36, 'auto_login', '1', 'register', ''),
																		(37, 'paypal_email', '', 'payment', ''),
																		(38, 'google_checkout_id', '', 'payment', ''),
																		(39, 'google_checkout_key', '', 'payment', ''),
																		(40, '2checkoutVendorId', '', 'payment', ''),
																		(41, '2checkoutSecret', '', 'payment', ''),
																		(42, 'an_merchant_email', '', 'payment', ''),
																		(43, 'an_loginid', '', 'payment', ''),
																		(44, 'an_transkey', '', 'payment', ''),
																		(45, 'poffline_art_id', '', 'payment', ''),
																		(46, 'enable_paypal', '0', 'payment', ''),
																		(47, 'paypal_testmode', '1', 'payment', ''),
																		(48, 'enable_gco', '0', 'payment', ''),
																		(49, 'gco_testmode', '0', 'payment', ''),
																		(50, 'enable_2co', '0', 'payment', ''),
																		(51, '2co_testmode', '0', 'payment', ''),
																		(52, 'an_email_merchant', '1', 'payment', ''),
																		(53, 'an_email_customer', '1', 'payment', ''),
																		(54, 'enable_authorize', '0', 'payment', ''),
																		(55, 'enable_cc', '0', 'payment', ''),
																		(56, 'enable_poffline', '0', 'payment', ''),
																		(57, 'msc_extend', '0', 'global', ''),
																		(58, 'default_receipt', '', 'email', ''),
																		(59, 'authorize_testmode', '1', 'payment', ''),
																		(60, 'cc_methods', 'authorize', 'payment', ''),
																		(61, 'paypal_api_username', '', 'payment', ''),
																		(62, 'paypal_api_passwd', '', 'payment', ''),
																		(63, 'paypal_api_signature', '', 'payment', ''),
																		(64, 'payment_mode', 'b', 'global', ''),
																		(65, 'primary_currency', '', 'currency', ''),
																		(66, 'paypal_mode', 'Express Checkout', 'payment', ''),
																		(67, 'is_msc_mode_customized', '0', 'global', ''),
																		(68, 'customized_msc_mode', '', 'global', ''),
																		(69, 'enable_eway', '1', 'payment', ''),
																		(70, 'eway_testmode', '1', 'payment', ''),
																		(71, 'eWayCustomerID', '87654321', 'payment', ''),
																		(72, 'eWayUsername', 'TestAccount', 'payment', ''),
																		(73, 'eWayPassword', 'dfafdasf', 'payment', ''),
																		(74, 'frontend_style', 'msc5', 'global', ''),
																		(75, 'backend_style', 'msc5', 'global', '');";
				$db->setQuery($query);
				if(!$db->query())
				{
					$result['text'][]= "Error Migrating MSC Core Table";
					return false;
				}
			}
		}
		// Member Table;
		$fields= OsemscHelper::getDBFields('#__osemsc_member');
		if(!isset($fields['#__osemsc_member']['status']))
		{
			$query= "ALTER TABLE `#__osemsc_member` ADD  `status` int(1) NOT NULL default '1' COMMENT '0 for end, 1 for active';";
			$db->setQuery($query);
			if(!$db->query())
			{
				$result['text'][]= "Error Migrating MSC Core Table";
				return false;
			}
		}
		if(!isset($fields['#__osemsc_member']['notified2']))
		{
			$query= "ALTER TABLE `#__osemsc_member` ADD  `notified2` tinyint(1) default NULL;";
			$db->setQuery($query);
			if(!$db->query())
			{
				$result['text'][]= "Error Migrating MSC Core Table";
				return false;
			}
		}
		if(!isset($fields['#__osemsc_member']['notified3']))
		{
			$query= "ALTER TABLE `#__osemsc_member` ADD  `notified3` tinyint(1) default NULL;";
			$db->setQuery($query);
			if(!$db->query())
			{
				$result['text'][]= "Error Migrating MSC Core Table";
				return false;
			}
		}
		if(!isset($fields['#__osemsc_member']['invitation']))
		{
			$query= "ALTER TABLE `#__osemsc_member` ADD  `invitation` tinyint(1) default NULL;";
			$db->setQuery($query);
			if(!$db->query())
			{
				$result['text'][]= "Error Migrating MSC Core Table";
				return false;
			}
		}
		if(!isset($fields['#__osemsc_member']['eternal']))
		{
			$query= "ALTER TABLE `#__osemsc_member` ADD  `eternal` int(1) NOT NULL default '0' COMMENT '1 for true, eternal membership';";
			$db->setQuery($query);
			if(!$db->query())
			{
				$result['text'][]= "Error Migrating MSC Core Table";
				return false;
			}
		}
		if(!isset($fields['#__osemsc_member']['params']))
		{
			$query= "ALTER TABLE `#__osemsc_member` ADD  `params` text;";
			$db->setQuery($query);
			if(!$db->query())
			{
				$result['text'][]= "Error Migrating MSC Core Table";
				return false;
			}
			$query= "ALTER TABLE `#__osemsc_member` ADD KEY `msc_id_2` (`msc_id`);";
			$db->setQuery($query);
			if(!$db->query())
			{
				$result['text'][]= "Error Migrating MSC Core Table";
				return false;
			}
		}
		// Content Table;
		$fields= OsemscHelper::getDBFields('#__osemsc_content');
		if(!isset($fields['#__osemsc_content']['entry_id']))
		{
			$query= "ALTER TABLE `#__osemsc_content` ADD  `entry_id` int(11) NOT NULL;";
			$db->setQuery($query);
			if(!$db->query())
			{
				$result['text'][]= "Error Migrating MSC Core Table";
				return false;
			}
		}
		if(!isset($fields['#__osemsc_content']['entry_type']))
		{
			$query= "ALTER TABLE `#__osemsc_content` ADD  `entry_type` varchar(20) NOT NULL default 'msc';";
			$db->setQuery($query);
			if(!$db->query())
			{
				$result['text'][]= "Error Migrating MSC Core Table";
				return false;
			}
		}
		if(!isset($fields['#__osemsc_content']['status']))
		{
			$query= "ALTER TABLE `#__osemsc_content` ADD `status` int(3) NOT NULL default '0';";
			$db->setQuery($query);
			if(!$db->query())
			{
				$result['text'][]= "Error Migrating MSC Core Table";
				return false;
			}
		}
		// Billing Info;
		$fields= OsemscHelper::getDBFields('#__osemsc_billinginfo');
		if(isset($fields['#__osemsc_billinginfo']['street1']))
		{
			if(!isset($fields['#__osemsc_billinginfo']['addr1']))
			{
			 	$query= "ALTER TABLE `#__osemsc_billinginfo` CHANGE  `street1`  `addr1` text;";
				$db->setQuery($query);
				if(!$db->query())
				{
					$result['text'][]= "Error Migrating MSC Core Table";
					return false;
				}
			}

		}
		if(isset($fields['#__osemsc_billinginfo']['street2']))
		{
			if(!isset($fields['#__osemsc_billinginfo']['addr2']))
			{
				$query= "ALTER TABLE `#__osemsc_billinginfo` CHANGE  `street2`  `addr2` text;";
				$db->setQuery($query);
				if(!$db->query())
				{
					$result['text'][]= "Error Migrating MSC Core Table";
					return false;
				}
			}
		}
		if(isset($fields['#__osemsc_billinginfo']['state_id']))
		{
			if(!isset($fields['#__osemsc_billinginfo']['state']))
			{
				$query= "ALTER TABLE `#__osemsc_billinginfo` CHANGE  `state_id`  `state` varchar(100);";
				$db->setQuery($query);
				if(!$db->query())
				{
					$result['text'][]= "Error Migrating MSC Core Table";
					return false;
				}
			}
		}
		if(isset($fields['#__osemsc_billinginfo']['country_id']))
		{
			if(!isset($fields['#__osemsc_billinginfo']['country']))
			{
				$query= "ALTER TABLE `#__osemsc_billinginfo` CHANGE  `country_id`  `country` varchar(100);";
				$db->setQuery($query);
				if(!$db->query())
				{
					$result['text'][]= "Error Migrating MSC Core Table";
					return false;
				}
			}
		}

		$query= "ALTER TABLE `#__osemsc_tax` CHANGE `rate` `rate` FLOAT( 10, 2 ) NOT NULL DEFAULT '0.00';";
		$db->setQuery($query);
		if(!$db->query())
		{
			$result['text'][]= "Error Updating MSC Core Table";
			return false;
		}
		$query= "ALTER TABLE `#__osemsc_coupon` CHANGE `discount` `discount` double NOT NULL DEFAULT '0';";
		$db->setQuery($query);
		if(!$db->query())
		{
			$result['text'][]= "Error Updating MSC Core Table";
			return false;
		}

		$fields= OsemscHelper::getDBFields('#__osemsc_order');
		if(!isset($fields['#__osemsc_order']['transactions']))
		{
			$query= "ALTER TABLE `#__osemsc_order` ADD `transactions` text NULL;";
			$db->setQuery($query);
			if(!$db->query())
			{
				$result['text'][]= "Error Updating MSC Core Table";
				return false;
			}
		}

		$query= "SELECT count(*) FROM `#__osemsc_configuration`";
		$db->setQuery($query);
		$result= $db->loadResult();

		if ($result==0)
		{
			$query = "INSERT INTO `#__osemsc_email` (`id`, `msc_id`, `subject`, `body`, `type`, `params`) VALUES" .
					"(NULL, 0, 'Sample Sales Receipt', '<div style=\"margin-left: 50px;\">\n<div style=\"margin-top: 20px; margin-left: 10px; display: block;\">\n<table width=\"100%\">\n<tbody>\n<tr>\n<td width=\"80px\">YOUR LOGO<br /></td>\n<td style=\"color: #333333;\">&nbsp;</td>\n</tr>\n</tbody>\n</table>\n</div>\n<div id=\"invoice-to\" style=\"margin-left: 10px; margin-top: 10px;\">\n<div style=\"margin-left: 1px; margin-bottom: 0px; margin-top: 0px; font-family: Tahoma,Geneva,Kalimati,sans-serif; color: #238db4; vertical-align: top; font-weight: bold; text-align: left;\">Thank you for your order!</div>\n<br />\n<div style=\"background-color: #e8f1fa; padding: 5px;\"><span style=\"font-weight: bold;\">Invoice To:</span></div>\n<div style=\"padding: 5px;\">[user.firstname] [user.lastname]<br /> [user.company]<br /> [user.email]<br /> [user.address1]<br /> [user.address2]<br /> [user.city] [user.state]<br /> [user.country] [user.postcode]<br /> [user.telephone]</div>\n</div>\n<div style=\"margin-left: 10px; margin-top: 10px;\">\n<div style=\"background-color: #e8f1fa; padding: 5px;\"><span style=\"font-weight: bold;\">Order Information</span></div>\n<div style=\"padding: 5px;\">Order Number: [order.order_number] <br /> Order Date: [order.date] <br /> Order Status: [order.order_status]</div>\n<div style=\"background-color: #e8f1fa; padding: 5px; margin-top: 10px;\"><span style=\"font-weight: bold;\">Membership Detail</span></div>\n<div>[order.itemlist]</div>\n<div>\n<p style=\"text-align: justify;\"><strong><span style=\"color: #cc6600;\">Subscriptions - About Recurring Subscriptions<br /></span></strong></p>\n</div>\n</div>\n</div>', 'receipt', '{\"user.username\":\"user.username\",\"user.name\":\"user.jname\",\"user.email\":\"user.email\",\"user.user_status\":\"user.block\",\"user.firstname\":\"user.firstname\",\"user.lastname\":\"user.lastname\",\"user.primary_contact\":\"user.primary_contact\",\"user.company\":\"user.company\",\"user.address1\":\"user.addr1\",\"user.address2\":\"user.addr2\",\"user.city\":\"user.city\",\"user.state\":\"user.state\",\"user.country\":\"user.country\",\"user.postcode\":\"user.postcode\",\"user.telephone\":\"user.telephone\",\"order.order_id\":\"order.order_id\",\"order.order_number\":\"order.order_number\",\"order.order_status\":\"order.order_status\",\"order.subtotal\":\"order.subtotal\",\"order.total\":\"order.total\",\"order.gross_tax\":\"order.gross_tax\",\"order.discount\":\"order.discount\",\"order.itemlist\":\"order.itemlist\",\"order.payment_method\":\"order.payment_method\",\"order.date\":\"order.create_date\",\"order.payment_mode\":\"order.payment_mode\"}')," .
					"(NULL, 0, 'Sample Terms of Service', '<h4>Sample Terms of Service</h4>\n<h4>About Recurring Subscriptions</h4>\n<p>For  Paypal, you can simply cancel the recurring payment in your  Paypal  account, or please contact us by  email before the expiration of your  subscription term and we''ll do it for you.</p>\n<p style=\"text-align: justify;\">To  cancel your  recurring Visa or Mastercard subscription payment, please  contact us by  email before the expiration of your subscription term and  we will remove  you from any future recurring billing.</p>\n<p style=\"text-align: justify;\">Please notify us at least 48 hours if you wish to cancel your  Paypal or  Credit Card recurring  billing before your next renewal date. Please  note there are no refunds for recurring billing once the charge has been  processed.</p>', 'term', '[]')," .
					"(NULL, 0, 'Sample Cancellation Confirmation', '<p>Dear [user.firstname]</p>\n<p>Thank you for your email indicating you would like to cancel your recurring billing. You will be removed from any future recurring billing and your subscription will end at the end of your current term.</p>\n<p>I hope you have enjoyed being a member of our website and I hope you will re-join us sometime in the near future.</p>\n<p>If you have any questions or concerns, please, I invite you to write me back personally.</p>\n<p>Best Wishes<strong></strong></p>\n<p><strong>Management Team<br /></strong></p>', 'cancel_email', '{\"user.username\":\"user.username\",\"user.email\":\"user.email\",\"user.firstname\":\"user.firstname\",\"user.lastname\":\"user.lastname\",\"user.primary_contact\":\"user.primary_contact\",\"member.start_date\":\"member.start_date\",\"member.expired_date\":\"member.expired_date\"}')," .
					"(NULL, 0, 'Sample Your Username + Password Details', '<p>Dear [user.firstname] [user.lastname]</p>\n<p>Here are your login credentials:</p>\n<p>LOGIN USERNAME: [user.username]</p>\n<p>LOGIN PASSWORD: [user.password]</p>\n<p>Please keep these details for future reference.</p>\n<p>Best Wishes<strong></strong></p>\n<p><strong>Management Team</strong></p>', 'reg_email', '{\"user.username\":\"user.username\",\"user.name\":\"user.name\",\"user.password\":\"user.password\",\"user.email\":\"user.email\",\"user.firstname\":\"user.firstname\",\"user.lastname\":\"user.lastname\",\"user.primary_contact\":\"user.primary_contact\",\"user.user_status\":\"user.block\"}')," .
					"(NULL, 0, 'Sample Membership Expiration Alert', '<p>Dear [user.firstname] [user.lastname]</p>\n<p>Your membership is coming close to it''s expiration date [member.expired_date].</p>\n<p>Best Wishes<strong></strong></p>\n<p><strong>Management Team</strong></p>', 'notification', '{\"user.username\":\"user.username\",\"user.email\":\"user.email\",\"user.firstname\":\"user.firstname\",\"user.lastname\":\"user.lastname\",\"user.primary_contact\":\"user.block\",\"member.start_date\":\"member.start_date\",\"member.expired_date\":\"member.expired_date\"}')," .
					"(NULL, 0, 'Sample Membership Expiration', '<p>Dear [user.firstname] [user.lastname]</p>\n<p>Your membership has expired. Thank you for having been a member.</p>\n<p>Should you like to renew please go to our website to sign up.</p>\n<p>If you have any questions or concerns, please do not hesitate to personally contact me.</p>\n<p>Best Wishes<strong></strong></p>\n<p><strong>Management Team</strong></p>', 'exp_email', '{\"user.username\":\"user.username\",\"user.email\":\"user.email\",\"user.firstname\":\"user.firstname\",\"user.lastname\":\"user.lastname\",\"user.primary_contact\":\"user.block\",\"member.start_date\":\"member.start_date\",\"member.expired_date\":\"member.expired_date\"}')," .
					"(NULL, 0, 'Sample Successful Sign Up email', '<div class=\"item\">\n<div class=\"pos-content\">\n<div class=\"element element-textarea  first last\">Hi [user.firstname]</div>\n<div class=\"element element-textarea  first last\"></div>\n<div class=\"element element-textarea  first last\">Your membership has been activated successfully.</div>\n<div class=\"element element-textarea  first last\"></div>\n<div class=\"element element-textarea  first last\">Best wishes</div>\n<div class=\"element element-textarea  first last\">Management Team</div>\n<div class=\"element element-textarea  first last\"></div>\n<div class=\"element element-textarea  first last\"></div>\n</div>\n</div>', 'wel_email', '{\"user.username\":\"user.username\",\"user.name\":\"user.jname\",\"user.email\":\"user.email\",\"user.user_status\":\"user.block\",\"user.firstname\":\"user.firstname\",\"user.lastname\":\"user.lastname\",\"user.primary_contact\":\"user.primary_contact\",\"user.company\":\"user.company\",\"user.address1\":\"user.addr1\",\"user.address2\":\"user.addr2\",\"user.city\":\"user.city\",\"user.state\":\"user.state\",\"user.country\":\"user.country\",\"user.postcode\":\"user.postcode\",\"user.telephone\":\"user.telephone\",\"member.start_date\":\"member.start_date\",\"member.expired_date\":\"member.real_expired_date\",\"member.period\":\"member.period\",\"member.msc_title\":\"member.msc_title\",\"member.msc_des\":\"member.msc_des\",\"order.order_id\":\"order.order_id\",\"order.order_number\":\"order.order_number\",\"order.order_status\":\"order.order_status\",\"order.subtotal\":\"order.subtotal\",\"order.total\":\"order.total\",\"order.discount\":\"order.discount\",\"order.table\":\"order.table\",\"order.payment_method\":\"order.payment_method\",\"order.date\":\"order.create_date\"}')," .
					"(NULL, 0, 'Sample Sales Receipt 2', '<div class=\"osereceipt\">\n<div class=\"receipt-content\">\n<table width=\"100%\">\n<tbody>\n<tr>\n<td width=\"70%\">\n<p><span class=\"invoice-header\">INVOICE</span><br /><br /> <span class=\"date\">Date: [order.date]</span><br /><br /><span class=\"invoice-number\">Invoice # 2011-[order.order_id]</span></p>\n<p class=\"billing-info\"><strong><span class=\"billing-header\">Billing Address</span></strong><br /> [user.company]<br /> [user.address1]<br /> [user.city], [user.state], [user.postcode]<br /> [user.firstname] [user.lastname]</p>\n<p class=\"customer-ref\">Customer Reference Number: <br /> [order.order_number]</p>\n</td>\n<td style=\"color: #666666;\">&nbsp;YOUR LOGO HERE</td>\n</tr>\n</tbody>\n</table>\n<br /> \n<table class=\"receipt-list\" width=\"100%\">\n<tbody>\n<tr class=\"rows\">\n<td class=\"header\" height=\"25px\" valign=\"middle\">Subscription Detail</td>\n</tr>\n<tr class=\"rows\">\n<td class=\"items\" height=\"70px\" valign=\"middle\">[order.itemlist]</td>\n</tr>\n<tr class=\"rows\" align=\"right\">\n<td class=\"subtotal\" height=\"25px\" valign=\"middle\">SUBTOTAL EUR [order.subtotal]</td>\n</tr>\n<tr class=\"rows\" align=\"right\">\n<td class=\"subtotal\" height=\"25px\" valign=\"middle\">SALES TAX EUR [order.gross_tax]</td>\n</tr>\n<tr class=\"rows\" align=\"right\">\n<td class=\"subtotal\" height=\"25px\" valign=\"middle\">TOTAL EUR [order.total]</td>\n</tr>\n</tbody>\n</table>\n<br /><br />\n<div style=\"text-align: center;\"><span class=\"thank-you\">Thank you for your business!</span></div>\n<br /><br />\n<div style=\"text-align: center;\"><span class=\"slogan\">YOUR SLOGAN HERE</span></div>\n</div>\n</div>', 'receipt', '{\"user.username\":\"user.username\",\"user.name\":\"user.jname\",\"user.email\":\"user.email\",\"user.user_status\":\"user.block\",\"user.firstname\":\"user.firstname\",\"user.lastname\":\"user.lastname\",\"user.primary_contact\":\"user.primary_contact\",\"user.company\":\"user.company\",\"user.address1\":\"user.addr1\",\"user.address2\":\"user.addr2\",\"user.city\":\"user.city\",\"user.state\":\"user.state\",\"user.country\":\"user.country\",\"user.postcode\":\"user.postcode\",\"user.telephone\":\"user.telephone\",\"order.order_id\":\"order.order_id\",\"order.order_number\":\"order.order_number\",\"order.order_status\":\"order.order_status\",\"order.vat_number\":\"order.vat_number\",\"order.subtotal\":\"order.subtotal\",\"order.total\":\"order.total\",\"order.gross_tax\":\"order.gross_tax\",\"order.discount\":\"order.discount\",\"order.itemlist\":\"order.itemlist\",\"order.payment_method\":\"order.payment_method\",\"order.date\":\"order.create_date\",\"order.payment_mode\":\"order.payment_mode\"}'),".
					"(NULL, 0, 'Sample Membership Cancellation Email', '<div>\n<div>Dear [user.name],</div>\n<div></div>\n<div>Your membership has been cancelled.</div>\n</div>\n<div>\n<div>Membership associated order id: [order.order_id]</div>\n</div>\n<div>\n<div>Membership associated order number: [order.order_number]</div>\n</div>\n<div>\n<div>Membership associated order status:[order.order_status]</div>\n</div>\n<p>Best regards</p>\n<p>Management Team</p>', 'cancelorder_email', '{\"user.username\":\"user.username\",\"user.name\":\"user.jname\",\"user.email\":\"user.email\",\"user.user_status\":\"user.block\",\"user.firstname\":\"user.firstname\",\"user.lastname\":\"user.lastname\",\"user.primary_contact\":\"user.primary_contact\",\"user.company\":\"user.company\",\"user.address1\":\"user.addr1\",\"user.address2\":\"user.addr2\",\"user.city\":\"user.city\",\"user.state\":\"user.state\",\"user.country\":\"user.country\",\"user.postcode\":\"user.postcode\",\"user.telephone\":\"user.telephone\",\"order.order_id\":\"order.order_id\",\"order.order_number\":\"order.order_number\",\"order.order_status\":\"order.order_status\",\"order.vat_number\":\"order.vat_number\",\"order.subtotal\":\"order.subtotal\",\"order.total\":\"order.total\",\"order.gross_tax\":\"order.gross_tax\",\"order.discount\":\"order.discount\",\"order.itemlist\":\"order.itemlist\",\"order.payment_method\":\"order.payment_method\",\"order.date\":\"order.create_date\",\"order.payment_mode\":\"order.payment_mode\"}');";
			$db->setQuery($query);
			if ($db->query())
			{
				return true;
			}
			else
			{
				return false;
			}
		}

		//Custom Fields
		$fields= OsemscHelper::getDBFields('#__osemsc_fields');
		if(!isset($fields['#__osemsc_fields']['note']))
		{
			$query= "ALTER TABLE `#__osemsc_fields` ADD  `note` varchar(255) NOT NULL DEFAULT '';";
			$db->setQuery($query);
			if(!$db->query())
			{
				$result['text'][]= "Error Migrating MSC Core Table";
				return false;
			}
		}
		/*
		require_once(JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'init.php');
		$tree= oseRegistry :: call('msc')->getInstance('Tree');
		$tree->reorder(0);
		$tree->rebuildTree();
		*/
		return true;
	}
	function installSQL()
	{
		//-- common images
		$img_OK= '<img src="images/publish_g.png" />';
		$img_WARN= '<img src="images/publish_y.png" />';
		$img_ERROR= '<img src="images/publish_r.png" />';
		$BR= '<br />';
		//--install...
		$db= JFactory :: getDBO();
		$query= "CREATE TABLE IF NOT EXISTS `#__osemsc_acl` (
							  `id` int(11) NOT NULL auto_increment,
							  `parent_id` int(11) NOT NULL default '0',
							  `title` varchar(100) NOT NULL,
							  `alias` varchar(50) NOT NULL,
							  `description` text,
							  `showtitle` tinyint(1) NOT NULL default '0' COMMENT '1 for Only show the title in memberlist. Use it in children membership.',
							  `ordering` smallint(3) NOT NULL default '1',
							  `lft` int(11) NOT NULL default '1',
							  `rgt` int(11) NOT NULL default '2',
							  `leaf` int(1) NOT NULL default '1',
							  `level` int(11) NOT NULL default '0',
							  `published` tinyint(1) NOT NULL default '1',
							  `menuid` int(11) NOT NULL DEFAULT '0',
							  `image` text,
							  `params` text,
							  PRIMARY KEY  (`id`),
							  KEY `title` (`title`,`alias`)
							) ENGINE=MyISAM  AUTO_INCREMENT=1 ;";
		$db->setQuery($query);
		if(!$db->query())
		{
			echo $img_ERROR.JText :: _('Unable to create table').$BR;
			echo $db->getErrorMsg();
			return false;
		}
		$query= "CREATE TABLE IF NOT EXISTS `#__osemsc_addon` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `name` varchar(50) NOT NULL,
							  `title` varchar(50) NOT NULL,
							  `frontend` int(1) NOT NULL DEFAULT '0',
							  `backend` int(1) NOT NULL DEFAULT '1',
							  `ordering` tinyint(2) DEFAULT '99',
							  `type` varchar(20) DEFAULT NULL,
							  `action` varchar(50) DEFAULT NULL,
							  `addon_name` varchar(50) DEFAULT NULL,
							  `backend_enabled` tinyint(1) NOT NULL DEFAULT '1',
							  `frontend_enabled` tinyint(1) NOT NULL DEFAULT '1',
							  PRIMARY KEY (`id`),
							  UNIQUE KEY `name` (`name`,`type`)
							) ENGINE=MyISAM  AUTO_INCREMENT=10;";
		$db->setQuery($query);
		if(!$db->query())
		{
			echo $img_ERROR.JText :: _('Unable to create table').$BR;
			echo $db->getErrorMsg();
			return false;
		}
		$query= "SELECT count(*) FROM `#__osemsc_addon`";
		$db->setQuery($query);
		$result= $db->loadResult();
		if(empty($result))
		{
			$query= " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) VALUES
											(NULL, 'msc', 'Membership Control', 0, 1, 2, 'panel', NULL, 'oseMscAddon.msc', 1, 0),
											(NULL, 'msc', 'Membership Information', 1, 0, 3, 'member_msc', '1', 'oseMscAddon.msc', 0, 1),
											(NULL, 'msc', 'Membership', 0, 1, 4, 'member_user', '1', 'oseMscAddon.msc', 1, 0),
											(NULL, 'msc', 'Join Msc', 1, 1, 1, 'join', '1', '', 1, 1),
											(NULL, 'msc', 'Renew Membership', 1, 1, 1, 'renew', '1', '', 1, 1),
											(NULL, 'msc_renew', 'Renew Membership', 1, 0, 1, 'member_msc', '1', 'oseMscAddon.msc_renew', 0, 1),
											(NULL, 'msc_list', 'Membership List', 1, 0, 1, 'registerOS_header', '0', 'oseMscAddon.msc_list', 0, 1),
											(NULL, 'mscuser', 'MscUser', 1, 1, 1, 'register_billing', '1', '', 1, 1),
											(NULL, 'msc_cancel', 'Cancel Membership', 1, 0, 2, 'member_msc', '1', 'oseMscAddon.msc_cancel', 0, 1),
											(NULL, 'mscuser', 'Msc User Info.', 1, 1, 5, 'registerOS_body', '1', '', 1, 1),

											(NULL, 'history', 'Member History', 1, 1, 2, 'join', '1', NULL, 1, 1),
											(NULL, 'history', 'Renew History', 1, 1, 2, 'renew', '1', '', 1, 1),
											(NULL, 'join_history', 'Membership History', 1, 1, 4, 'member_msc', '1', 'oseMscAddon.join_history', 1, 1),

											(NULL, 'payment', 'Payment Setting', 0, 1, 3, 'panel', '1', 'oseMscAddon.payment', 1, 0),
											(NULL, 'payment', 'Payment', 1, 0, 6, 'registerOS_body', '1', 'oseMscAddon.payment', 1, 1),
											(NULL, 'payment_mode', 'Membership Renewal Preference', 1, 0, 3, 'registerOS_body', '0', 'oseMscAddon.payment_mode', 0, 0),
											(NULL, 'paymentAdv', 'Advance Payment Setting', 0, 1, 4, 'panel', '1', 'oseMscAddon.paymentAdv', 1, 0),
											(NULL, 'payment_mode_e', 'Membership Renewal Preference', 1, 0, 3, 'registerOS_body', '0', 'oseMscAddon.payment_mode_e', 0, 0),
											(NULL, 'payment', 'Payment Information', 1, 0, 1, 'register_payment', '0', 'oseMscAddon.payment', 0, 1),

											(NULL, 'billinginfo', 'Billing Information', 1, 1, 1, 'member_billing', '1', 'oseMscAddon.billinginfo', 1, 1),
											(NULL, 'billinginfo', 'Billing Information', 1, 0, 2, 'register_billing', '1', 'oseMscAddon.billinginfo', 1, 1),
											(NULL, 'company', 'Company Information', 0, 0, 3, 'register_billing', '0', 'oseMscAddon.company', 0, 1),
											(NULL, 'billinginfo', 'Billing Information', 1, 0, 4, 'registerOS_body', '1', 'oseMscAddon.billinginfo', 0, 1),

											(NULL, 'profile', 'Additional Information', 1, 0, 2, 'registerOS_body', '1', 'oseMscAddon.profile', 0, 0),
											(NULL, 'profile', 'Additional Information', 1, 1, '', 'member_user', '1', 'oseMscAddon.profile', 1, 0),
											(NULL, 'mailing', 'Mailing Information', 1, 0, 2, 'registerOS_body', '1', 'oseMscAddon.mailing', 0, 0),
											(NULL, 'mailing', 'Mailing Information', 1, 1, 2, 'member_company', '1', 'oseMscAddon.mailing', 0, 0),

											(NULL, 'access', 'Access Level', 0, 1, 1, 'config', NULL, 'oseMscAddon.access', 0, 0),
											(NULL, 'menu', 'Menu Control', 0, 1, 1, 'content', NULL, 'oseMscAddon.menu', 1, 1),
											(NULL, 'module', 'Module Control', 0, 1, 2, 'content', NULL, 'oseMscAddon.module', 1, 0),
											(NULL, 'component', 'Component Control', 0, 1, 3, 'content', NULL, 'oseMscAddon.component', 1, 0),
											(NULL, 'jcontent', 'Joomla Content Control', 0, 1, 4, 'content', NULL, 'oseMscAddon.jcontent', 1, 0),
											(NULL, 'jcontent_sequential', 'Joomla Sequential Content Control', 0, 1, 7, 'content', NULL, 'oseMscAddon.jcontent_sequential', 1, 0),
											(NULL, 'jgroup', 'Joomla! Group', 1, 1, 8, 'join', '1', '', 1, 1),
											(NULL, 'jgroup', 'Joomla! Group Bridge', 0, 1, 3, 'bridge', '1', 'oseMscAddon.jgroup', 1, 0),
											(NULL, 'jgroup', 'Joomla! Group', 1, 1, 7, 'renew', '1', '', 1, 1),
											(NULL, 'juser', 'New Account Creation', 1, 0, 1, 'registerOS_body', '0', 'oseMscAddon.juser', 0, 1),
											(NULL, 'juser', 'My Account Information', 1, 1, 1, 'member_user', '1', 'oseMscAddon.juser', 1, 1),

											(NULL, 'order', 'Renew Order', 1, 1, 3, 'renew', '1', '', 1, 1),
											(NULL, 'order', 'Billing History', 1, 1, 7, 'member_billing', '1', 'oseMscAddon.order', 1, 1),
											(NULL, 'order', 'Join Order', 1, 1, 5, 'join', '1', '', 1, 1),
											(NULL, 'order', 'Cancel Order', 1, 0, 6, 'member_msc', '1', '', 0, 0),

											(NULL, 'basic', 'Basic Information', 0, 1, 1, 'panel', '1', 'oseMscAddon.basic', 1, 0),
											(NULL, 'phoca', 'PhocaDownload Mangement', 0, 1, 5, 'content', '1', 'oseMscAddon.phoca', 0, 0),
											(NULL, 'phpbb', 'PHPBB Forum User', 1, 1, 3, 'usersync', '1', NULL, 0, 0),
											(NULL, 'login', 'Existing User? Please Login', 1, 0, 2, 'registerOS_header', '0', 'oseMscAddon.login', 0, 1),
											(NULL, 'jspt', 'JSPT Bridge', 0, 1, 4, 'bridge', '1', 'oseMscAddon.jspt', 0, 0),
											(NULL, 'jspt', 'Renew: JSPT', 1, 1, 8, 'renew', '1', '', 0, 0),
											(NULL, 'jspt', 'Join: JSPT', 1, 1, 9, 'join', '1', '', 0, 0),

											(NULL, 'terms', 'Terms', 1, 0, 2, 'registerOS_footer', '0', 'oseMscAddon.terms', 0, 1),
											(NULL, 'terms', 'Terms', 1, 0, 2, 'register_payment', '0', 'oseMscAddon.terms', 0, 1),

											(NULL, 'k2item', 'K2 Item Management', 0, 1, 6, 'content', '1', 'oseMscAddon.k2item', 0, 0),
											(NULL, 'coupon', 'Coupon', 1, 0, 1, 'registerOS_footer', '0', 'oseMscAddon.coupon', 0, 0),
											(NULL, 'pap', 'Post Affiliate Pro', 1, 1, 10, 'join', '1', '', 0, 0),
											(NULL, 'pap', 'Renew: PAP', 1, 1, 9, 'renew', '1', '', 0, 0),
											(NULL, 'pap', 'Order: PAP', 1, 0, 1, 'register_order', '1', '', 0, 0);";
			$db->setQuery($query);
			if(!$db->query())
			{
				echo $img_ERROR.JText :: _('Unable to create table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		else
		{
			$query= " SELECT id FROM `#__osemsc_addon` WHERE `title` = 'Cancel Order' AND `type` ='member_msc'";
			$db->setQuery($query);
			$addon_id= $db->loadResult();
			if(empty($addon_id))
			{
				$query= " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) VALUES
														(NULL, 'order', 'Cancel Order', 1, 0, 6, 'member_msc', '1', '', 0, 0);";
			}
			$db->setQuery($query);
			if(!$db->query())
			{
				echo $img_ERROR.JText :: _('Unable to insert addon record').$BR;
				echo $db->getErrorMsg();
				return false;
			}

			$query= " SELECT id FROM `#__osemsc_addon` WHERE `name` = 'jcontent_sequential' AND `type` ='content'";
			$db->setQuery($query);
			$addon_id= $db->loadResult();
			if(empty($addon_id))
			{
				$query= " INSERT INTO `#__osemsc_addon` (`id`, `name`, `title`, `frontend`, `backend`, `ordering`, `type`, `action`, `addon_name`, `backend_enabled`, `frontend_enabled`) VALUES
														(NULL, 'jcontent_sequential', 'Joomla Sequential Content Control', 0, 1, 7, 'content', NULL, 'oseMscAddon.jcontent_sequential', 1, 0); ";
			}
			$db->setQuery($query);
			if(!$db->query())
			{
				echo $img_ERROR.JText :: _('Unable to insert addon record').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		$query= "CREATE TABLE IF NOT EXISTS `#__osemsc_billinginfo` (
							  `user_id` int(11) NOT NULL default '0',
							  `firstname` varchar(100) default NULL,
							  `lastname` varchar(100) default NULL,
							  `company` varchar(200) default NULL,
							  `addr1` text COMMENT 'address 1',
							  `addr2` text COMMENT 'address 2',
							  `city` varchar(100) default NULL,
							  `state` varchar(100) default NULL COMMENT 'State ID',
							  `country` varchar(100) default NULL COMMENT 'Country ID',
							  `postcode` varchar(20) default NULL,
							  `telephone` varchar(20) default NULL,
							  PRIMARY KEY  (`user_id`)
							) ENGINE=MyISAM ;	";
		$db->setQuery($query);
		if(!$db->query())
		{
			echo $img_ERROR.JText :: _('Unable to create table').$BR;
			echo $db->getErrorMsg();
			return false;
		}
		$query= "CREATE TABLE IF NOT EXISTS `#__osemsc_configuration` (
							  `id` int(11) NOT NULL auto_increment,
							  `key` text,
							  `value` text,
							  `type` varchar(20) NOT NULL,
							  `default` text NOT NULL,
							  PRIMARY KEY  (`id`)
							) ENGINE=MyISAM  AUTO_INCREMENT=1;";
		$db->setQuery($query);
		if(!$db->query())
		{
			echo $img_ERROR.JText :: _('Unable to create table').$BR;
			echo $db->getErrorMsg();
			return false;
		}
		$query= "SELECT count(*) FROM `#__osemsc_configuration`";
		$db->setQuery($query);
		$result= $db->loadResult();
		if(empty($result))
		{
			$query= "INSERT INTO `#__osemsc_configuration` (`id`, `key`, `value`, `type`, `default`) VALUES
									(18, 'is_member_mode_customized', '0', 'global', ''),
									(19, 'customized_member_mode', '', 'global', ''),
									(20, 'manual_renew_mode', 'renew', 'global', ''),
									(21, 'manual_to_automatic_mode', 'extend', 'global', ''),
									(22, 'gmap_key', '', 'global', ''),
									(23, 'payment_system', '0', 'payment', ''),
									(24, 'cc_testmode', '1', 'payment', ''),
									(25, 'auto_login', '1', 'member', ''),
									(26, 'admin_group', '25', 'email', ''),
									(27, 'default_reg_email', '', 'email', ''),
									(28, 'member_mode', '0', 'global', ''),
									(29, 'sendReg2Admin', '1', 'email', ''),
									(30, 'sendWel2Admin', '1', 'email', ''),
									(31, 'sendCancel2Admin', '1', 'email', ''),
									(32, 'sendExp2Admin', '1', 'email', ''),
									(33, 'sendReceipt2Admin', '1', 'email', ''),
									(34, 'register_form', 'onestep', 'register', ''),
									(35, 'onestep_payment_mode', 'm', 'register', ''),
									(36, 'auto_login', '1', 'register', ''),
									(37, 'paypal_email', '', 'payment', ''),
									(38, 'google_checkout_id', '', 'payment', ''),
									(39, 'google_checkout_key', '', 'payment', ''),
									(40, '2checkoutVendorId', '', 'payment', ''),
									(41, '2checkoutSecret', '', 'payment', ''),
									(42, 'an_merchant_email', '', 'payment', ''),
									(43, 'an_loginid', '', 'payment', ''),
									(44, 'an_transkey', '', 'payment', ''),
									(45, 'poffline_art_id', '', 'payment', ''),
									(46, 'enable_paypal', '0', 'payment', ''),
									(47, 'paypal_testmode', '1', 'payment', ''),
									(48, 'enable_gco', '0', 'payment', ''),
									(49, 'gco_testmode', '0', 'payment', ''),
									(50, 'enable_2co', '0', 'payment', ''),
									(51, '2co_testmode', '0', 'payment', ''),
									(52, 'an_email_merchant', '1', 'payment', ''),
									(53, 'an_email_customer', '1', 'payment', ''),
									(54, 'enable_authorize', '1', 'payment', ''),
									(55, 'enable_cc', '0', 'payment', ''),
									(56, 'enable_poffline', '0', 'payment', ''),
									(57, 'msc_extend', '0', 'global', ''),
									(58, 'default_receipt', '', 'email', ''),
									(59, 'authorize_testmode', '1', 'payment', ''),
									(60, 'cc_methods', 'authorize', 'payment', ''),
									(61, 'paypal_api_username', '', 'payment', ''),
									(62, 'paypal_api_passwd', '', 'payment', ''),
									(63, 'paypal_api_signature', '', 'payment', ''),
									(64, 'payment_mode', 'b', 'global', ''),
									(65, 'primary_currency', 'USD', 'currency', ''),
									(66, 'paypal_mode', 'Express Checkout', 'payment', ''),
									(67, 'is_msc_mode_customized', '0', 'global', ''),
									(68, 'customized_msc_mode', '', 'global', ''),
									(69, 'enable_eway', '1', 'payment', ''),
									(70, 'eway_testmode', '1', 'payment', ''),
									(71, 'eWayCustomerID', '87654321', 'payment', ''),
									(72, 'eWayUsername', 'TestAccount', 'payment', ''),
									(73, 'eWayPassword', 'dfafdasf', 'payment', ''),
									(74, 'frontend_style', 'msc6_default', 'global', ''),
									(75, 'backend_style', 'msc5', 'global', '');";
			$db->setQuery($query);
			if(!$db->query())
			{
				echo $img_ERROR.JText :: _('Unable to create table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		$query= "CREATE TABLE IF NOT EXISTS `#__osemsc_content` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `type` varchar(20) NOT NULL DEFAULT 'joomla',
							  `content_type` varchar(10) NOT NULL,
							  `content_id` int(11) NOT NULL,
							  `entry_type` varchar(20) NOT NULL DEFAULT 'msc',
							  `entry_id` int(11) NOT NULL,
							  `status` int(3) NOT NULL DEFAULT '0',
							  `params` text NOT NULL,
							  PRIMARY KEY (`id`),
							  UNIQUE KEY `type` (`type`,`content_type`,`content_id`,`entry_type`,`entry_id`)
							) ENGINE=MyISAM;";
		$db->setQuery($query);
		if(!$db->query())
		{
			echo $img_ERROR.JText :: _('Unable to create table').$BR;
			echo $db->getErrorMsg();
			return false;
		}
		$query= "CREATE TABLE IF NOT EXISTS `#__osemsc_directory` (
							  `directory_id` int(11) NOT NULL AUTO_INCREMENT,
							  `company_id` int(11) NOT NULL DEFAULT '0',
							  `directory_name` varchar(255) NOT NULL,
							  `directory_website` varchar(255) NOT NULL,
							  `directory_description` text NOT NULL,
							  `directory_logo` text NOT NULL,
							  PRIMARY KEY (`directory_id`)
							) ENGINE=MyISAM  AUTO_INCREMENT=1;	";
		$db->setQuery($query);
		if(!$db->query())
		{
			echo $img_ERROR.JText :: _('Unable to create table').$BR;
			echo $db->getErrorMsg();
			return false;
		}
		$query= "CREATE TABLE IF NOT EXISTS `#__osemsc_country` (
							  `country_id` int(11) NOT NULL auto_increment,
							  `country_name` varchar(64) default NULL,
							  `country_3_code` char(3) default NULL,
							  `country_2_code` char(2) default NULL,
							  PRIMARY KEY  (`country_id`),
							  KEY `idx_country_name` (`country_name`)
							) ENGINE=MyISAM  COMMENT='Country records' AUTO_INCREMENT=1";
		$db->setQuery($query);
		if(!$db->query())
		{
			echo $img_ERROR.JText :: _('Unable to create table').$BR;
			echo $db->getErrorMsg();
			return false;
		}
		$query= "SELECT count(*) FROM `#__osemsc_country`";
		$db->setQuery($query);
		$result= $db->loadResult();
		if(empty($result))
		{
			$query= "INSERT INTO `#__osemsc_country` (`country_id`, `country_name`, `country_3_code`, `country_2_code`) VALUES "."(1, 'Afghanistan', 'AFG', 'AF'), "."(2, 'Albania', 'ALB', 'AL'), "."(3, 'Algeria', 'DZA', 'DZ'), "."(4, 'American Samoa', 'ASM', 'AS'), "."(5, 'Andorra', 'AND', 'AD'), "."(6, 'Angola', 'AGO', 'AO'), "."(7, 'Anguilla', 'AIA', 'AI'), "."(8, 'Antarctica', 'ATA', 'AQ'), "."(9, 'Antigua and Barbuda', 'ATG', 'AG'), "."(10, 'Argentina', 'ARG', 'AR'), "."(11, 'Armenia', 'ARM', 'AM'), "."(12, 'Aruba', 'ABW', 'AW'), "."(13, 'Australia', 'AUS', 'AU'), "."(14, 'Austria', 'AUT', 'AT'), "."(15, 'Azerbaijan', 'AZE', 'AZ'), "."(16, 'Bahamas', 'BHS', 'BS'), "."(17, 'Bahrain', 'BHR', 'BH'), "."(18, 'Bangladesh', 'BGD', 'BD'), "."(19, 'Barbados', 'BRB', 'BB'), "."(20, 'Belarus', 'BLR', 'BY'), "."(21, 'Belgium', 'BEL', 'BE'), "."(22, 'Belize', 'BLZ', 'BZ'), "."(23, 'Benin', 'BEN', 'BJ'), "."(24, 'Bermuda', 'BMU', 'BM'), "."(25, 'Bhutan', 'BTN', 'BT'), "."(26, 'Bolivia', 'BOL', 'BO'), "."(27, 'Bosnia and Herzegowina', 'BIH', 'BA'), "."(28, 'Botswana', 'BWA', 'BW'), "."(29, 'Bouvet Island', 'BVT', 'BV'), "."(30, 'Brazil', 'BRA', 'BR'), "."(31, 'British Indian Ocean Territory', 'IOT', 'IO'), "."(32, 'Brunei Darussalam', 'BRN', 'BN'), "."(33, 'Bulgaria', 'BGR', 'BG'), "."(34, 'Burkina Faso', 'BFA', 'BF'), "."(35, 'Burundi', 'BDI', 'BI'), "."(36, 'Cambodia', 'KHM', 'KH'), "."(37, 'Cameroon', 'CMR', 'CM'), "."(38, 'Canada', 'CAN', 'CA'), "."(39, 'Cape Verde', 'CPV', 'CV'), "."(40, 'Cayman Islands', 'CYM', 'KY'), "."(41, 'Central African Republic', 'CAF', 'CF'), "."(42, 'Chad', 'TCD', 'TD'), "."(43, 'Chile', 'CHL', 'CL'), "."(44, 'China', 'CHN', 'CN'), "."(45, 'Christmas Island', 'CXR', 'CX'), "."(46, 'Cocos (Keeling) Islands', 'CCK', 'CC'), "."(47, 'Colombia', 'COL', 'CO'), "."(48, 'Comoros', 'COM', 'KM'), "."(49, 'Congo', 'COG', 'CG'), "."(50, 'Cook Islands', 'COK', 'CK'), "."(51, 'Costa Rica', 'CRI', 'CR'), "."(52, 'Cote D''Ivoire', 'CIV', 'CI'), "."(53, 'Croatia', 'HRV', 'HR'), "."(54, 'Cuba', 'CUB', 'CU'), "."(55, 'Cyprus', 'CYP', 'CY'), "."(56, 'Czech Republic', 'CZE', 'CZ'), "."(57, 'Denmark', 'DNK', 'DK'), "."(58, 'Djibouti', 'DJI', 'DJ'), "."(59, 'Dominica', 'DMA', 'DM'), "."(60, 'Dominican Republic', 'DOM', 'DO'), "."(61, 'East Timor', 'TMP', 'TP'), "."(62, 'Ecuador', 'ECU', 'EC'), "."(63, 'Egypt', 'EGY', 'EG'), "."(64, 'El Salvador', 'SLV', 'SV'), "."(65, 'Equatorial Guinea', 'GNQ', 'GQ'), "."(66, 'Eritrea', 'ERI', 'ER'), "."(67, 'Estonia', 'EST', 'EE'), "."(68, 'Ethiopia', 'ETH', 'ET'), "."(69, 'Falkland Islands (Malvinas)', 'FLK', 'FK'), "."(70, 'Faroe Islands', 'FRO', 'FO'), "."(71, 'Fiji', 'FJI', 'FJ'), "."(72, 'Finland', 'FIN', 'FI'), "."(73, 'France', 'FRA', 'FR'), "."(74, 'France, Metropolitan', 'FXX', 'FX'), "."(75, 'French Guiana', 'GUF', 'GF'), "."(76, 'French Polynesia', 'PYF', 'PF'), "."(77, 'French Southern Territories', 'ATF', 'TF'), "."(78, 'Gabon', 'GAB', 'GA'), "."(79, 'Gambia', 'GMB', 'GM'), "."(80, 'Georgia', 'GEO', 'GE'), "."(81, 'Germany', 'DEU', 'DE'), "."(82, 'Ghana', 'GHA', 'GH'), "."(83, 'Gibraltar', 'GIB', 'GI'), "."(84, 'Greece', 'GRC', 'GR'), "."(85, 'Greenland', 'GRL', 'GL'), "."(86, 'Grenada', 'GRD', 'GD'), "."(87, 'Guadeloupe', 'GLP', 'GP'), "."(88, 'Guam', 'GUM', 'GU'), "."(89, 'Guatemala', 'GTM', 'GT'), "."(90, 'Guinea', 'GIN', 'GN'), "."(91, 'Guinea-bissau', 'GNB', 'GW'), "."(92, 'Guyana', 'GUY', 'GY'), "."(93, 'Haiti', 'HTI', 'HT'), "."(94, 'Heard and Mc Donald Islands', 'HMD', 'HM'), "."(95, 'Honduras', 'HND', 'HN'), "."(96, 'Hong Kong', 'HKG', 'HK'), "."(97, 'Hungary', 'HUN', 'HU'), "."(98, 'Iceland', 'ISL', 'IS'), "."(99, 'India', 'IND', 'IN'), "."(100, 'Indonesia', 'IDN', 'ID'), "."(101, 'Iran (Islamic Republic of)', 'IRN', 'IR'), "."(102, 'Iraq', 'IRQ', 'IQ'), "."(103, 'Ireland', 'IRL', 'IE'), "."(104, 'Israel', 'ISR', 'IL'), "."(105, 'Italy', 'ITA', 'IT'), "."(106, 'Jamaica', 'JAM', 'JM'), "."(107, 'Japan', 'JPN', 'JP'), "."(108, 'Jordan', 'JOR', 'JO'), "."(109, 'Kazakhstan', 'KAZ', 'KZ'), "."(110, 'Kenya', 'KEN', 'KE'), "."(111, 'Kiribati', 'KIR', 'KI'), "."(112, 'Korea, Democratic People''s Republic of', 'PRK', 'KP'), "."(113, 'Korea, Republic of', 'KOR', 'KR'), "."(114, 'Kuwait', 'KWT', 'KW'), "."(115, 'Kyrgyzstan', 'KGZ', 'KG'), "."(116, 'Lao People''s Democratic Republic', 'LAO', 'LA'), "."(117, 'Latvia', 'LVA', 'LV'), "."(118, 'Lebanon', 'LBN', 'LB'), "."(119, 'Lesotho', 'LSO', 'LS'), "."(120, 'Liberia', 'LBR', 'LR'), "."(121, 'Libyan Arab Jamahiriya', 'LBY', 'LY'), "."(122, 'Liechtenstein', 'LIE', 'LI'), "."(123, 'Lithuania', 'LTU', 'LT'), "."(124, 'Luxembourg', 'LUX', 'LU'), "."(125, 'Macau', 'MAC', 'MO'), "."(126, 'Macedonia, The Former Yugoslav Republic of', 'MKD', 'MK'), "."(127, 'Madagascar', 'MDG', 'MG'), "."(128, 'Malawi', 'MWI', 'MW'), "."(129, 'Malaysia', 'MYS', 'MY'), "."(130, 'Maldives', 'MDV', 'MV'), "."(131, 'Mali', 'MLI', 'ML'), "."(132, 'Malta', 'MLT', 'MT'), "."(133, 'Marshall Islands', 'MHL', 'MH'), "."(134, 'Martinique', 'MTQ', 'MQ'), "."(135, 'Mauritania', 'MRT', 'MR'), "."(136, 'Mauritius', 'MUS', 'MU'), "."(137, 'Mayotte', 'MYT', 'YT'), "."(138, 'Mexico', 'MEX', 'MX'), "."(139, 'Micronesia, Federated States of', 'FSM', 'FM'), "."(140, 'Moldova, Republic of', 'MDA', 'MD'), "."(141, 'Monaco', 'MCO', 'MC'), "."(142, 'Mongolia', 'MNG', 'MN'), "."(143, 'Montserrat', 'MSR', 'MS'), "."(144, 'Morocco', 'MAR', 'MA'), "."(145, 'Mozambique', 'MOZ', 'MZ'), "."(146, 'Myanmar', 'MMR', 'MM'), "."(147, 'Namibia', 'NAM', 'NA'), "."(148, 'Nauru', 'NRU', 'NR'), "."(149, 'Nepal', 'NPL', 'NP'), "."(150, 'Netherlands', 'NLD', 'NL'), "."(151, 'Netherlands Antilles', 'ANT', 'AN'), "."(152, 'New Caledonia', 'NCL', 'NC'), "."(153, 'New Zealand', 'NZL', 'NZ'), "."(154, 'Nicaragua', 'NIC', 'NI'), "."(155, 'Niger', 'NER', 'NE'), "."(156, 'Nigeria', 'NGA', 'NG'), "."(157, 'Niue', 'NIU', 'NU'), "."(158, 'Norfolk Island', 'NFK', 'NF'), "."(159, 'Northern Mariana Islands', 'MNP', 'MP'), "."(160, 'Norway', 'NOR', 'NO'), "."(161, 'Oman', 'OMN', 'OM'), "."(162, 'Pakistan', 'PAK', 'PK'), "."(163, 'Palau', 'PLW', 'PW'), "."(164, 'Panama', 'PAN', 'PA'), "."(165, 'Papua New Guinea', 'PNG', 'PG'), "."(166, 'Paraguay', 'PRY', 'PY'), "."(167, 'Peru', 'PER', 'PE'), "."(168, 'Philippines', 'PHL', 'PH'), "."(169, 'Pitcairn', 'PCN', 'PN'), "."(170, 'Poland', 'POL', 'PL'), "."(171, 'Portugal', 'PRT', 'PT'), "."(172, 'Puerto Rico', 'PRI', 'PR'), "."(173, 'Qatar', 'QAT', 'QA'), "."(174, 'Reunion', 'REU', 'RE'), "."(175, 'Romania', 'ROM', 'RO'), "."(176, 'Russian Federation', 'RUS', 'RU'), "."(177, 'Rwanda', 'RWA', 'RW'), "."(178, 'Saint Kitts and Nevis', 'KNA', 'KN'), "."(179, 'Saint Lucia', 'LCA', 'LC'), "."(180, 'Saint Vincent and the Grenadines', 'VCT', 'VC'), "."(181, 'Samoa', 'WSM', 'WS'), "."(182, 'San Marino', 'SMR', 'SM'), "."(183, 'Sao Tome and Principe', 'STP', 'ST'), "."(184, 'Saudi Arabia', 'SAU', 'SA'), "."(185, 'Senegal', 'SEN', 'SN'), "."(186, 'Seychelles', 'SYC', 'SC'), "."(187, 'Sierra Leone', 'SLE', 'SL'), "."(188, 'Singapore', 'SGP', 'SG'), "."(189, 'Slovakia (Slovak Republic)', 'SVK', 'SK'), "."(190, 'Slovenia', 'SVN', 'SI'), "."(191, 'Solomon Islands', 'SLB', 'SB'), "."(192, 'Somalia', 'SOM', 'SO'), "."(193, 'South Africa', 'ZAF', 'ZA'), "."(194, 'South Georgia and the South Sandwich Islands', 'SGS', 'GS'), "."(195, 'Spain', 'ESP', 'ES'), "."(196, 'Sri Lanka', 'LKA', 'LK'), "."(197, 'St. Helena', 'SHN', 'SH'), "."(198, 'St. Pierre and Miquelon', 'SPM', 'PM'), "."(199, 'Sudan', 'SDN', 'SD'), "."(200, 'Suriname', 'SUR', 'SR'), "."(201, 'Svalbard and Jan Mayen Islands', 'SJM', 'SJ'), "."(202, 'Swaziland', 'SWZ', 'SZ'), "."(203, 'Sweden', 'SWE', 'SE'), "."(204, 'Switzerland', 'CHE', 'CH'), "."(205, 'Syrian Arab Republic', 'SYR', 'SY'), "."(206, 'Taiwan', 'TWN', 'TW'), "."(207, 'Tajikistan', 'TJK', 'TJ'), "."(208, 'Tanzania, United Republic of', 'TZA', 'TZ'), "."(209, 'Thailand', 'THA', 'TH'), "."(210, 'Togo', 'TGO', 'TG'), "."(211, 'Tokelau', 'TKL', 'TK'), "."(212, 'Tonga', 'TON', 'TO'), "."(213, 'Trinidad and Tobago', 'TTO', 'TT'), "."(214, 'Tunisia', 'TUN', 'TN'), "."(215, 'Turkey', 'TUR', 'TR'), "."(216, 'Turkmenistan', 'TKM', 'TM'), "."(217, 'Turks and Caicos Islands', 'TCA', 'TC'), "."(218, 'Tuvalu', 'TUV', 'TV'), "."(219, 'Uganda', 'UGA', 'UG'), "."(220, 'Ukraine', 'UKR', 'UA'), "."(221, 'United Arab Emirates', 'ARE', 'AE'), "."(222, 'United Kingdom', 'GBR', 'GB'), "."(223, 'United States', 'USA', 'US'), "."(224, 'United States Minor Outlying Islands', 'UMI', 'UM'), "."(225, 'Uruguay', 'URY', 'UY'), "."(226, 'Uzbekistan', 'UZB', 'UZ'), "."(227, 'Vanuatu', 'VUT', 'VU'), "."(228, 'Vatican City State (Holy See)', 'VAT', 'VA'), "."(229, 'Venezuela', 'VEN', 'VE'), "."(230, 'Viet Nam', 'VNM', 'VN'), "."(231, 'Virgin Islands (British)', 'VGB', 'VG'), "."(232, 'Virgin Islands (U.S.)', 'VIR', 'VI'), "."(233, 'Wallis and Futuna Islands', 'WLF', 'WF'), "."(234, 'Western Sahara', 'ESH', 'EH'), "."(235, 'Yemen', 'YEM', 'YE'), "."(236, 'Serbia', 'SRB', 'RS'), "."(237, 'The Democratic Republic of Congo', 'DRC', 'DC'), "."(238, 'Zambia', 'ZMB', 'ZM'), "."(239, 'Zimbabwe', 'ZWE', 'ZW'), "."(240, 'East Timor', 'XET', 'XE'), "."(241, 'Jersey', 'XJE', 'XJ'), "."(242, 'St. Barthelemy', 'XSB', 'XB'), "."(243, 'St. Eustatius', 'XSE', 'XU'), "."(244, 'Canary Islands', 'XCA', 'XC'), "."(245, 'Montenegro', 'MNE', 'ME')";
			$db->setQuery($query);
			if(!$db->query())
			{
				echo $img_ERROR.JText :: _('Unable to create table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		$query= "CREATE TABLE IF NOT EXISTS `#__osemsc_state` (
							  `state_id` int(11) NOT NULL AUTO_INCREMENT,
							  `country_id` int(11) NOT NULL DEFAULT '1',
							  `state_name` varchar(64) DEFAULT NULL,
							  `state_3_code` char(3) DEFAULT NULL,
							  `state_2_code` char(2) DEFAULT NULL,
							  PRIMARY KEY (`state_id`),
							  UNIQUE KEY `state_3_code` (`country_id`,`state_3_code`),
							  UNIQUE KEY `state_2_code` (`country_id`,`state_2_code`),
							  KEY `idx_country_id` (`country_id`)
							) ENGINE=MyISAM ;";
		$db->setQuery($query);
		if(!$db->query())
		{
			echo $img_ERROR.JText :: _('Unable to create table').$BR;
			echo $db->getErrorMsg();
			return false;
		}
		$query= "SELECT count(*) FROM `#__osemsc_state`";
		$db->setQuery($query);
		$result= $db->loadResult();
		if(empty($result))
		{
			$query= "INSERT INTO `#__osemsc_state` (`state_id`, `country_id`, `state_name`, `state_3_code`, `state_2_code`) VALUES
										(1, 223, 'Alabama', 'ALA', 'AL'),
										(2, 223, 'Alaska', 'ALK', 'AK'),
										(3, 223, 'Arizona', 'ARZ', 'AZ'),
										(4, 223, 'Arkansas', 'ARK', 'AR'),
										(5, 223, 'California', 'CAL', 'CA'),
										(6, 223, 'Colorado', 'COL', 'CO'),
										(7, 223, 'Connecticut', 'CCT', 'CT'),
										(8, 223, 'Delaware', 'DEL', 'DE'),
										(9, 223, 'District Of Columbia', 'DOC', 'DC'),
										(10, 223, 'Florida', 'FLO', 'FL'),
										(11, 223, 'Georgia', 'GEA', 'GA'),
										(12, 223, 'Hawaii', 'HWI', 'HI'),
										(13, 223, 'Idaho', 'IDA', 'ID'),
										(14, 223, 'Illinois', 'ILL', 'IL'),
										(15, 223, 'Indiana', 'IND', 'IN'),
										(16, 223, 'Iowa', 'IOA', 'IA'),
										(17, 223, 'Kansas', 'KAS', 'KS'),
										(18, 223, 'Kentucky', 'KTY', 'KY'),
										(19, 223, 'Louisiana', 'LOA', 'LA'),
										(20, 223, 'Maine', 'MAI', 'ME'),
										(21, 223, 'Maryland', 'MLD', 'MD'),
										(22, 223, 'Massachusetts', 'MSA', 'MA'),
										(23, 223, 'Michigan', 'MIC', 'MI'),
										(24, 223, 'Minnesota', 'MIN', 'MN'),
										(25, 223, 'Mississippi', 'MIS', 'MS'),
										(26, 223, 'Missouri', 'MIO', 'MO'),
										(27, 223, 'Montana', 'MOT', 'MT'),
										(28, 223, 'Nebraska', 'NEB', 'NE'),
										(29, 223, 'Nevada', 'NEV', 'NV'),
										(30, 223, 'New Hampshire', 'NEH', 'NH'),
										(31, 223, 'New Jersey', 'NEJ', 'NJ'),
										(32, 223, 'New Mexico', 'NEM', 'NM'),
										(33, 223, 'New York', 'NEY', 'NY'),
										(34, 223, 'North Carolina', 'NOC', 'NC'),
										(35, 223, 'North Dakota', 'NOD', 'ND'),
										(36, 223, 'Ohio', 'OHI', 'OH'),
										(37, 223, 'Oklahoma', 'OKL', 'OK'),
										(38, 223, 'Oregon', 'ORN', 'OR'),
										(39, 223, 'Pennsylvania', 'PEA', 'PA'),
										(40, 223, 'Rhode Island', 'RHI', 'RI'),
										(41, 223, 'South Carolina', 'SOC', 'SC'),
										(42, 223, 'South Dakota', 'SOD', 'SD'),
										(43, 223, 'Tennessee', 'TEN', 'TN'),
										(44, 223, 'Texas', 'TXS', 'TX'),
										(45, 223, 'Utah', 'UTA', 'UT'),
										(46, 223, 'Vermont', 'VMT', 'VT'),
										(47, 223, 'Virginia', 'VIA', 'VA'),
										(48, 223, 'Washington', 'WAS', 'WA'),
										(49, 223, 'West Virginia', 'WEV', 'WV'),
										(50, 223, 'Wisconsin', 'WIS', 'WI'),
										(51, 223, 'Wyoming', 'WYO', 'WY'),
										(52, 38, 'Alberta', 'ALB', 'AB'),
										(53, 38, 'British Columbia', 'BRC', 'BC'),
										(54, 38, 'Manitoba', 'MAB', 'MB'),
										(55, 38, 'New Brunswick', 'NEB', 'NB'),
										(56, 38, 'Newfoundland and Labrador', 'NFL', 'NL'),
										(57, 38, 'Northwest Territories', 'NWT', 'NT'),
										(58, 38, 'Nova Scotia', 'NOS', 'NS'),
										(59, 38, 'Nunavut', 'NUT', 'NU'),
										(60, 38, 'Ontario', 'ONT', 'ON'),
										(61, 38, 'Prince Edward Island', 'PEI', 'PE'),
										(62, 38, 'Quebec', 'QEC', 'QC'),
										(63, 38, 'Saskatchewan', 'SAK', 'SK'),
										(64, 38, 'Yukon', 'YUT', 'YT'),
										(65, 222, 'England', 'ENG', 'EN'),
										(66, 222, 'Northern Ireland', 'NOI', 'NI'),
										(67, 222, 'Scotland', 'SCO', 'SD'),
										(68, 222, 'Wales', 'WLS', 'WS'),
										(69, 13, 'Australian Capital Territory', 'ACT', 'AC'),
										(70, 13, 'New South Wales', 'NSW', 'NS'),
										(71, 13, 'Northern Territory', 'NOT', 'NT'),
										(72, 13, 'Queensland', 'QLD', 'QL'),
										(73, 13, 'South Australia', 'SOA', 'SA'),
										(74, 13, 'Tasmania', 'TAS', 'TS'),
										(75, 13, 'Victoria', 'VIC', 'VI'),
										(76, 13, 'Western Australia', 'WEA', 'WA'),
										(77, 138, 'Aguascalientes', 'AGS', 'AG'),
										(78, 138, 'Baja California Norte', 'BCN', 'BN'),
										(79, 138, 'Baja California Sur', 'BCS', 'BS'),
										(80, 138, 'Campeche', 'CAM', 'CA'),
										(81, 138, 'Chiapas', 'CHI', 'CS'),
										(82, 138, 'Chihuahua', 'CHA', 'CH'),
										(83, 138, 'Coahuila', 'COA', 'CO'),
										(84, 138, 'Colima', 'COL', 'CM'),
										(85, 138, 'Distrito Federal', 'DFM', 'DF'),
										(86, 138, 'Durango', 'DGO', 'DO'),
										(87, 138, 'Guanajuato', 'GTO', 'GO'),
										(88, 138, 'Guerrero', 'GRO', 'GU'),
										(89, 138, 'Hidalgo', 'HGO', 'HI'),
										(90, 138, 'Jalisco', 'JAL', 'JA'),
										(91, 138, 'México (Estado de)', 'EDM', 'EM'),
										(92, 138, 'Michoacán', 'MCN', 'MI'),
										(93, 138, 'Morelos', 'MOR', 'MO'),
										(94, 138, 'Nayarit', 'NAY', 'NY'),
										(95, 138, 'Nuevo León', 'NUL', 'NL'),
										(96, 138, 'Oaxaca', 'OAX', 'OA'),
										(97, 138, 'Puebla', 'PUE', 'PU'),
										(98, 138, 'Querétaro', 'QRO', 'QU'),
										(99, 138, 'Quintana Roo', 'QUR', 'QR'),
										(100, 138, 'San Luis Potosí', 'SLP', 'SP'),
										(101, 138, 'Sinaloa', 'SIN', 'SI'),
										(102, 138, 'Sonora', 'SON', 'SO'),
										(103, 138, 'Tabasco', 'TAB', 'TA'),
										(104, 138, 'Tamaulipas', 'TAM', 'TM'),
										(105, 138, 'Tlaxcala', 'TLX', 'TX'),
										(106, 138, 'Veracruz', 'VER', 'VZ'),
										(107, 138, 'Yucatán', 'YUC', 'YU'),
										(108, 138, 'Zacatecas', 'ZAC', 'ZA'),
										(109, 30, 'Acre', 'ACR', 'AC'),
										(110, 30, 'Alagoas', 'ALG', 'AL'),
										(111, 30, 'Amapá', 'AMP', 'AP'),
										(112, 30, 'Amazonas', 'AMZ', 'AM'),
										(113, 30, 'Bahía', 'BAH', 'BA'),
										(114, 30, 'Ceará', 'CEA', 'CE'),
										(115, 30, 'Distrito Federal', 'DFB', 'DF'),
										(116, 30, 'Espirito Santo', 'ESS', 'ES'),
										(117, 30, 'Goiás', 'GOI', 'GO'),
										(118, 30, 'Maranhão', 'MAR', 'MA'),
										(119, 30, 'Mato Grosso', 'MAT', 'MT'),
										(120, 30, 'Mato Grosso do Sul', 'MGS', 'MS'),
										(121, 30, 'Minas Geraís', 'MIG', 'MG'),
										(122, 30, 'Paraná', 'PAR', 'PR'),
										(123, 30, 'Paraíba', 'PRB', 'PB'),
										(124, 30, 'Pará', 'PAB', 'PA'),
										(125, 30, 'Pernambuco', 'PER', 'PE'),
										(126, 30, 'Piauí', 'PIA', 'PI'),
										(127, 30, 'Rio Grande do Norte', 'RGN', 'RN'),
										(128, 30, 'Rio Grande do Sul', 'RGS', 'RS'),
										(129, 30, 'Rio de Janeiro', 'RDJ', 'RJ'),
										(130, 30, 'Rondônia', 'RON', 'RO'),
										(131, 30, 'Roraima', 'ROR', 'RR'),
										(132, 30, 'Santa Catarina', 'SAC', 'SC'),
										(133, 30, 'Sergipe', 'SER', 'SE'),
										(134, 30, 'São Paulo', 'SAP', 'SP'),
										(135, 30, 'Tocantins', 'TOC', 'TO'),
										(136, 44, 'Anhui', 'ANH', '34'),
										(137, 44, 'Beijing', 'BEI', '11'),
										(138, 44, 'Chongqing', 'CHO', '50'),
										(139, 44, 'Fujian', 'FUJ', '35'),
										(140, 44, 'Gansu', 'GAN', '62'),
										(141, 44, 'Guangdong', 'GUA', '44'),
										(142, 44, 'Guangxi Zhuang', 'GUZ', '45'),
										(143, 44, 'Guizhou', 'GUI', '52'),
										(144, 44, 'Hainan', 'HAI', '46'),
										(145, 44, 'Hebei', 'HEB', '13'),
										(146, 44, 'Heilongjiang', 'HEI', '23'),
										(147, 44, 'Henan', 'HEN', '41'),
										(148, 44, 'Hubei', 'HUB', '42'),
										(149, 44, 'Hunan', 'HUN', '43'),
										(150, 44, 'Jiangsu', 'JIA', '32'),
										(151, 44, 'Jiangxi', 'JIX', '36'),
										(152, 44, 'Jilin', 'JIL', '22'),
										(153, 44, 'Liaoning', 'LIA', '21'),
										(154, 44, 'Nei Mongol', 'NML', '15'),
										(155, 44, 'Ningxia Hui', 'NIH', '64'),
										(156, 44, 'Qinghai', 'QIN', '63'),
										(157, 44, 'Shandong', 'SNG', '37'),
										(158, 44, 'Shanghai', 'SHH', '31'),
										(159, 44, 'Shaanxi', 'SHX', '61'),
										(160, 44, 'Sichuan', 'SIC', '51'),
										(161, 44, 'Tianjin', 'TIA', '12'),
										(162, 44, 'Xinjiang Uygur', 'XIU', '65'),
										(163, 44, 'Xizang', 'XIZ', '54'),
										(164, 44, 'Yunnan', 'YUN', '53'),
										(165, 44, 'Zhejiang', 'ZHE', '33'),
										(166, 104, 'Israel', 'ISL', 'IL'),
										(167, 104, 'Gaza Strip', 'GZS', 'GZ'),
										(168, 104, 'West Bank', 'WBK', 'WB'),
										(169, 151, 'St. Maarten', 'STM', 'SM'),
										(170, 151, 'Bonaire', 'BNR', 'BN'),
										(171, 151, 'Curacao', 'CUR', 'CR'),
										(172, 175, 'Alba', 'ABA', 'AB'),
										(173, 175, 'Arad', 'ARD', 'AR'),
										(174, 175, 'Arges', 'ARG', 'AG'),
										(175, 175, 'Bacau', 'BAC', 'BC'),
										(176, 175, 'Bihor', 'BIH', 'BH'),
										(177, 175, 'Bistrita-Nasaud', 'BIS', 'BN'),
										(178, 175, 'Botosani', 'BOT', 'BT'),
										(179, 175, 'Braila', 'BRL', 'BR'),
										(180, 175, 'Brasov', 'BRA', 'BV'),
										(181, 175, 'Bucuresti', 'BUC', 'B'),
										(182, 175, 'Buzau', 'BUZ', 'BZ'),
										(183, 175, 'Calarasi', 'CAL', 'CL'),
										(184, 175, 'Caras Severin', 'CRS', 'CS'),
										(185, 175, 'Cluj', 'CLJ', 'CJ'),
										(186, 175, 'Constanta', 'CST', 'CT'),
										(187, 175, 'Covasna', 'COV', 'CV'),
										(188, 175, 'Dambovita', 'DAM', 'DB'),
										(189, 175, 'Dolj', 'DLJ', 'DJ'),
										(190, 175, 'Galati', 'GAL', 'GL'),
										(191, 175, 'Giurgiu', 'GIU', 'GR'),
										(192, 175, 'Gorj', 'GOR', 'GJ'),
										(193, 175, 'Hargita', 'HRG', 'HR'),
										(194, 175, 'Hunedoara', 'HUN', 'HD'),
										(195, 175, 'Ialomita', 'IAL', 'IL'),
										(196, 175, 'Iasi', 'IAS', 'IS'),
										(197, 175, 'Ilfov', 'ILF', 'IF'),
										(198, 175, 'Maramures', 'MAR', 'MM'),
										(199, 175, 'Mehedinti', 'MEH', 'MH'),
										(200, 175, 'Mures', 'MUR', 'MS'),
										(201, 175, 'Neamt', 'NEM', 'NT'),
										(202, 175, 'Olt', 'OLT', 'OT'),
										(203, 175, 'Prahova', 'PRA', 'PH'),
										(204, 175, 'Salaj', 'SAL', 'SJ'),
										(205, 175, 'Satu Mare', 'SAT', 'SM'),
										(206, 175, 'Sibiu', 'SIB', 'SB'),
										(207, 175, 'Suceava', 'SUC', 'SV'),
										(208, 175, 'Teleorman', 'TEL', 'TR'),
										(209, 175, 'Timis', 'TIM', 'TM'),
										(210, 175, 'Tulcea', 'TUL', 'TL'),
										(211, 175, 'Valcea', 'VAL', 'VL'),
										(212, 175, 'Vaslui', 'VAS', 'VS'),
										(213, 175, 'Vrancea', 'VRA', 'VN'),
										(214, 105, 'Agrigento', 'AGR', 'AG'),
										(215, 105, 'Alessandria', 'ALE', 'AL'),
										(216, 105, 'Ancona', 'ANC', 'AN'),
										(217, 105, 'Aosta', 'AOS', 'AO'),
										(218, 105, 'Arezzo', 'ARE', 'AR'),
										(219, 105, 'Ascoli Piceno', 'API', 'AP'),
										(220, 105, 'Asti', 'AST', 'AT'),
										(221, 105, 'Avellino', 'AVE', 'AV'),
										(222, 105, 'Bari', 'BAR', 'BA'),
										(223, 105, 'Belluno', 'BEL', 'BL'),
										(224, 105, 'Benevento', 'BEN', 'BN'),
										(225, 105, 'Bergamo', 'BEG', 'BG'),
										(226, 105, 'Biella', 'BIE', 'BI'),
										(227, 105, 'Bologna', 'BOL', 'BO'),
										(228, 105, 'Bolzano', 'BOZ', 'BZ'),
										(229, 105, 'Brescia', 'BRE', 'BS'),
										(230, 105, 'Brindisi', 'BRI', 'BR'),
										(231, 105, 'Cagliari', 'CAG', 'CA'),
										(232, 105, 'Caltanissetta', 'CAL', 'CL'),
										(233, 105, 'Campobasso', 'CBO', 'CB'),
										(234, 105, 'Carbonia-Iglesias', 'CAR', 'CI'),
										(235, 105, 'Caserta', 'CAS', 'CE'),
										(236, 105, 'Catania', 'CAT', 'CT'),
										(237, 105, 'Catanzaro', 'CTZ', 'CZ'),
										(238, 105, 'Chieti', 'CHI', 'CH'),
										(239, 105, 'Como', 'COM', 'CO'),
										(240, 105, 'Cosenza', 'COS', 'CS'),
										(241, 105, 'Cremona', 'CRE', 'CR'),
										(242, 105, 'Crotone', 'CRO', 'KR'),
										(243, 105, 'Cuneo', 'CUN', 'CN'),
										(244, 105, 'Enna', 'ENN', 'EN'),
										(245, 105, 'Ferrara', 'FER', 'FE'),
										(246, 105, 'Firenze', 'FIR', 'FI'),
										(247, 105, 'Foggia', 'FOG', 'FG'),
										(248, 105, 'Forli-Cesena', 'FOC', 'FC'),
										(249, 105, 'Frosinone', 'FRO', 'FR'),
										(250, 105, 'Genova', 'GEN', 'GE'),
										(251, 105, 'Gorizia', 'GOR', 'GO'),
										(252, 105, 'Grosseto', 'GRO', 'GR'),
										(253, 105, 'Imperia', 'IMP', 'IM'),
										(254, 105, 'Isernia', 'ISE', 'IS'),
										(255, 105, 'L''Aquila', 'AQU', 'AQ'),
										(256, 105, 'La Spezia', 'LAS', 'SP'),
										(257, 105, 'Latina', 'LAT', 'LT'),
										(258, 105, 'Lecce', 'LEC', 'LE'),
										(259, 105, 'Lecco', 'LCC', 'LC'),
										(260, 105, 'Livorno', 'LIV', 'LI'),
										(261, 105, 'Lodi', 'LOD', 'LO'),
										(262, 105, 'Lucca', 'LUC', 'LU'),
										(263, 105, 'Macerata', 'MAC', 'MC'),
										(264, 105, 'Mantova', 'MAN', 'MN'),
										(265, 105, 'Massa-Carrara', 'MAS', 'MS'),
										(266, 105, 'Matera', 'MAA', 'MT'),
										(267, 105, 'Medio Campidano', 'MED', 'VS'),
										(268, 105, 'Messina', 'MES', 'ME'),
										(269, 105, 'Milano', 'MIL', 'MI'),
										(270, 105, 'Modena', 'MOD', 'MO'),
										(271, 105, 'Napoli', 'NAP', 'NA'),
										(272, 105, 'Novara', 'NOV', 'NO'),
										(273, 105, 'Nuoro', 'NUR', 'NU'),
										(274, 105, 'Ogliastra', 'OGL', 'OG'),
										(275, 105, 'Olbia-Tempio', 'OLB', 'OT'),
										(276, 105, 'Oristano', 'ORI', 'OR'),
										(277, 105, 'Padova', 'PDA', 'PD'),
										(278, 105, 'Palermo', 'PAL', 'PA'),
										(279, 105, 'Parma', 'PAA', 'PR'),
										(280, 105, 'Pavia', 'PAV', 'PV'),
										(281, 105, 'Perugia', 'PER', 'PG'),
										(282, 105, 'Pesaro e Urbino', 'PES', 'PU'),
										(283, 105, 'Pescara', 'PSC', 'PE'),
										(284, 105, 'Piacenza', 'PIA', 'PC'),
										(285, 105, 'Pisa', 'PIS', 'PI'),
										(286, 105, 'Pistoia', 'PIT', 'PT'),
										(287, 105, 'Pordenone', 'POR', 'PN'),
										(288, 105, 'Potenza', 'PTZ', 'PZ'),
										(289, 105, 'Prato', 'PRA', 'PO'),
										(290, 105, 'Ragusa', 'RAG', 'RG'),
										(291, 105, 'Ravenna', 'RAV', 'RA'),
										(292, 105, 'Reggio Calabria', 'REG', 'RC'),
										(293, 105, 'Reggio Emilia', 'REE', 'RE'),
										(294, 105, 'Rieti', 'RIE', 'RI'),
										(295, 105, 'Rimini', 'RIM', 'RN'),
										(296, 105, 'Roma', 'ROM', 'RM'),
										(297, 105, 'Rovigo', 'ROV', 'RO'),
										(298, 105, 'Salerno', 'SAL', 'SA'),
										(299, 105, 'Sassari', 'SAS', 'SS'),
										(300, 105, 'Savona', 'SAV', 'SV'),
										(301, 105, 'Siena', 'SIE', 'SI'),
										(302, 105, 'Siracusa', 'SIR', 'SR'),
										(303, 105, 'Sondrio', 'SOO', 'SO'),
										(304, 105, 'Taranto', 'TAR', 'TA'),
										(305, 105, 'Teramo', 'TER', 'TE'),
										(306, 105, 'Terni', 'TRN', 'TR'),
										(307, 105, 'Torino', 'TOR', 'TO'),
										(308, 105, 'Trapani', 'TRA', 'TP'),
										(309, 105, 'Trento', 'TRE', 'TN'),
										(310, 105, 'Treviso', 'TRV', 'TV'),
										(311, 105, 'Trieste', 'TRI', 'TS'),
										(312, 105, 'Udine', 'UDI', 'UD'),
										(313, 105, 'Varese', 'VAR', 'VA'),
										(314, 105, 'Venezia', 'VEN', 'VE'),
										(315, 105, 'Verbano Cusio Ossola', 'VCO', 'VB'),
										(316, 105, 'Vercelli', 'VER', 'VC'),
										(317, 105, 'Verona', 'VRN', 'VR'),
										(318, 105, 'Vibo Valenzia', 'VIV', 'VV'),
										(319, 105, 'Vicenza', 'VII', 'VI'),
										(320, 105, 'Viterbo', 'VIT', 'VT'),
										(321, 195, 'A Coruña', 'ACO', '15'),
										(322, 195, 'Alava', 'ALA', '01'),
										(323, 195, 'Albacete', 'ALB', '02'),
										(324, 195, 'Alicante', 'ALI', '03'),
										(325, 195, 'Almeria', 'ALM', '04'),
										(326, 195, 'Asturias', 'AST', '33'),
										(327, 195, 'Avila', 'AVI', '05'),
										(328, 195, 'Badajoz', 'BAD', '06'),
										(329, 195, 'Baleares', 'BAL', '07'),
										(330, 195, 'Barcelona', 'BAR', '08'),
										(331, 195, 'Burgos', 'BUR', '09'),
										(332, 195, 'Caceres', 'CAC', '10'),
										(333, 195, 'Cadiz', 'CAD', '11'),
										(334, 195, 'Cantabria', 'CAN', '39'),
										(335, 195, 'Castellon', 'CAS', '12'),
										(336, 195, 'Ceuta', 'CEU', '51'),
										(337, 195, 'Ciudad Real', 'CIU', '13'),
										(338, 195, 'Cordoba', 'COR', '14'),
										(339, 195, 'Cuenca', 'CUE', '16'),
										(340, 195, 'Girona', 'GIR', '17'),
										(341, 195, 'Granada', 'GRA', '18'),
										(342, 195, 'Guadalajara', 'GUA', '19'),
										(343, 195, 'Guipuzcoa', 'GUI', '20'),
										(344, 195, 'Huelva', 'HUL', '21'),
										(345, 195, 'Huesca', 'HUS', '22'),
										(346, 195, 'Jaen', 'JAE', '23'),
										(347, 195, 'La Rioja', 'LRI', '26'),
										(348, 195, 'Las Palmas', 'LPA', '35'),
										(349, 195, 'Leon', 'LEO', '24'),
										(350, 195, 'Lleida', 'LLE', '25'),
										(351, 195, 'Lugo', 'LUG', '27'),
										(352, 195, 'Madrid', 'MAD', '28'),
										(353, 195, 'Malaga', 'MAL', '29'),
										(354, 195, 'Melilla', 'MEL', '52'),
										(355, 195, 'Murcia', 'MUR', '30'),
										(356, 195, 'Navarra', 'NAV', '31'),
										(357, 195, 'Ourense', 'OUR', '32'),
										(358, 195, 'Palencia', 'PAL', '34'),
										(359, 195, 'Pontevedra', 'PON', '36'),
										(360, 195, 'Salamanca', 'SAL', '37'),
										(361, 195, 'Santa Cruz de Tenerife', 'SCT', '38'),
										(362, 195, 'Segovia', 'SEG', '40'),
										(363, 195, 'Sevilla', 'SEV', '41'),
										(364, 195, 'Soria', 'SOR', '42'),
										(365, 195, 'Tarragona', 'TAR', '43'),
										(366, 195, 'Teruel', 'TER', '44'),
										(367, 195, 'Toledo', 'TOL', '45'),
										(368, 195, 'Valencia', 'VAL', '46'),
										(369, 195, 'Valladolid', 'VLL', '47'),
										(370, 195, 'Vizcaya', 'VIZ', '48'),
										(371, 195, 'Zamora', 'ZAM', '49'),
										(372, 195, 'Zaragoza', 'ZAR', '50'),
										(373, 11, 'Aragatsotn', 'ARG', 'AG'),
										(374, 11, 'Ararat', 'ARR', 'AR'),
										(375, 11, 'Armavir', 'ARM', 'AV'),
										(376, 11, 'Gegharkunik', 'GEG', 'GR'),
										(377, 11, 'Kotayk', 'KOT', 'KT'),
										(378, 11, 'Lori', 'LOR', 'LO'),
										(379, 11, 'Shirak', 'SHI', 'SH'),
										(380, 11, 'Syunik', 'SYU', 'SU'),
										(381, 11, 'Tavush', 'TAV', 'TV'),
										(382, 11, 'Vayots-Dzor', 'VAD', 'VD'),
										(383, 11, 'Yerevan', 'YER', 'ER'),
										(384, 99, 'Andaman & Nicobar Islands', 'ANI', 'AI'),
										(385, 99, 'Andhra Pradesh', 'AND', 'AN'),
										(386, 99, 'Arunachal Pradesh', 'ARU', 'AR'),
										(387, 99, 'Assam', 'ASS', 'AS'),
										(388, 99, 'Bihar', 'BIH', 'BI'),
										(389, 99, 'Chandigarh', 'CHA', 'CA'),
										(390, 99, 'Chhatisgarh', 'CHH', 'CH'),
										(391, 99, 'Dadra & Nagar Haveli', 'DAD', 'DD'),
										(392, 99, 'Daman & Diu', 'DAM', 'DA'),
										(393, 99, 'Delhi', 'DEL', 'DE'),
										(394, 99, 'Goa', 'GOA', 'GO'),
										(395, 99, 'Gujarat', 'GUJ', 'GU'),
										(396, 99, 'Haryana', 'HAR', 'HA'),
										(397, 99, 'Himachal Pradesh', 'HIM', 'HI'),
										(398, 99, 'Jammu & Kashmir', 'JAM', 'JA'),
										(399, 99, 'Jharkhand', 'JHA', 'JH'),
										(400, 99, 'Karnataka', 'KAR', 'KA'),
										(401, 99, 'Kerala', 'KER', 'KE'),
										(402, 99, 'Lakshadweep', 'LAK', 'LA'),
										(403, 99, 'Madhya Pradesh', 'MAD', 'MD'),
										(404, 99, 'Maharashtra', 'MAH', 'MH'),
										(405, 99, 'Manipur', 'MAN', 'MN'),
										(406, 99, 'Meghalaya', 'MEG', 'ME'),
										(407, 99, 'Mizoram', 'MIZ', 'MI'),
										(408, 99, 'Nagaland', 'NAG', 'NA'),
										(409, 99, 'Orissa', 'ORI', 'OR'),
										(410, 99, 'Pondicherry', 'PON', 'PO'),
										(411, 99, 'Punjab', 'PUN', 'PU'),
										(412, 99, 'Rajasthan', 'RAJ', 'RA'),
										(413, 99, 'Sikkim', 'SIK', 'SI'),
										(414, 99, 'Tamil Nadu', 'TAM', 'TA'),
										(415, 99, 'Tripura', 'TRI', 'TR'),
										(416, 99, 'Uttaranchal', 'UAR', 'UA'),
										(417, 99, 'Uttar Pradesh', 'UTT', 'UT'),
										(418, 99, 'West Bengal', 'WES', 'WE'),
										(419, 101, 'Ahmadi va Kohkiluyeh', 'BOK', 'BO'),
										(420, 101, 'Ardabil', 'ARD', 'AR'),
										(421, 101, 'Azarbayjan-e Gharbi', 'AZG', 'AG'),
										(422, 101, 'Azarbayjan-e Sharqi', 'AZS', 'AS'),
										(423, 101, 'Bushehr', 'BUS', 'BU'),
										(424, 101, 'Chaharmahal va Bakhtiari', 'CMB', 'CM'),
										(425, 101, 'Esfahan', 'ESF', 'ES'),
										(426, 101, 'Fars', 'FAR', 'FA'),
										(427, 101, 'Gilan', 'GIL', 'GI'),
										(428, 101, 'Gorgan', 'GOR', 'GO'),
										(429, 101, 'Hamadan', 'HAM', 'HA'),
										(430, 101, 'Hormozgan', 'HOR', 'HO'),
										(431, 101, 'Ilam', 'ILA', 'IL'),
										(432, 101, 'Kerman', 'KER', 'KE'),
										(433, 101, 'Kermanshah', 'BAK', 'BA'),
										(434, 101, 'Khorasan-e Junoubi', 'KHJ', 'KJ'),
										(435, 101, 'Khorasan-e Razavi', 'KHR', 'KR'),
										(436, 101, 'Khorasan-e Shomali', 'KHS', 'KS'),
										(437, 101, 'Khuzestan', 'KHU', 'KH'),
										(438, 101, 'Kordestan', 'KOR', 'KO'),
										(439, 101, 'Lorestan', 'LOR', 'LO'),
										(440, 101, 'Markazi', 'MAR', 'MR'),
										(441, 101, 'Mazandaran', 'MAZ', 'MZ'),
										(442, 101, 'Qazvin', 'QAS', 'QA'),
										(443, 101, 'Qom', 'QOM', 'QO'),
										(444, 101, 'Semnan', 'SEM', 'SE'),
										(445, 101, 'Sistan va Baluchestan', 'SBA', 'SB'),
										(446, 101, 'Tehran', 'TEH', 'TE'),
										(447, 101, 'Yazd', 'YAZ', 'YA'),
										(448, 101, 'Zanjan', 'ZAN', 'ZA');";
			$db->setQuery($query);
			if(!$db->query())
			{
				echo $img_ERROR.JText :: _('Unable to create table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		$query= "CREATE TABLE IF NOT EXISTS `#__osemsc_email` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `msc_id` int(11) NOT NULL,
							  `subject` text,
							  `body` text,
							  `type` varchar(20) DEFAULT NULL COMMENT '1 for email, 2 for receipt',
							  `params` text,
							  PRIMARY KEY (`id`)
							) ENGINE=MyISAM  AUTO_INCREMENT=1 ;	";
		$db->setQuery($query);
		if(!$db->query())
		{
			echo $img_ERROR.JText :: _('Unable to create table').$BR;
			echo $db->getErrorMsg();
			return false;
		}

		$query= "CREATE TABLE IF NOT EXISTS `#__osemsc_ext` (
							  `id` int(11) NOT NULL COMMENT 'synchronize with osemsc_acl.id',
							  `type` char(30) default NULL,
							  `params` text,
							  UNIQUE KEY `id` (`id`,`type`)
							) ENGINE=MyISAM ;";
		$db->setQuery($query);
		if(!$db->query())
		{
			echo $img_ERROR.JText :: _('Unable to create table').$BR;
			echo $db->getErrorMsg();
			return false;
		}
		$query= "CREATE TABLE IF NOT EXISTS `#__osemsc_location` (
							  `location_id` int(11) NOT NULL auto_increment,
							  `directory_id` int(11) default NULL,
							  `addr1` text COMMENT 'address 1',
							  `addr2` text COMMENT 'address 2',
							  `city` varchar(100) default NULL,
							  `state` varchar(100) default NULL COMMENT 'State ID',
							  `country` varchar(100) default NULL COMMENT 'Country ID',
							  `postcode` varchar(20) default NULL,
							  `telephone` varchar(20) default NULL,
							  `fax` varchar(50) NOT NULL,
							  `contact_name` varchar(100) NOT NULL,
							  `contact_title` varchar(100) NOT NULL,
							  `contact_email` varchar(100) NOT NULL,
							  PRIMARY KEY  (`location_id`)
							) ENGINE=MyISAM AUTO_INCREMENT=1 ;";
		$db->setQuery($query);
		if(!$db->query())
		{
			echo $img_ERROR.JText :: _('Unable to create table').$BR;
			echo $db->getErrorMsg();
			return false;
		}
		$query= "CREATE TABLE IF NOT EXISTS `#__osemsc_member` (
							  `id` int(11) NOT NULL auto_increment,
							  `msc_id` int(11) NOT NULL,
							  `member_id` int(11) NOT NULL,
							  `status` int(1) NOT NULL default '1' COMMENT '0 for end, 1 for active',
							  `eternal` int(1) NOT NULL default '1' COMMENT '1 for true, eternal membership ',
							  `start_date` datetime NOT NULL default '0000-00-00 00:00:00',
							  `expired_date` datetime NOT NULL default '0000-00-00 00:00:00',
							  `notified` tinyint(1) default NULL,
							  `notified2` tinyint(1) default NULL,
							  `notified3` tinyint(1) default NULL,
							  `params` text,
							  PRIMARY KEY  (`id`),
							  UNIQUE KEY `msc_id` (`msc_id`,`member_id`),
							  KEY `msc_id_2` (`msc_id`)
							) ENGINE=MyISAM AUTO_INCREMENT=1;";
		$db->setQuery($query);
		if(!$db->query())
		{
			echo $img_ERROR.JText :: _('Unable to create table').$BR;
			echo $db->getErrorMsg();
			return false;
		}
		$query= "CREATE TABLE IF NOT EXISTS `#__osemsc_member_expired` (
							  `id` int(11) NOT NULL auto_increment,
							  `msc_id` int(11) NOT NULL,
							  `member_id` int(11) NOT NULL,
							  `eternal` int(1) NOT NULL default '1' COMMENT '1 for true, eternal membership ',
							  `start_date` datetime NOT NULL default '0000-00-00 00:00:00',
							  `expired_date` datetime NOT NULL default '0000-00-00 00:00:00',
							  `notified` tinyint(1) default NULL,
							  `params` text,
							  PRIMARY KEY  (`id`),
							  UNIQUE KEY `msc_id` (`msc_id`,`member_id`),
							  KEY `msc_id_2` (`msc_id`)
							) ENGINE=MyISAM AUTO_INCREMENT=1 ;";
		$db->setQuery($query);
		if(!$db->query())
		{
			echo $img_ERROR.JText :: _('Unable to create table').$BR;
			echo $db->getErrorMsg();
			return false;
		}
		$query= "CREATE TABLE IF NOT EXISTS `#__osemsc_member_history` (
							  `id` int(11) NOT NULL auto_increment,
							  `msc_id` int(11) NOT NULL,
							  `member_id` int(11) NOT NULL,
							  `action` varchar(20) NOT NULL COMMENT 'join,cancel,expired,pending',
							  `date` datetime NOT NULL default '0000-00-00 00:00:00',
							  `accumulated` varchar(50) NOT NULL COMMENT 'accumuating the total hours',
							  `params` text,
							  PRIMARY KEY  (`id`),
							  KEY `msc_id` (`msc_id`,`member_id`)
							) ENGINE=MyISAM AUTO_INCREMENT=1 ;";
		$db->setQuery($query);
		if(!$db->query())
		{
			echo $img_ERROR.JText :: _('Unable to create table').$BR;
			echo $db->getErrorMsg();
			return false;
		}

		$query= "CREATE TABLE IF NOT EXISTS `#__osemsc_tax` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `country_3_code` varchar(3) NOT NULL,
							  `state_2_code` varchar(3) NOT NULL DEFAULT 'ALL',
							  `rate` FLOAT( 10, 2 ) NOT NULL DEFAULT '0.00' ,
							  `file_control` varchar(100) DEFAULT NULL,
							  `has_file_control` int(1) NOT NULL DEFAULT '0',
							  `ordering` int(5) NOT NULL DEFAULT '0',
							  `lft` int(11) NOT NULL DEFAULT '0',
							  `rgt` int(11) NOT NULL DEFAULT '0',
							  PRIMARY KEY (`id`),
							  UNIQUE KEY `state_2_code` (`country_3_code`,`state_2_code`),
							  KEY `country_3_code` (`country_3_code`)
							) ENGINE=MyISAM AUTO_INCREMENT=1 ;	";
		$db->setQuery($query);
		if(!$db->query())
		{
			echo $img_ERROR.JText :: _('Unable to create table').$BR;
			echo $db->getErrorMsg();
			return false;
		}
		$query= "CREATE TABLE IF NOT EXISTS `#__osemsc_mtcat` (
							  `cat_id` int(11) NOT NULL AUTO_INCREMENT,
							  `directory_id` int(11) DEFAULT NULL,
							  PRIMARY KEY (`cat_id`)
							) ENGINE=MyISAM AUTO_INCREMENT=1 ;";
		$db->setQuery($query);
		if(!$db->query())
		{
			echo $img_ERROR.JText :: _('Unable to create table').$BR;
			echo $db->getErrorMsg();
			return false;
		}
		$query= "CREATE TABLE IF NOT EXISTS `#__osemsc_mtrel` (
							  `link_id` int(11) NOT NULL,
							  `directory_id` int(11) default NULL,
							  PRIMARY KEY  (`link_id`)
							) ENGINE=MyISAM ;";
		$db->setQuery($query);
		if(!$db->query())
		{
			echo $img_ERROR.JText :: _('Unable to create table').$BR;
			echo $db->getErrorMsg();
			return false;
		}
		$query= "CREATE TABLE IF NOT EXISTS `#__osemsc_order` (
							  `order_id` int(11) NOT NULL auto_increment,
							  `user_id` int(11) NOT NULL default '0',
							  `entry_id` int(11) NOT NULL default '0',
							  `entry_type` varchar(10) NOT NULL default 'msc' COMMENT 'product_id',
							  `order_number` varchar(32) default NULL,
							  `payment_serial_number` varchar(50) NOT NULL,
							  `order_status` varchar(20) default 'pending' COMMENT 'p for pending,can for cancel,c for confirmed',
							  `payment_price` float(10,2) NOT NULL default '0.00',
							  `payment_currency` varchar(10) NOT NULL,
							  `payment_method` varchar(32) default NULL COMMENT 'pp for paypal, gco for Google Checkout, 2co for 2CheckOut, authorize for Authorize.Net',
							  `create_date` datetime default NULL,
							  `payment_mode` varchar(3) NOT NULL default 'm' COMMENT 'a for automaticall, m for manually',
							  `payment_from` varchar(20) NOT NULL default 'system_register',
							  `params` text,
							  `transactions` text NULL,
							  PRIMARY KEY  (`order_id`),
							  UNIQUE KEY `order_number` (`order_number`),
							  KEY `user_id` (`user_id`,`entry_id`)
							) ENGINE=MyISAM AUTO_INCREMENT=1;";
		$db->setQuery($query);
		if(!$db->query())
		{
			echo $img_ERROR.JText :: _('Unable to create table').$BR;
			echo $db->getErrorMsg();
			return false;
		}
		$query= "CREATE TABLE IF NOT EXISTS `#__osemsc_order_item` (
					  `order_item_id` int(11) NOT NULL auto_increment,
					  `order_id` int(11) NOT NULL default '0',
					  `entry_id` int(11) NOT NULL default '0',
					  `entry_type` varchar(10) NOT NULL default 'msc' COMMENT 'product_id',
					  `order_status` varchar(20) default 'pending' COMMENT 'p for pending,can for cancel,c for confirmed,f for failed, i for invalid',
					  `payment_price` float(10,2) NOT NULL default '0.00',
					  `payment_currency` varchar(10) NOT NULL,
					  `create_date` datetime default NULL,
					  `payment_mode` varchar(3) NOT NULL default 'm' COMMENT 'a for automaticall, m for manually',
					  `params` text,
					  PRIMARY KEY  (`order_item_id`),
					  KEY `user_id` (`entry_id`)
					) ENGINE=MyISAM AUTO_INCREMENT=1;";
		$db->setQuery($query);
		if(!$db->query())
		{
			echo $img_ERROR.JText :: _('Unable to create table').$BR;
			echo $db->getErrorMsg();
			return false;
		}
		$query= "CREATE TABLE IF NOT EXISTS `#__osemsc_coupon` (
							  `id` int(11) NOT NULL auto_increment,
							  `title` varchar(50) NOT NULL,
							  `code` text NOT NULL,
							  `type` varchar(20) NOT NULL,
							  `amount` int(10) NOT NULL default '1' COMMENT 'number of times a coupon can be used, -1 means infinited',
							  `amount_infinity` int(1) NOT NULL default '0',
							  `discount` double NOT NULL default '0',
							  `discount_type` varchar(20) NOT NULL default 'rate',
							  `params` text,
							  `data` text COMMENT 'for spare use',
							  PRIMARY KEY  (`id`)
							) ENGINE=MyISAM AUTO_INCREMENT=1 ;";
		$db->setQuery($query);
		if(!$db->query())
		{
			echo $img_ERROR.JText :: _('Unable to create table').$BR;
			echo $db->getErrorMsg();
			return false;
		}
		$query= "CREATE TABLE IF NOT EXISTS `#__osemsc_coupon_user` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  `coupon_id` int(11) NOT NULL DEFAULT '0',
							  `coupon_number` varchar(20) NOT NULL,
							  `msc_id` int(11) NOT NULL,
							  `user_id` int(11) NOT NULL,
							  `paid` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'record whether user has paid for msc with coupon',
							  `params` text,
							  PRIMARY KEY (`id`),
							  KEY `coupon_id` (`coupon_id`,`msc_id`,`user_id`)
							) ENGINE=MyISAM ;";
		$db->setQuery($query);
		if(!$db->query())
		{
			echo $img_ERROR.JText :: _('Unable to create table').$BR;
			echo $db->getErrorMsg();
			return false;
		}
		$query= "CREATE TABLE IF NOT EXISTS `#__osemsc_userinfo` (
							  `user_id` int(11) NOT NULL default '0',
							  `firstname` varchar(100) default NULL,
							  `lastname` varchar(100) default NULL,
							  `primary_contact` int(1) NOT NULL default '1',
							  PRIMARY KEY  (`user_id`)
							) ENGINE=MyISAM; ";
		$db->setQuery($query);
		if(!$db->query())
		{
			echo $img_ERROR.JText :: _('Unable to create table').$BR;
			echo $db->getErrorMsg();
			return false;
		}
		$query= "CREATE TABLE IF NOT EXISTS `#__osemsc_credit` (
							  `member_id` int(11) NOT NULL,
							  `credit` int(11) NOT NULL,
							  `recharge_times` int(11) NOT NULL,
							  `total_consume_amout` int(11) NOT NULL,
							  `params` text NOT NULL,
							  UNIQUE KEY `member_id` (`member_id`)
							) ENGINE=MyISAM ;	";
		$db->setQuery($query);
		if(!$db->query())
		{
			echo $img_ERROR.JText :: _('Unable to create table').$BR;
			echo $db->getErrorMsg();
			return false;
		}
		$query= " CREATE TABLE IF NOT EXISTS `#__osemsc_fields` (
							  `id` int(10) NOT NULL AUTO_INCREMENT,
							  `name` varchar(255) NOT NULL,
							  `type` varchar(255) NOT NULL,
							  `ordering` int(11) DEFAULT '0',
							  `published` tinyint(1) NOT NULL DEFAULT '0',
							  `require` tinyint(1) NOT NULL DEFAULT '0',
							  `params` text,
							  PRIMARY KEY (`id`)
							) ENGINE=MyISAM AUTO_INCREMENT=1 ;";
		$db->setQuery($query);
		if(!$db->query())
		{
			echo $img_ERROR.JText :: _('Unable to create table').$BR;
			echo $db->getErrorMsg();
			return false;
		}
		$query= " CREATE TABLE IF NOT EXISTS `#__osemsc_fields_values` (
							  `id` int(10) NOT NULL AUTO_INCREMENT,
							  `member_id` int(11) NOT NULL,
							  `field_id` int(10) NOT NULL,
							  `value` text NOT NULL,
							  PRIMARY KEY (`id`),
							  KEY `field_id` (`field_id`),
							  KEY `member_id` (`member_id`)
							) ENGINE=MyISAM AUTO_INCREMENT=1 ;";
		$db->setQuery($query);
		if(!$db->query())
		{
			echo $img_ERROR.JText :: _('Unable to create table').$BR;
			echo $db->getErrorMsg();
			return false;
		}
		$query= "CREATE TABLE IF NOT EXISTS `#__osemsc_paymentgateway` (
					  `pgw_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'pgw => payment gateway',
					  `enabled` int(1) NOT NULL DEFAULT '1',
					  `test` int(1) NOT NULL DEFAULT '1',
					  `title` varchar(100) DEFAULT NULL,
					  `folder` varchar(50) NOT NULL,
					  `filename` varchar(100) DEFAULT NULL,
					  `checkout_method` varchar(50) NOT NULL DEFAULT 'api' COMMENT 'api,form',
					  `authorize_method` varchar(50) NOT NULL COMMENT 'ipn,api',
					  `is_cc` int(1) NOT NULL DEFAULT '0' COMMENT 'credit card',
					  `hasIPN` int(1) NOT NULL DEFAULT '0' COMMENT '2 means when using account or individual setting, it will be required.',
					  `ipn_filename` varchar(100) DEFAULT NULL,
					  `description` text NOT NULL,
					  `config` text NOT NULL,
					  `params` text NOT NULL,
					  `v` tinyint(3) NOT NULL DEFAULT '1',
					  PRIMARY KEY (`pgw_id`),
					  KEY `user_id` (`title`),
					  KEY `title` (`title`)
					) ENGINE=MyISAM AUTO_INCREMENT=1 ; ";
		$db->setQuery($query);
		if(!$db->query())
		{
			echo $img_ERROR.JText :: _('Unable to create table').$BR;
			echo $db->getErrorMsg();
			return false;
		}
		$query= "SELECT COUNT(*) FROM `#__osemsc_paymentgateway`";
		$db->setQuery($query);
		$result= $db->loadResult();
		if(empty($result))
		{
			$query= " INSERT INTO `#__osemsc_paymentgateway` (`pgw_id`, `enabled`, `test`, `title`, `folder`, `filename`, `checkout_method`, `authorize_method`, `is_cc`, `hasIPN`, `ipn_filename`, `description`, `config`, `params`,`v`) "." VALUES "."(NULL, 1, 1, 'Paypal', 'paypal', 'paypal', 'form', 'ipn', 0, 1,'paypal_notify.php', '{has_trial:yes,has_subscription:yes}', '', '', 1),"."(NULL, 1, 1, 'Authorize.Net', 'authorize', 'authorize', 'api', 'ipn', 1, 2,'authorizenet_notify.php', '', '', '', 1),"."(NULL, 1, 1, 'Paypal Pro', 'paypal', 'paypalpro', 'api', '', 0, 0, NULL,'', '', '', 1),"."(NULL, 1, 1, 'Paypal Api(Credit Card)', 'paypal', NULL, 'api', '', 1, 0,NULL, '', '', '', 1),"."(NULL, 1, 1, 'ePay.dk', 'epaydk', 'epay', 'api', '', 1, 1,'epaydk_notify.php', '', '', '', 1),"."(NULL, 1, 1, 'eWay', 'eway', 'eway', 'api', 'api', 1, 0, NULL, '', '', '',1),"."(NULL, 1, 1, 'Payment Network', 'payment_network', 'pnw', 'api', '', 1, 0,NULL, '', '', '', 1),"."(NULL, 1, 1, 'Bean Stream', 'beanstream', 'beanstream', 'api', '', 1, 0,NULL, '', '', '', 1);";
			$db->setQuery($query);
			if(!$db->query())
			{
				echo $img_ERROR.JText :: _('Unable to create table').$BR;
				echo $db->getErrorMsg();
				return false;
			}
		}
		
		$query = "CREATE TABLE IF NOT EXISTS `#__ose_activation` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `code` text NOT NULL,
				  `ext` varchar(20) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		$db->setQuery($query);
		if(!$db->query())
		{
			echo $img_ERROR.JText :: _('Unable to create table').$BR;
			echo $db->getErrorMsg();
			return false;
		}
		return true;
	}
	function oseunpack($src, $dest, $file)
	{
		$extractdir= JPath :: clean($dest);
		$archivename= JPath :: clean($src.DS.$file);
		// do the unpacking of the archive
		$result= JArchive :: extract($archivename, $extractdir);
		if($result === false)
		{
			return false;
		}
		else
		{
			if(JFile :: delete($archivename))
			{
				return true;
			}
		}
	}
	function installLanguage($type)
	{
		if ($type =='back')
		{
			$src= JPATH_ADMINISTRATOR.DS.'components'.DS.$this->component.DS.'language'.DS.'en-GB'.DS.'en-GB.'.$this->component.'.ini';
			$dest= JPATH_ADMINISTRATOR.DS.'language'.DS.'en-GB'.DS.'en-GB.'.$this->component.'.ini';
		}
		elseif($type =='backsys')
		{
			$src= JPATH_ADMINISTRATOR.DS.'components'.DS.$this->component.DS.'language'.DS.'en-GB'.DS.'en-GB.'.$this->component.'.sys.ini';
			$dest= JPATH_ADMINISTRATOR.DS.'language'.DS.'en-GB'.DS.'en-GB.'.$this->component.'.sys.ini';
		}
		else
		{
			$src= JPATH_SITE.DS.'components'.DS.$this->component.DS.'language'.DS.'en-GB'.DS.'en-GB.'.$this->component.'.ini';
			$dest= JPATH_SITE.DS.'language'.DS.'en-GB'.DS.'en-GB.'.$this->component.'.ini';
		}
		if(!JFile :: copy($src, $dest))
		{
			echo JText :: _('Unable to copy language file');
			return false;
		}
		else
		{
			return true;
		}
	}
	function installMenuPatch()
	{
		if (JOOMLA30==true)
		{
			$src= JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'crossover'.DS.'joomla30'.DS.'site.php';
		}
		else if (JOOMLA25==true)
		{
			$src= JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'crossover'.DS.'joomla25'.DS.'menu.php';
		}
		else if (JOOMLA17==true)
		{
			$src= JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'crossover'.DS.'joomla17'.DS.'menu.php';
		}
		else
		{
			$src= JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'crossover'.DS.'joomla'.DS.'menu.php';
		}
		if (JOOMLA30==true)
		{
			$dest= JPATH_ROOT.DS.'libraries'.DS.'cms'.DS.'menu'.DS.'site.php';
		}
		else
		{
			$dest= JPATH_ROOT.DS.'includes'.DS.'menu.php';			
		}	
		
		if(!JFile :: copy($src, $dest))
		{
			echo JText :: _('Unable to copy Menu patch file');
			return false;
		}
		else
		{
			return true;
		}
	}
	function installModulePatch()
	{
		if (JOOMLA30==true)
		{
			$src= JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'crossover'.DS.'joomla30'.DS.'helper.php';
		}
		else if (JOOMLA25==true)
		{
			$src= JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'crossover'.DS.'joomla25'.DS.'helper.php';
		}
		else if (JOOMLA17==true)
		{
			$src= JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'crossover'.DS.'joomla17'.DS.'helper.php';
		}
		else
		{
		 	$src= JPATH_SITE.DS.'components'.DS.'com_osemsc'.DS.'crossover'.DS.'joomla'.DS.'helper.php';
		}
		if (JOOMLA30==true)
		{
			$dest= JPATH_SITE.DS.'libraries'.DS.'legacy'.DS.'module'.DS.'helper.php';
		}	
		else
		{
			$dest= JPATH_SITE.DS.'libraries'.DS.'joomla'.DS.'application'.DS.'module'.DS.'helper.php';			
		}

		if(!JFile :: copy($src, $dest))
		{
			echo JText :: _('Unable to copy module patch file');
			return false;
		}
		else
		{
			return true;
		}
	}
	function installViews($step)
	{
		$config= new JConfig();
		$html= '';
		$html .= '<div style="width:100px; float:left;">'.JText :: _('Create Views').'</div>';
		$success = true;
		$result= null;
		$db= JFactory :: getDBO();
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') 
		{
			$query= "SHOW CREATE VIEW `#__osemsc_userinfo_view`";
		}
		else
		{		
			$query= "SHOW TABLE STATUS LIKE '#__osemsc_userinfo_view'";
		}
		$query = OsemscHelper::setQuery($query);
		$db->setQuery($query); 
		$result= $db->loadObjectlist();
				
		if(empty($result))
		{
			$query= "CREATE SQL SECURITY INVOKER VIEW `#__osemsc_userinfo_view` AS select `u`.`id` AS `user_id`,`u`.`name` AS `jname`,`u`.`username` AS `username`,`u`.`email` AS `email`,`u`.`block` AS `block`,`ui`.`firstname` AS `firstname`,`ui`.`lastname` AS `lastname`,`ui`.`primary_contact` AS `primary_contact` FROM (`#__users` `u` join `#__osemsc_userinfo` `ui` on((`u`.`id` = `ui`.`user_id`)));";
			$viewError= array();
			$db->setQuery($query);
			$db->query();
			if(preg_match("/doesn\'t exist/", $db->getErrorMsg()))
			{
				$viewhtml."<div class='setting-msg'>#The following View cannot be created in Joomla, please execute the following SQL through phpmyadmin in your hosting control panel:<br />";
				$sql= str_replace("#__", $config->dbprefix, $query);
				$viewhtml.$sql."</div>";
				$success= false;
			}
		}
		$result= null;
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
		{
		    $query= "SHOW CREATE VIEW `#__osemsc_member_view`";
		}
		else
		{
			$query= "SHOW TABLE STATUS LIKE '#__osemsc_member_view'";			
		}	
		$query = OsemscHelper::setQuery($query);
		$db->setQuery($query);
		$result= $db->loadObjectlist();
		
		if(empty($result))
		{
			$query= "CREATE  SQL SECURITY INVOKER VIEW `#__osemsc_member_view` AS select `mem`.`id` AS `id`,`acl`.`title` AS `msc_name`,`mem`.`msc_id` AS `msc_id`,`mem`.`member_id` AS `member_id`,`mem`.`status` AS `status`,`mem`.`notified` AS `notified`,`mem`.`eternal` AS `eternal`,`mem`.`start_date` AS `start_date`,`mem`.`expired_date` AS `expired_date`,`mem`.`params` AS `memParams`,`u`.`username` AS `username`,`u`.`name` AS `name`,`u`.`email` AS `email`,`u`.`registerDate` AS `registerDate`,`u`.`params` AS `userParams` from ((`#__osemsc_member` `mem` join `#__users` `u` on((`u`.`id` = `mem`.`member_id`))) join `#__osemsc_acl` `acl` on((`acl`.`id` = `mem`.`msc_id`)));";
			$db->setQuery($query);
			$db->query();
			if(preg_match("/doesn\'t exist/", $db->getErrorMsg()))
			{
				$viewhtml."<div class='setting-msg'>#The following View cannot be created in Joomla, please execute the following SQL through phpmyadmin in your hosting control panel:<br />";
				$sql= str_replace("#__", $config->dbprefix, $query);
				$viewhtml.$sql."</div>";
				$success= false;
			}
		}
		/*
		$result= null;
		$query= "SHOW CREATE VIEW `#__osemsc_member_view`";
		$db->setQuery($query);
		$result= $db->loadResult();
		if(empty($result))
		{
			$query[0]= "SELECT * FROM #__osemsc_member_view";
			$createquery[0]= "CREATE SQL SECURITY INVOKER VIEW `#__osemsc_member_view` AS select `mem`.`id` AS `id`,`acl`.`title` AS `msc_name`,`mem`.`msc_id` AS `msc_id`,`mem`.`member_id` AS `member_id`,`mem`.`status` AS `status`,`mem`.`notified` AS `notified`,`mem`.`eternal` AS `eternal`,`mem`.`start_date` AS `start_date`,`mem`.`expired_date` AS `expired_date`,`mem`.`params` AS `memParams`,`u`.`username` AS `username`,`u`.`name` AS `name`,`u`.`email` AS `email`,`u`.`registerDate` AS `registerDate`,`u`.`params` AS `userParams` from ((`#__osemsc_member` `mem` join `#__users` `u` on((`u`.`id` = `mem`.`member_id`))) join `#__osemsc_acl` `acl` on((`acl`.`id` = `mem`.`msc_id`)));";
			$query[1]= "SELECT * FROM #__osemsc_userinfo_view";
			$createquery[1]= "CREATE SQL SECURITY INVOKER VIEW `#__osemsc_userinfo_view` AS select `u`.`id` AS `user_id`,`u`.`name` AS `jname`,`u`.`username` AS `username`,`u`.`email` AS `email`,`u`.`block` AS `block`,`ui`.`firstname` AS `firstname`,`ui`.`lastname` AS `lastname`,`ui`.`primary_contact` AS `primary_contact` FROM (`#__users` `u` join `#__osemsc_userinfo` `ui` on((`u`.`id` = `ui`.`user_id`)));";
			
			$config= new JConfig();
			$result= true;
			$viewhtml= '';
			for($i= 0; $i < 2; $i++)
			{
				$db->setQuery($query[$i]);
				$db->query();
				if(preg_match("/doesn\'t exist/", $db->getErrorMsg()))
				{
					$viewhtml."<div class='setting-msg'>#The following View cannot be created in Joomla, please execute the following SQL through phpmyadmin in your hosting control panel:<br />";
					$sql= str_replace("#__", $config->dbprefix, $createquery[$i]);
					$viewhtml.$sql."</div>";
					$result= false;
				}
			}
		}
		*/
		
		if($success == true)
		{
			$html .= $this->successStatus;
			$autoSubmit= $this->getAutoSubmitFunction();
			//$form = $this->getInstallForm(5);
			$message= $autoSubmit.$html;
			$status= true;
		}
		else
		{
			$html .= $this->failedStatus;
			$html .= $viewhtml;
			$errorMsg= $this->getErrorMessage($step, $step);
			$message= $html.$errorMsg;
			$status= false;
			$step= $step -1;
		}
		$drawdata= new stdClass();
		$drawdata->message= $message;
		$drawdata->status= true;
		$drawdata->step= $step;
		$drawdata->title= JText :: _('CREATING VIEWS');
		$drawdata->install= 1;
		return $drawdata;
	}
	function clearInstallation($step)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$html= '';
		$zip= array();
		$zip[]= $this->backendPath.'admin.zip';
		$zip[]= $this->backendPath.'cpuMSC.zip';
		$zip[]= $this->backendPath.'com_cpu_admin.zip';
		$zip[]= $this->backendPath.'com_cpu_site.zip';
		$zip[]= $this->frontendPath.'site.zip';
		$zip[]= $this->backendPath.'ose_plugins.zip';
		$result= true;
		foreach($zip as $z)
		{
			$html .= '<div style="width:500px; float:left;">'.JText :: _('Clearing file').' '.$z.'</div>';
			$result= JFile :: delete($z);
			if($result == true)
			{
				$html .= $this->successStatus;
				$autoSubmit= $this->getAutoSubmitFunction();
				//$form = $this->getInstallForm(5);
				$message= $autoSubmit.$html;
				$status= true;
			}
			else
			{
				$html .= $this->failedStatus;
				$errorMsg= $this->getErrorMessage($step, $step);
				$message= $html.$errorMsg;
				$status= false;
				$step= $step -1;
			}
		}
		$f= $this->backendPath.'osePlugins'.DS;
		$html .= '<div style="width:500px; float:left;">'.JText :: _('Clearing folder').' '.$z.'</div>';
		$result= JFolder :: delete($f);
		if($result == true)
		{
			$html .= $this->successStatus;
			$autoSubmit= $this->getAutoSubmitFunction();
			//$form = $this->getInstallForm(5);
			$message= $autoSubmit.$html;
			$status= true;
		}
		else
		{
			$html .= $this->failedStatus;
			$errorMsg= $this->getErrorMessage($step, $step);
			$message= $html.$errorMsg;
			$status= false;
			$step= $step -1;
		}
		$drawdata= new stdClass();
		$drawdata->message= $message;
		$drawdata->status= true;
		$drawdata->step= $step;
		$drawdata->title= JText :: _('CREATING VIEWS');
		$drawdata->install= 1;
		return $drawdata;
	}
	function installationComplete($step)
	{
		$cache= JFactory :: getCache();
		$cache->clean();
		$version= OSEMSCVERSION;
		$file= dirname(__FILE__).DS.'installer.dummy.ini';
		if(JFile :: exists($file) && JFile :: delete($file))
		{
			$html= '';
			$html .= '<div style="margin: 30px 0; padding: 10px; background: #edffb7; border: solid 1px #8ba638; width: 50%; -moz-border-radius: 5px; -webkit-border-radius: 5px;">
						<div style="background: #edffb7 url(templates/khepri/images/toolbar/icon-32-apply.png) no-repeat 0 0;width: 32px; height: 32px; float: left; margin-right: 10px;"></div>
						<h3 style="padding: 0; margin: 0 0 5px;">Installation has been completed</h3></div>';
		}
		else
		{
			$html= '<div></div>';
			$html .= '<div style="margin: 30px 0; padding: 10px; background: #edffb7; border: solid 1px #8ba638; width: 50%; -moz-border-radius: 5px; -webkit-border-radius: 5px;">
						<div style="background: #edffb7 url(templates/khepri/images/toolbar/icon-32-apply.png) no-repeat 0 0;width: 32px; height: 32px; float: left; margin-right: 10px;"></div>
						<h3 style="padding: 0; margin: 0 0 5px;">Installation has been completed</h3>However we were unable to remove the file <b>installer.dummy.ini</b> located in the '.dirname(__FILE__).' folder. Please remove it manually in order to completed the installation.</div>';
		}
		ob_start();
?>

		<div style="margin: 30px 0; padding: 10px; background: #fbfbfb; border: solid 1px #ccc; width: 50%; -moz-border-radius: 5px; -webkit-border-radius: 5px;">
			<h3 style="color: red;">IMPORTANT!!</h3>
			<div>Before you begin, you might want to take a look at the following documentations first</div>
			<ul style="background: none;padding: 0; margin-left: 15px;">
				<li style="background: none;padding: 0;margin:0;"><a href="http://wiki.opensource-excellence.com/index.php?title=Documentation_-_OSE_Membership_5#Create_a_membership" target="_blank">Creating Membership</a></li>
				<li style="background: none;padding: 0;margin:0;"><a href="http://wiki.opensource-excellence.com/index.php?title=Documentation_-_OSE_Membership_5#Membership_email_template" target="_blank">Creating email templates.</a></li>
				<li style="background: none;padding: 0;margin:0;"><a href="http://wiki.opensource-excellence.com/index.php?title=Documentation_-_OSE_Membership_5#Payment_Mode_-_Manual_Renewing_or_Automatically_Renewing.3F" target="_blank">Payment Mode</a></li>
				<li style="background: none;padding: 0;margin:0;"><a href="http://wiki.opensource-excellence.com/index.php?title=FAQ_-_OSE_Membership_5#Paypal" target="_blank">Setting up Paypal</a></li>
			</ul>
			<div>You can read the full documentation at <a href="http://wiki.opensource-excellence.com" target="_blank">OSE Wiki Website</a></div>
		</div>

	<?php

		$content= ob_get_contents();
		ob_end_clean();
		$html .= $content;
		//$form = $this->getInstallForm(0, 0);
		$message= $html;
		$drawdata= new stdClass();
		$drawdata->message= $message;
		$drawdata->status= true;
		$drawdata->step= $step;
		$drawdata->title= JText :: _('INSTALLATION COMPLETED');
		$drawdata->install= 0;
		return $drawdata;
	}
	function getErrorMessage($error= "", $extraInfo= "")
	{
		switch($error)
		{
			case 0 :
				$errorWarning= $error.'-'.$extraInfo.' : '.JText :: _('The operation is invalid');
				break;
			case 1 :
				$errorWarning= $error.'-'.$extraInfo.' : '.JText :: _('The file is missing');
				break;
			case 2 :
				$errorWarning= $error.'-'.$extraInfo.' : '.JText :: _('OSE BACKEND EXTRACT FAILED WARN');
				break;
			case 3 :
				$errorWarning= $error.'-'.$extraInfo.' : '.JText :: _('OSE CPU INSTALL FAILED');
				break;
			case 4 :
				$errorWarning= $error.'-'.$extraInfo.' : '.JText :: _('OSE FRONTEND EXTRACT FAILED WARN');
				break;
			case 5 :
				$errorWarning= $error.'-'.$extraInfo.' : '.JText :: _('Error creating OSE tables');
				break;
			case 6 :
				$errorWarning= $error.'-'.$extraInfo.' : '.JText :: _('Error creating OSE tables');
				break;
			case 7 :
				$errorWarning= $error.'-'.$extraInfo.' : '.JText :: _('Error fixing OSE table integrity');
				break;
			case 8 :
				$errorWarning= $error.'-'.$extraInfo.' : '.JText :: _('Error creating Database Views');
				break;
			case 101 :
				$errorWarning= $error.' : '.JText :: _('PHP version is lower than 5.2, your version is'.' '.$extraInfo);
				break;
			default :
				$error=(!empty($error)) ? $error : '99';
				$errorWarning= $error.'-'.$extraInfo.' : '.JText :: _('UNEXPECTED ERROR WARN');
				break;
		}
		ob_start();
?>
		<div style="font-weight: 700; color: red; padding-top:10px">
			<?php echo $errorWarning; ?>
		</div>
		<div id="communityContainer" style="margin-top:10px">
			<div><?php echo JText::_('OSE INSTALLATION ERROR HELP'); ?></div>
			<div><a href="http://wiki.opensource-excellence.com/index.php?title=Trouble_Shooting_-_OSE_Membership_5">http://wiki.opensource-excellence.com/index.php?title=Trouble_Shooting_-_OSE_Membership_5</a></div>
		</div>
		<?php

		$errorMsg= ob_get_contents();
		@ ob_end_clean();
		return $errorMsg;
	}
}
class oseInstallerVerifier
{
	var $template;
	var $dbhelper;
	function __construct()
	{
		require_once(dirname(__FILE__).DS.'installer.template.php');
		$this->template= new oseInstallerTemplate();
	}
	function isLatestFriendTable()
	{
		$fields= $this->dbhelper->_isExistTableColumn('#__community_users', 'friendcount');
		return $fields;
	}
	function isLatestGroupMembersTable()
	{
		$fields= $this->dbhelper->_getFields('#__community_groups_members');
		$result= array();
		if(array_key_exists('permissions', $fields))
		{
			if($fields['permissions'] == 'varchar')
			{
				return false;
			}
		}
		return true;
	}
	function isPhotoPrivacyUpdated()
	{
		return $this->dbhelper->checkPhotoPrivacyUpdated();
	}
	function isLatestGroupTable()
	{
		$fields= $this->dbhelper->_getFields();
		if(!array_key_exists('membercount', $fields))
		{
			return false;
		}
		if(!array_key_exists('wallcount', $fields))
		{
			return false;
		}
		if(!array_key_exists('discusscount', $fields))
		{
			return false;
		}
		return true;
	}
	/**
	 * Method to check if the GD library exist
	 *
	 * @returns boolean	return check status
	 **/
	function testImage()
	{
		$msg= '
					<style type="text/css">
					.Yes {
						color:#46882B;
						font-weight:bold;
					}
					.No {
						color:#CC0000;
						font-weight:bold;
					}
					.jomsocial_install tr {

					}
					.jomsocial_install td {
						color: #888;
						padding: 3px;
					}
					.jomsocial_install td.item {
						color: #333;
					}
					</style>
					<div class="install-body" style="background: #fbfbfb; border: solid 1px #ccc; -moz-border-radius: 5px; -webkit-border-radius: 5px; padding: 20px; width: 50%;">
						<p>If any of these items are not supported (marked as <span class="No">No</span>), your system does not meet the requirements for installation. Some features might not be available. Please take appropriate actions to correct the errors.</p>
							<table class="content jomsocial_install" style="width: 100%; background">
								<tbody>';
		// @rule: Test for JPG image extensions
		$type= 'JPEG';
		if(function_exists('imagecreatefromjpeg'))
		{
			$msg .= $this->template->testImageMessage($type, true);
		}
		else
		{
			$msg .= $this->template->testImageMessage($type, false);
		}
		// @rule: Test for png image extensions
		$type= 'PNG';
		if(function_exists('imagecreatefrompng'))
		{
			$msg .= $this->template->testImageMessage($type, true);
		}
		else
		{
			$msg .= $this->template->testImageMessage($type, false);
		}
		// @rule: Test for gif image extensions
		$type= 'GIF';
		if(function_exists('imagecreatefromgif'))
		{
			$msg .= $this->template->testImageMessage($type, true);
		}
		else
		{
			$msg .= $this->template->testImageMessage($type, false);
		}
		/*
		$type= 'FINFO_OPEN';
		if(function_exists('finfo_open'))
		{
			$msg .= $this->template->testImageMessage($type, true);
		}
		else
		{
			$msg .= $this->template->testImageMessage($type, false);
		}
		*/
		$msg .= '
								</tbody>
							</table>

					</div>';
		return $msg;
	}
	function checkFileExist($file)
	{
		return file_exists($file);
	}
}
?>