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



class BidsHelperSEF{
    function GetTaskMap()
    {
        $task_map=array(
            "viewbids"=>"auction",
            "userdetails"=>"auctioneer"

        );
        return $task_map;
    }
    function Task2Map($task){
        $map=self::GetTaskMap();

        if (isset($map[$task]))
            return $map[$task];
        else
            return $task;
    }
    function Map2Task($url_part){
        $map=array_flip(self::GetTaskMap());
        if (isset($map[$url_part]))
            return $map[$url_part];
        else
            return $url_part;
    }

	function str_clean( $string ) {
		$aToReplace = array(" ","/","&","�","�","�","!","$","%","@","?","#","(",")","+","*",":",";","'","\"");
		$aReplacements = array("-","-","and","");

		$str_buff = str_replace($aToReplace,$aReplacements,strtolower($string)	);
		return $str_buff;
	}

	function getCatString(&$id){

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('path')
            ->from('#__categories')
            ->where('id='.$db->quote($id));

        $db->setQuery($query);

        return $db->loadResult();
	}

	function getTitleBid(&$id){
		$database = JFactory::getDBO();
		$database->setQuery("select title from #__bid_auctions where id=$id");
		$result = $database->loadResult();
		$result = self::str_clean($result);
		return $result;
	}

	function getUsername(&$id){
		$database = JFactory::getDBO();
		$database->setQuery("select a.username from #__users a where a.id = $id ");
		$result = $database->loadResult();
		$result = self::str_clean($result);
		return $result;
	}

    function getAuctionNr($url_array)
    {
        $ret="";
        if (in_array("auction_nr",$url_array)){
            $i=array_search("auction_nr",$url_array);
            self::setVar("auction_nr",$url_array[$i+1]);
            $ret.="&auction_nr=".$url_array[$i+1];
        }
        return $ret;

    }
	function getVal (&$string, $varname, $default) {

	     $string = str_replace("&amp;","&",$string);    // replace "&amp;" with "&" for explore routine...
	     $vars = explode("&", $string);

	     $temp = array();
	     $i=0;

	     foreach ($vars as $var) {
	        $temp = explode("=", $var);

	        if ($temp[0] == $varname) {  // Found the variable
	         	break;
	        }
	        $i++;
	     }
	     if ($temp[0] != $varname) { // Not found => Set Default values
	        $temp[0] = $varname;
	        $temp[1] = $default;
	     }

     return $temp[1];
	}

	function  setVar($varname,$value) {

	 global $_GET, $_REQUEST,$_POST;       // Mandatory : Need to access to gobal Link (Request) and Posted (GET) Mambo data


     $_POST[$varname] = $value;
     $_GET[$varname] = $value;
     $_REQUEST[$varname] = $value;
     $v = "&" .$varname. "=$value";

     return $v;
  	}

}
    
