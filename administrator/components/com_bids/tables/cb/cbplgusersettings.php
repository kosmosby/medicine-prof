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

defined ('_JEXEC') or die();

class JTableCbPlgUserSettings extends JTable {
    var $id	 = null;
    var $userid	 = null;
    var $name	 = null;
    var $value	 = null;

    function __construct(&$db) {
        parent::__construct('#__bid_user_settings','id',$db);
    }

    /**
     * Save Settings List
     *  @param   $settings array of key - name => value = setting value
     *
    */
    function saveUserSettings($settings){

        // Delete old settings
        $this->_db->setQuery("DELETE FROM `#__bid_user_settings` WHERE userid = '{$this->userid}' ");
        $this->_db->query();

        $inserts = array();

        if($settings){
            foreach($settings as $name => $settingVal){
                    $inserts[] = " ( '{$name}', '{$settingVal}', '{$this->userid}' ) ";
            }

            $this->_db->setQuery("INSERT INTO #__bid_user_settings (name, value ,userid ) VALUES ".implode(",", $inserts));
            $this->_db->query();
        }
    }
}
?>
