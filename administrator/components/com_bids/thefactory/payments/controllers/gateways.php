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
 * @subpackage: payments
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class JTheFactoryGatewaysController extends JTheFactoryController
{
    var $name='Gateways';
    var $_name='Gateways';
	function __construct()
	{
       parent::__construct('payments');
       JHtml::addIncludePath($this->basepath.DS.'html');
    }

    function Listing()
    {
        $model= JModel::getInstance('Gateways','JTheFactoryModel');

        $rows=$model->getGatewayList(false);
        $view=$this->getView('gateways');
        $view->assign('gateways',$rows);
        $view->display('list');
    }
    function Toggle()
    {
        $ids=JRequest::getVar('id');
        $model= JModel::getInstance('Gateways','JTheFactoryModel');

        if (!is_array($ids))
            $ids=array($ids);
        foreach($ids as $id)
            $msg=$model->toggle($id);

        $this->setRedirect("index.php?option=".APP_EXTENSION."&task=gateways.listing",$msg);
    }
    function SetDefault()
    {
        $id=JRequest::getVar('id');
        $model= JModel::getInstance('Gateways','JTheFactoryModel');

        if (is_array($id))
            $id=$id[0];

        $msg=$model->setdefault($id);

        $this->setRedirect("index.php?option=".APP_EXTENSION."&task=gateways.listing",$msg);

    }
    private function getGatewayObject()
    {
        $id=JRequest::getVar('id');
        if (is_array($id))
            $id=$id[0];
        if ($id)
        {
            $table= JTable::getInstance('GatewaysTable','JTheFactory');
            $table->load($id);

            $classname=$table->classname;
        }else
            $classname=JRequest::getVar('classname');


        $model= JModel::getInstance('Gateways','JTheFactoryModel');
        return $model->getGatewayObject($classname);
    }
    function Edit()
    {

        $gateway=$this->getGatewayObject();

        $gateway->showAdminForm();

    }
    function Save()
    {
        $gateway=$this->getGatewayObject();

        $gateway->saveAdminForm();

        $this->setRedirect("index.php?option=".APP_EXTENSION."&task=gateways.listing",JText::_('FACTORY_CONFIGURATION_SAVED'));
    }
    function Install()
    {
        $view=$this->getView('gateways');
        $view->display('install');

    }
    function doUpload()
    {
        $userfile = JRequest::getVar('pack', null, 'files', 'array');
        $lang= JFactory::getLanguage();
        $lang->load('com_installer');
 		// Make sure that file uploads are enabled in php
 		if (!(bool) ini_get('file_uploads')) {
 			JError::raiseWarning('', JText::_('COM_BIDS_COM_INSTALLER_MSG_INSTALL_WARNINSTALLFILE'));
             $this->setRedirect("index.php?option=".APP_EXTENSION."&task=gateways.install");
 			return true;
 		}

 		// Make sure that zlib is loaded so that the package can be unpacked
 		if (!extension_loaded('zlib')) {
 			JError::raiseWarning('', JText::_('COM_BIDS_COM_INSTALLER_MSG_INSTALL_WARNINSTALLZLIB'));
             $this->setRedirect("index.php?option=".APP_EXTENSION."&task=gateways.install");
 			return true;
 		}

 		// If there is no uploaded file, we have a problem...
 		if (!is_array($userfile)) {
 			JError::raiseWarning('', JText::_('COM_BIDS_COM_INSTALLER_MSG_INSTALL_NO_FILE_SELECTED'));
             $this->setRedirect("index.php?option=".APP_EXTENSION."&task=gateways.install");
 			return true;
 		}

 		// Check if there was a problem uploading the file.
 		if ($userfile['error'] || $userfile['size'] < 1) {
 			JError::raiseWarning('', JText::_('COM_BIDS_COM_INSTALLER_MSG_INSTALL_WARNINSTALLUPLOADERROR'));
             $this->setRedirect("index.php?option=".APP_EXTENSION."&task=gateways.install");
 			return true;
 		}
 		// Build the appropriate paths
 		$config		= JFactory::getConfig();
 		$tmp_dest	= $config->get('tmp_path') . '/' . $userfile['name'];
 		$tmp_src	= $userfile['tmp_name'];

 		// Move uploaded file
 		jimport('joomla.filesystem.file');
 		$uploaded = JFile::upload($tmp_src, $tmp_dest);
   		jimport('joomla.installer.helper');

 		$package = JTheFactoryGatewaysHelper::unpackGatewayPack($tmp_dest);
 		// Was the package unpacked?
 		if (!$package) {
 			JError::raiseWarning('', JText::_('COM_BIDS_COM_INSTALLER_UNABLE_TO_FIND_INSTALL_PACKAGE'));
             $this->setRedirect("index.php?option=".APP_EXTENSION."&task=gateways.install");
 			return true;
 		}

        JTheFactoryGatewaysHelper::installGatewayPack($package['dir']);
		if (!is_file($package['packagefile'])) {
			$config = JFactory::getConfig();
			$package['packagefile'] = $config->get('tmp_path') . '/' . $package['packagefile'];
		}

		JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
        $this->setRedirect("index.php?option=".APP_EXTENSION."&task=gateways.listing",JText::_("FACTORY_PAYMENT_GATEWAY_INSTALLED"));

    }


}
