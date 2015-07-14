<?php
/**------------------------------------------------------------------------
com_rbids - Reverse Auction Factory 3.0.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
 * @build: 01/04/2012
 * @package: RBids
-------------------------------------------------------------------------*/ 

defined('_JEXEC') or die('Restricted access');

abstract class JHTMLCountry
{
    static function selectlist($name='country',$attributes='',$defaultvalue=null,$only_existing_users=false)
    {
        if (!$only_existing_users)
        {
            $db= JFactory::getDbo();
            $db->setQuery("select `name` as text,`name` as value from `#__".APP_PREFIX."_country` where `active`=1 order by `name` ");
            $country=$db->loadObjectList();
        }else{
    		$model = JModel::getInstance('User' ,'bidsModel');
            $countrylist=$model->getUserCountries();
            $country=array();
            foreach($countrylist as $c)
                $country[] = JHTML::_('select.option', $c->country,$c->country);        
        }
        $emptyopt[] = JHTML::_('select.option', '',JText::_("COM_BIDS_CHOOSE_COUNTRY"));
		$country = array_merge( $emptyopt, $country );

        return JHtml::_('select.genericlist',$country,$name,$attributes,'value','text',$defaultvalue);
    }

}
