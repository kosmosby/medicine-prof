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
 * @subpackage: config
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
jimport('joomla.application.component.helper');

class JTheFactoryModelConfig extends JModel
{
    var $context='config';
    var $formxml=null;

	function __construct($config)
	{
        parent::__construct($config);
        $this->context=APP_EXTENSION."_config.";
        $this->formxml=$config['formxml'];
	}
	function getDataFromRequest()
    {
        jimport('joomla.form.form');
        $data=JRequest::get('post',JREQUEST_ALLOWHTML);
        unset($data['option']);
        unset($data['task']);
        foreach($data as $k=>$d) {
            if(0===strpos($k,'_')) {
                unset($data[$k]);
            }
        }

        $form=JForm::getInstance('config',$this->formxml);
        $fields=$form->getFieldset();
        foreach($fields as $field) {
            if ($field->type=='Checkbox') {
                $data[$field->name]=array_key_exists($field->name,$data) ? intval($data[$field->name]) : 0;
            }
        }
        return $data;
    }
	function save($data)
    {
		// Get the previous configuration.
		if (is_object($data))
        {
            $data = JArrayHelper::fromObject($data);
        }

		$prev = JTheFactoryHelper::getConfig();
		$prev = JArrayHelper::fromObject($prev);
		$data = array_merge($prev, $data);

		$configfile= JTheFactoryAdminHelper::getConfigFile();

		$config = new JRegistry('config');
		$config->loadArray($data); 		

        jimport('joomla.filesystem.path');
        jimport('joomla.filesystem.file');
        jimport('joomla.client.helper');

		// Get the new FTP credentials.
		$ftp = JClientHelper::getCredentials('ftp', true);

		// Attempt to make the file writeable if using FTP.
		if (!$ftp['enabled'] && JPath::isOwner($configfile) && !JPath::setPermissions($configfile, '0644')) {
			JError::raiseNotice(101, JText::_('FACTORY_SETTINGS_FILE_IS_NOT_WRITABLE'));
		}

		// Attempt to write the configuration file as a PHP class named JConfig.
		$configString = $config->toString('PHP', array('class' => ucfirst(APP_PREFIX)."Config", 'closingtag' => false));
		if (!JFile::write($configfile, $configString)) {
			JError::raiseWarning(101, JText::_('FACTORY_SETTINGS_FILE_WRITE_FAILED'));
			return false;
		}

		// Attempt to make the file unwriteable if using FTP.
		if (!$ftp['enabled'] && JPath::isOwner($configfile) && !JPath::setPermissions($configfile, '0444')) {
			JError::raiseNotice(101, JText::_('FACTORY_SETTINGS_FILE_IS_NOT_WRITABLE'));
		}

		return true;
    }
}
