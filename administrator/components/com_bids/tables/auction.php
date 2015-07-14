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

defined( '_JEXEC' ) or die( 'Restricted access' );

class JTableAuction extends FactoryFieldsTbl {

    public
        $id,
        $userid,
        $published,
        $title,
        $shortdescription,
        $description,
        $cat,
        $auction_type,
        $automatic,
        $initial_price,
        $BIN_price,
        $min_increase,
        $reserve_price,
        $currency,
        $start_date,
        $end_date,
        $closed_date,
        $modified,
        $close_offer,
        $close_by_admin,
        $featured,
        $newmessages,
        $payment_info,
        $shipment_info,
        $extended_counter,
        $nr_items,
        $quantity,
        $auction_nr,
        $hits,
        $params;

    //Parameters and default values
    public $_defaultParams=array("picture"=>1,
                           "add_picture"=>1,
                           "auto_accept_bin"=>1,
                           "bid_counts"=>1,
                           "max_price"=>1,
                           "show_reserve"=>0,
                           "price_suggest"=>0,
                           "price_suggest_min"=>'');

    //TODO: editable fields AND editable after auction starts
    protected $_editableFields = array();

    function __construct( &$db ) {
//        $fields = $this->getProperties();
//        $notEditable = array('id','userid','auction_type','automatic','closed_date','close_offer','close_by_admin','hits','modified',
//        'newmessages','auction_nr','featured','extended_counter',);
        parent::__construct( '#__bid_auctions', 'id', $db );
    }

    public function get($property, $default=null)
    {
        if (method_exists($this,'get'.ucfirst($property))){ //Getter
            if(isset($this->_cache) && is_object($this->_cache) && isset($this->_cache->$property)){
                return $this->_cache->$property;
            } 
            $method='get'.ucfirst($property);
            $res=self::$method($default);
            $this->setCache($property,$res);
            return $res;             
        }
        if (strpos($property,'.')){//External object !
            $p=explode('.',$property);
            $class='BidsHelper'.ucfirst($p[0]).'Auction';
            array_shift($p);
            if (count($p)==1) $p=$p[0];
            if (class_exists($class))
                return call_user_func(array($class,'get'),$this,$p);
        }
        return parent::get($property, $default);
    }
    function setCache($property,$value)
    {
        if(!isset($this->_cache) || !is_object($this->_cache)){
            $this->_cache=new StdClass();
        }
        $this->_cache->$property=$value;
    }
    function setCacheObject($auction_object)
    {
        //used to store extended properties got by model
        $this->_cache=$auction_object;
    }

