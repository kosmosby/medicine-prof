<?php
/**------------------------------------------------------------------------
com_bids - Auction Factory 2.5.0
------------------------------------------------------------------------
 * @author TheFactory
 * @copyright Copyright (C) 2011 SKEPSIS Consult SRL. All Rights Reserved.
 * @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * Websites: http://www.thefactory.ro
 * Technical Support: Forum - http://www.thefactory.ro/joomla-forum/
-------------------------------------------------------------------------*/



class BidsHelperDateTime {

    static function getTimeStamp($date) {

        preg_match('#([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})#', $date, $m);

        return gmmktime($m[4], $m[5], $m[6], $m[2], $m[3], $m[1]);
    }

    static function dateFormatConvert2JHTML($withTime=true) {

        static $dateFormat = '';
        if ('' != $dateFormat) {
            return $dateFormat;
        }

        $cfg = BidsHelperTools::getConfig();

        $dateFormat = $cfg->bid_opt_date_format;

        if ($cfg->bid_opt_enable_hour && $withTime) {
            $dateFormat .= ' H:M';
        }

        return $dateFormat;
    }

    static function getUTCDate($d,$h,$m,$s) {

        $jconfig = JFactory::getConfig();
        $cfg = BidsHelperTools::getConfig();

        $error_msg = array();

        $date = BidsHelperTools::auctionDatetoIso($d);

        if(!$date) {
            return false;
        }

        $hour = $minutes = '00';
        if ($cfg->bid_opt_enable_hour) {
            $hour = $h ? $h : '00';
            $minutes = $m ? $m : '00';
            if ($hour > 24 || $minutes > 60) {
                return false;
            }
        }

        $DateTime = JFactory::getDate($date . ' ' . $hour . ':' . $minutes . ':00', $jconfig->getValue('config.offset'));
        if (!$DateTime) {
            return false;
        }

        $date = BidsHelperTools::auctionDatetoIso($d);

        if ($cfg->bid_opt_enable_hour) {
            $hour = intval($h);
            if($hour<0 || $hour>59) {
                $hour = 0;
            }
            if( 1==strlen($hour) ) {
                $hour = '0'.$hour;
            }
            $minutes = intval($m);
            if($minutes<0 || $minutes>59) {
                $minutes = 0;
            }
            if( 1==strlen($minutes) ) {
                $minutes = '0'.$minutes;
            }
        } else {
            $hour = $minutes = '00';
        }

        $DateTime = JFactory::getDate($date . ' ' . $hour . ':' . $minutes . ':00', $jconfig->getValue('config.offset'));

        return ($DateTime->toUnix() < time() ) ? JFactory::getDate()->toMySQL() : $DateTime->toMySQL();
    }

    static function translateAutoextendPeriod($periodCode) {

        switch ($periodCode) {
            case 'second':
                return 1;
            case 'minute':
                return 60;
            case 'hour':
                return 3600;
            case 'day':
                return 86400;
            case 'month':
                return 2628000;
            case 'year':
                return 31536000;
        }
        return 0;
    }
   static function dateFormatConversion($dateformat)
    {
        $strftime_format=$dateformat;
        $strftime_format=str_replace('%','%%',$strftime_format);
        $strftime_format=str_replace('Y','%Y',$strftime_format);
        $strftime_format=str_replace('y','%y',$strftime_format);
        $strftime_format=str_replace('d','%d',$strftime_format);
        $strftime_format=str_replace('D','%A',$strftime_format);
        $strftime_format=str_replace('m','%m',$strftime_format);
        $strftime_format=str_replace('F','%B',$strftime_format);
        return $strftime_format;
    }
    
}
