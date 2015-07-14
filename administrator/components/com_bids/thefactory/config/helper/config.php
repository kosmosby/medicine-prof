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

class JTheFactoryConfigHelper
{
    static function getFieldGroups($filename)
    {
        $xml = JFactory::getXML($filename);
        $groups=array();
        if (isset($xml->groups) && count($xml->groups)) {
            foreach ($xml->groups as $fields)
            {
                $g=new stdClass();
                $g->name=(string)$fields->attributes()->name;
                $g->title=(string)$fields->attributes()->label;
                $groups[]=$g;
            }
        }
        return $groups;
    }
    function getFieldsets($filename,$group)
    {
        $xml = JFactory::getXML($filename);
        $elements = $xml->xpath('//groups[@name="'.(string) $group.'"]');
        $sets=array();
        foreach ($elements as $element)
        {
				if ($tmp = $element->xpath('descendant::fieldset[@name] | descendant::field[@fieldset]/@fieldset')) {
					$sets = array_merge($sets, (array) $tmp);
				}
        }
        return $sets;
    }
}


?>