    function delete( $oid=null ) {

        jimport('joomla.filesystem.file');

        if ($oid){
            $this->load($oid);
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('picture');
        $query->from('#__bid_pictures');
        $query->where('auction_id = '.$db->quote($this->id));
        $db->setQuery($query);
        $images = $db->loadObjectList();

        if (count($images)) {
            foreach ($images as $image){
                if (JFile::exists(AUCTION_PICTURES_PATH.DS.$image->picture)){
                    JFile::delete(AUCTION_PICTURES_PATH.DS.$image->picture);
                    JFile::delete(AUCTION_PICTURES_PATH.DS."middle_".$image->picture);
                    JFile::delete(AUCTION_PICTURES_PATH.DS."resize_".$image->picture);
                }
            }
        }

        //all the records dependent on auctions will be deleted through foreign keys
        return parent::delete();
    }

    function SendMails($userlist,$mailtype) {

        $app =  JFactory::getApplication();
        $database = JFactory::getDBO();

        $mail_from = $app->getCfg('mailfrom');
        $site_name = $app->getCfg('sitename');

        // If mailfrom is not defined A super administrator is notified
        if ( ! $mail_from  || ! $site_name ) {
                //get all super administrator
                $query = 'SELECT name, email, sendEmail' .
                                ' FROM #__users' .
                                ' WHERE LOWER( usertype ) = "super administrator"';
                $database->setQuery( $query );
                $rows = $database->loadObjectList();

                $site_name = $rows[0]->name;
                $mail_from = $rows[0]->email;
        }

        set_time_limit(0);
        ignore_user_abort();

        $mail_body=  JTable::getInstance('bidmail');
        if (!$mail_body->load($mailtype) ) return;
        if (!$mail_body->enabled) return;
		if(!is_array($userlist)) {
            if(is_object($userlist)) {
                $userlist = array($userlist);
            }
        }
        if (count($userlist)<=0) return;

        $database->setQuery('SELECT email FROM #__users WHERE id = '.$database->quote($this->userid) );
        $auctioneerEMAIL = $database->loadResult();

        $database->setQuery('SELECT email
                                FROM #__bids AS b
                                LEFT JOIN #__users u
                                    ON u.id = b.userid
                                WHERE
                                    auction_id = '.$database->quote($this->id).'
                                    AND accept = 1');
        $result = $database->loadResultArray();
        if(!empty($result)){
          $winnerEMAILs = implode(',',$result);
        }
        foreach($userlist as $can){

            if(!$can->email) {
                continue;
            }

            $userBid = $this->getBestBid($can->id);

            // ? Issues or Multiple domains and want to link to a particular domain name?
            // Replace JURI::root(). with the desired domain ex: 'http:://xy.com'.
            $url = JUri::getInstance()->toString(array('scheme', 'host', 'port')) . JRoute::_('index.php?option=com_bids&task=viewbids&id=' . $this->id . ':' . JFilterOutput::stringURLSafe($this->title));

            $patterns = array('%NAME%','%SURNAME%','%AUCTIONTITLE%','%AUCTIONDESCR%','%AUCTIONSTART%','%AUCTIONEND%','%AUCTIONLINK%','%BIDPRICE%','%AUCTIONEEREMAIL%','%WINNEREMAIL%');
            $replacements = array($can->name,@$can->surname,$this->title,$this->description,$this->start_date,$this->end_date,$url,(isset($userBid->bid_price) ? $userBid->bid_price : 0),$auctioneerEMAIL,$winnerEMAILs);

            $subj = str_replace($patterns,$replacements,$mail_body->subject);
            $mess = str_replace($patterns,$replacements,$mail_body->content);

            $mail   = JMail::getInstance();
            $mail->sendMail($mail_from,$site_name,$can->email, $subj, $mess, true);
        }
    }

    function sendNewMessage($message,$as_replyto=null,$force_recipientid=null){

        $my = JFactory::getUser();
        $m =  JTable::getInstance('bidmessage');

        //if $as_replyto is set then it is a reply from the auctioneer to the user asking something

        $m->auction_id		= $this->id;
        $m->message			= $message;
        $m->modified		= gmdate('Y-m-d H:i:s');
        $m->userid1 		= $my->id;
        $m->wasread         = 0;

        if( !$as_replyto){
            //new Question from user
            $m->parent_message	= 0;
            $m->userid2         = ($force_recipientid)?$force_recipientid:$this->userid;
        } else {
            $parentmessage =  JTable::getInstance('bidmessage');
            $parentmessage->load($as_replyto);
            $parentmessage->wasread = 1;
            $parentmessage->store();

            $m->parent_message 	= $as_replyto;
            $m->userid2 		= $parentmessage->userid1;
        }
        JTheFactoryEventsHelper::triggerEvent('onBeforeSendMessage',array($this,$m));
    	if ($res=$m->store())
            JTheFactoryEventsHelper::triggerEvent('onAfterSendMessage',array($this,$m));
    }

    function SetCategory($category_name)
    {
        $db = JFactory::getDbo();
        $db->setQuery("SELECT id FROM #__categories WHERE extension='".APP_EXTENSION."' AND title=" . $db->quote($category_name) );
        $cat = $db->LoadResult();
        if($cat) {
            $this->cat = $db->LoadResult();
        } else {
            $db->setQuery("SELECT id FROM #__categories WHERE extension='".APP_EXTENSION."' ORDER BY id ASC LIMIT 1" );
            $this->cat = $db->LoadResult();
        }
    }

    /**
     *
     * @param optional int $user_id
     * @return mixed
     * @since 2.0.9
     */
    function GetWinningBid( $user_id = null ) {

    	$db = JFactory::getDbo();

        $query = "SELECT
                    b.bid_price as price,
                    b.modified,
                    b.quantity,
                    b.userid as winner,
                    IF(b.bid_price=".$this->BIN_price.",'bin','bid')  as win_type,
                    u.username
                FROM #__bids as b
                LEFT JOIN #__users as u ON u.id = b.userid
                WHERE auction_id=".$this->id."' and accept = 1
                ORDER BY win_type";
        $db->setQuery($query);

        return $db->loadObjectList();
    }

    /**
     * Get Highest Bid
     * 	used in saveBid method
     *
     * @return object
     */
    function GetBestBid($userid=false) {

        $db = JFactory::getDbo();

        $query = "SELECT * FROM #__bids AS b WHERE auction_id='$this->id' ".($userid ? (' AND userid='.$userid) : '')."  ORDER BY bid_price DESC";
        $db->setQuery($query,0,1);

        $res = $db->loadObject();
        //very messy, but it has to work now; blame H.
        //when this userid has no bids, it returns the highest bid
        if(!$res) {
            $query = "SELECT * FROM #__bids AS b WHERE auction_id='$this->id'  ORDER BY bid_price DESC";
            $db->setQuery($query,0,1);
            $res = $db->loadObject();
        }
        return $res;
    }

    function getFieldOrderArray() {

        return array(
        	'a.start_date'      => bid_sort_start_date,
        	'a.title'           => bid_sort_title,
        	'b.username'        => bid_sort_username,
        	'a.end_date'        => bid_sort_end_date,
        	'a.initial_price'   => bid_sort_initialprice,
        	'a.hits'            => bid_sort_hits,
        	'a.BIN_price'       => bid_sort_binprice,
        	'a.id'              => bid_sort_newest
        );
    }
    function getPicturesList()
    {
        $db = JFactory::getDbo();

        $db->setQuery("select * from #__bid_pictures where auction_id={$this->id}");
        return $db->loadObjectList();

    }
    function hasBids() {

        $db = $this->getDBO();

        $db->setQuery("SELECT COUNT(1) FROM #__bids WHERE auction_id=".$db->Quote($this->id));
        $res = $db->loadResult();

        if($this->auction_type==AUCTION_TYPE_BIN_ONLY && bin_opt_price_suggestion) {
            $db->setQuery("SELECT COUNT(1) FROM #__bid_suggestions WHERE auction_id=".$db->Quote($this->id));
            $suggestions = $db->loadObjectList();
            $res = $db->loadResult();
        }

        return (boolean) $res;
    }

    function store($quicksave=false) {

        if(is_array($this->params)) {
            $stringParams = '';
            foreach($this->params as $k=>$v) {
                $stringParams .= $k.'='.$v.PHP_EOL;
            }
            $this->params = $stringParams;
        }

        return parent::store($quicksave);
    }
    function getGallery()
    {
        $images=array();
        $ilist=$this->getImages();
        foreach($ilist as $img)
            $images[]=$img->picture;

		$gallery = BidsHelperGallery::getGalleryPlugin();
        $gallery->clearImages();
		$gallery->addImageList($images);
        
        return $gallery->getGallery(); 
    }

    function getThumbnail()
    {
		return BidsHelperGallery::getGalleryPlugin()->getThumbImage();
    }

	function getImages() 
    {
        $db=$this->getDbo();
		$db->setQuery("SELECT * FROM #__bid_pictures WHERE auction_id = '{$this->id}'");
		return $db->loadObjectList();
	}
    function isMyAuction()
    {
		$my =  JFactory::getUser();
        if ($my->id && $my->id==$this->userid) return true;
        else return false;
    }

    function getHighest_bid() {
        $bid = $this->GetBestBid();
        return is_null($bid) ? 0 : $bid->bid_price;
    }
}
