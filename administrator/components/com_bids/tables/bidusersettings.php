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

class JTableBidUserSettings extends JTable {

    var $userid	    = null;
    var $settings	= null;

    function __construct(&$db) {

        parent::__construct('#__bid_user_settings','userid',$db);
    }

    function load($id=null) {

        parent::load($id);

        jimport('joomla.html.parameter');
        $params = new JParameter($this->settings);
        $this->settings = $params->toArray();
    }

    function store() {
        jimport('joomla.html.parameter');

        //there has to already be a record with this userid

        $db = JFactory::getDbo();
        $db->setQuery("SELECT COUNT(1) FROM ".$this->getTableName()." WHERE userid=".$this->userid);
        if(!$db->loadResult()) {
            $db->setQuery("INSERT INTO ".$this->getTableName()." (userid) VALUES (".$this->userid.")");
            $db->query();
        }

        if($this->settings instanceof JParameter) {
            $this->settings = $this->settings->toString();
        }
        else if (is_array($this->settings)) {
            $tmp = array();
            foreach($this->settings as $k=>$v) {
                $tmp[] = $k.'='.$v;
            }
            $this->settings = implode(PHP_EOL, $tmp);
        }

        return parent::store();
    }
}
