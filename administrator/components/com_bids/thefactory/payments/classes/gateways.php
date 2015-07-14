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

class TheFactoryPaymentGateway extends JObject
{
    var $name=null;
    var $fullname=null;
    var $formxml=null;
    var $pluginfolder=null;
    function __construct()
    {
        $MyApp= JTheFactoryApplication::getInstance();
        $this->formxml=$MyApp->app_path_admin.'payments'.DS.'plugins'.DS.'gateways'.DS.strtolower($this->name).DS.'form.xml';
        $this->pluginfolder=$MyApp->app_path_admin.'payments'.DS.'plugins'.DS.'gateways'.DS.strtolower($this->name);
        $lang= JFactory::getLanguage();
        $lang->load('thefactory.gateway.'.$this->name);

    }
    function getLogo()
    {
        $uri=JURI::base();
        $uri.=(substr(strrev($uri),0,13)==strrev('administrator'))?"":"administrator/";
        return $uri."components/".APP_EXTENSION."/thefactory/payments/plugins/gateways/".strtolower($this->name)."/logo.png";
    }
    function saveAdminForm()
    {
        $data=JRequest::get('post',JREQUEST_ALLOWHTML);
        unset($data['option']);
        unset($data['task']);
        unset($data['classname']);
        unset($data['id']);
        $config = new JRegistry($data);
        $params= $config->toString('INI');
        $model= JModel::getInstance('Gateways','JTheFactoryModel');
        $model->saveGatewayParams($this->name,$params);

    }
    function showAdminForm()
    {
        $model= JModel::getInstance('Gateways','JTheFactoryModel');
        $params=$model->loadGatewayParams($this->name);

        jimport('joomla.form.form');
        $form=JForm::getInstance($this->name,$this->formxml);
        $form->bind($params->toArray());

        $fieldsets=$form->getFieldsets();

        if (file_exists($this->pluginfolder.DS.'logo.png'))
            echo "<div style='padding:15px;'><img src='".$this->getLogo()."' border='0'></div>";
        echo "<form name='adminForm' action='index.php' method='post'>";
        echo "<input name='option' type='hidden' value='".APP_EXTENSION."'>";
        echo "<input name='task' type='hidden' value='gateways.save'>";
        echo "<input name='classname' type='hidden' value='{$this->name}'>";
        if (count($fieldsets))
            foreach($fieldsets as $fieldset)
            {
                $fields=$form->getFieldset((string)$fieldset->name);
                $this->showFieldSet($fields,(string)$fieldset->name);
            }
        else
        {
            $fields=$form->getFieldset();
            $this->showFieldSet($fields);
        }
        echo "</form>";
        self::setToolbar();
    }
    function getPaymentForm($order,$items,$urls,$shipping=null,$tax=null)
    {
        //has to be implemented in each gateway
        return;
    }
    function processIPN()
    {
        //has to be implemented in each gateway
        return;
    }
    function processTask()
    {
        //entry point for the "gateway" task of the paymentsprocessor controller
        //see bank wire gateway for sample
        return;
    }
    private function showFieldSet($fields,$setname=null)
    {
        if ($setname) echo "<fieldset class='adminform'><legend>{$setname}</legend>";
        echo "<table width='100%'>";
        foreach($fields as $field)
        {
            echo "
                    <tr>
                        <td class='paramlist_key' width='300' valign='top'>{$field->label}</td>
                        <td>{$field->input}</td>
                    </tr>
            ";            
        }
        echo "</table>";
        if ($setname) echo "</fieldset>";
    }
    private function setToolbar()
    {
        JToolBarHelper::save('gateways.save');
        JToolBarHelper::cancel('gateways.listing');

    }
}
