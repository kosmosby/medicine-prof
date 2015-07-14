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

class FactoryFieldValidator
{
    var $name='';
    var $classname=''; 
    var $errormessages=array();
    
    function validateValue($value,$params=null)
    {
        return true;
    }    
    
    function validateJS()
    {
        
        return '';
    }
    function getParams()
    {
        return null;
    }
    
}

?>
