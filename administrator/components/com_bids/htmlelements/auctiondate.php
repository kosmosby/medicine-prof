<?php

defined('_JEXEC') or die('Restricted access');

abstract class JHTMLAuctionDate
{
    static function calendar($isodate,$name)
    {
        $cfg= BidsHelperTools::getConfig();
        
        $result=JHTML::_('calendar',  $isodate, $name, $name ,BidsHelperDateTime::dateFormatConversion($cfg->date_format));
        
        if ($isodate) //ISODATES and JHtml::_('calendar') doesn't take kindly all formats       
            $result=str_replace(' value="'.htmlspecialchars($isodate, ENT_COMPAT, 'UTF-8').'"', 
                    ' value="'.htmlspecialchars(JHtml::date($isodate,$cfg->date_format,false), ENT_COMPAT, 'UTF-8').'"',
                    $result
            );

        return $result;
    }

}
