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

class FieldValidator_email extends FactoryFieldValidator
{
    var $name='Email Validator';
    var $classname='email'; 
    var $errormessages=array();
    
    function validateValue($value,$params=null)
    {
        $this->errormessages=array();
        
		// Split the email into a local and domain
		$atIndex	= strrpos($value, "@");
		$domain		= substr($value, $atIndex+1);
		$local		= substr($value, 0, $atIndex);
        if (empty($value))
            return true;
		// Check Length of domain
		$domainLen	= strlen($domain);
		if ($domainLen < 1 || $domainLen > 255) 
        {
            $this->errormessages[]=JText::_("FACTORY_EMAIL_FIELD_IS_NOT_VALID");            
			return false;
		}

		// Check the local address
		// We're a bit more conservative about what constitutes a "legal" address, that is, A-Za-z0-9!#$%&\'*+/=?^_`{|}~-
		$allowed	= 'A-Za-z0-9!#&*+=?_-';
		$regex		= "/^[$allowed][\.$allowed]{0,63}$/";
		if ( ! preg_match($regex, $local) ) {
            $this->errormessages[]=JText::_("FACTORY_EMAIL_FIELD_IS_NOT_VALID");            
			return false;
		}

		// No problem if the domain looks like an IP address, ish
		$regex		= '/^[0-9\.]+$/';
		if ( preg_match($regex, $domain)) {
            $this->errormessages[]=JText::_("FACTORY_EMAIL_FIELD_IS_NOT_VALID");            
			return true;
		}

		// Check Lengths
		$localLen	= strlen($local);
		if ($localLen < 1 || $localLen > 64) {
            $this->errormessages[]=JText::_("FACTORY_EMAIL_FIELD_IS_NOT_VALID");            
			return false;
		}

		// Check the domain
		$domain_array	= explode(".", $domain);
		$regex		= '/^[A-Za-z0-9-]{0,63}$/';
		foreach ($domain_array as $domain ) {

			// Must be something
			if ( ! $domain ) {
                $this->errormessages[]=JText::_("FACTORY_EMAIL_FIELD_IS_NOT_VALID");            
				return false;
			}

			// Check for invalid characters
			if ( ! preg_match($regex, $domain) ) {
                $this->errormessages[]=JText::_("FACTORY_EMAIL_FIELD_IS_NOT_VALID");            
				return false;
			}

			// Check for a dash at the beginning of the domain
			if ( strpos($domain, '-' ) === 0 ) {
                $this->errormessages[]=JText::_("FACTORY_EMAIL_FIELD_IS_NOT_VALID");            
				return false;
			}

			// Check for a dash at the end of the domain
			$length = strlen($domain) -1;
			if ( strpos($domain, '-', $length ) === $length ) {
                $this->errormessages[]=JText::_("FACTORY_EMAIL_FIELD_IS_NOT_VALID");            
				return false;
			}

		}

		return true;
    }    
    
    function validateJS()
    {
        
        return '';
    }
}

?>
