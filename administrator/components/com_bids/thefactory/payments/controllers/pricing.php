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

class JTheFactoryPricingController extends JTheFactoryController
{
    var $name='Pricing';
    var $_name='Pricing';
	function __construct()
	{
       parent::__construct('payments');
       JHTML::stylesheet("administrator/components/".APP_EXTENSION."/thefactory/payments/css/payments.css");
       JHtml::addIncludePath($this->basepath.DS.'html');
    }
    function execute($task)
    {
        $item=JRequest::getString('item');
        if ($item)
        {
            $controllername='J'.APP_PREFIX.'Admin'.ucfirst($item).'Controller';
            require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'pricing'.DS.strtolower($item).DS.'controllers'.DS.'admin.php' );
            $controller=new $controllername();
            $res=$controller->execute($task);
            $this->setRedirect($controller->redirect,$controller->message,$controller->messageType);
            return $res;
        }else{
            return parent::execute($task);
        }
    }
    function Listing()
    {
        $model= JModel::getInstance('Pricing','JTheFactoryModel');

        $rows=$model->getPricingList();
        $view=$this->getView('pricing');
        $view->assign('pricing',$rows);
        $view->display('list');
        
    }
    function Toggle()
    {
        $item=JRequest::getString('pricingitem');
        $model= JModel::getInstance('Pricing','JTheFactoryModel');
        $msg=$model->toggle($item);
        $this->setRedirect("index.php?option=".APP_EXTENSION."&task=pricing.listing",$msg);
    }
    function Install()
    {
        $view=$this->getView('pricing');
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
             $this->setRedirect("index.php?option=".APP_EXTENSION."&task=pricing.install");
 			return true;
 		}

 		// Make sure that zlib is loaded so that the package can be unpacked
 		if (!extension_loaded('zlib')) {
 			JError::raiseWarning('', JText::_('COM_BIDS_COM_INSTALLER_MSG_INSTALL_WARNINSTALLZLIB'));
             $this->setRedirect("index.php?option=".APP_EXTENSION."&task=pricing.install");
 			return true;
 		}

 		// If there is no uploaded file, we have a problem...
 		if (!is_array($userfile)) {
 			JError::raiseWarning('', JText::_('COM_BIDS_COM_INSTALLER_MSG_INSTALL_NO_FILE_SELECTED'));
             $this->setRedirect("index.php?option=".APP_EXTENSION."&task=pricing.install");
 			return true;
 		}

 		// Check if there was a problem uploading the file.
 		if ($userfile['error'] || $userfile['size'] < 1) {
 			JError::raiseWarning('', JText::_('COM_BIDS_COM_INSTALLER_MSG_INSTALL_WARNINSTALLUPLOADERROR'));
             $this->setRedirect("index.php?option=".APP_EXTENSION."&task=pricing.install");
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

 		$package = JTheFactoryPricingHelper::unpackPricingPack($tmp_dest);
 		// Was the package unpacked?
 		if (!$package) {
 			JError::raiseWarning('', JText::_('COM_BIDS_COM_INSTALLER_UNABLE_TO_FIND_INSTALL_PACKAGE'));
             $this->setRedirect("index.php?option=".APP_EXTENSION."&task=pricing.install");
 			return true;
 		}

        JTheFactoryPricingHelper::installPricingPack($package['dir']);
		if (!is_file($package['packagefile'])) {
			$config = JFactory::getConfig();
			$package['packagefile'] = $config->get('tmp_path') . '/' . $package['packagefile'];
		}

		JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
        $this->setRedirect("index.php?option=".APP_EXTENSION."&task=pricing.listing",JText::_("FACTORY_PRICING_ITEM_INSTALLED"));

    }
}
