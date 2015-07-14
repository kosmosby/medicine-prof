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
 * @subpackage: custom_fields
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

class FieldValidator_numeric extends FactoryFieldValidator
{
    var $name='Numeric Validator';
    var $classname='numeric';
    var $errormessages=array();

    function validateValue($value,$params=null)
    {
        $this->errormessages=array();
        $result=is_numeric($value);
        if (!$result){
            $this->errormessages[]=JText::_("FACTORY_NUMERIC_FIELD_IS_NOT_VALID");
            return false;
        }
        return true;
    }

    function validateJS()
    {
        return '';
    }

}
