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

class JTheFactoryConfigController extends JTheFactoryController
{
    var $name='Config';
    var $_name='Config';
    var $formxml=null;
	function __construct()
	{
        $MyApp= JTheFactoryApplication::getInstance();
       $this->formxml=JPATH_ROOT.DS."administrator".DS."components".DS.APP_EXTENSION.DS. $MyApp->getIniValue('configxml');
       parent::__construct();

    }

    function display()
    {
        jimport('joomla.form.form');
        $form=JForm::getInstance('config',$this->formxml);
        $cfg= JTheFactoryHelper::getConfig();
        $data	= JArrayHelper::fromObject($cfg);
        $form->bind($data);
        $groups=JTheFactoryConfigHelper::getFieldGroups($this->formxml);
        JTheFactoryEventsHelper::triggerEvent('onDisplaySettings',array($form,$groups,$data));

        $view=$this->getView('settings');
        $view->assignRef('groups',$groups);
        $view->assignRef('form',$form);
        $view->assignRef('formxml',$this->formxml);
        $view->display();
    }
    function SaveSettings()
    {
        JTheFactoryEventsHelper::triggerEvent('onBeforeSaveSettings');

        $model= JModel::getInstance('Config','JTheFactoryModel',array('formxml'=>$this->formxml));

        $data=$model->getDataFromRequest();
        if ($model->save($data)) JError::raiseNotice(101,JText::_("FACTORY_SETTINGS_SAVED"));

        JTheFactoryEventsHelper::triggerEvent('onAfterSaveSettings');
        $this->setRedirect("index.php?option=".APP_EXTENSION."&task=config.display");
    }
}
?>
