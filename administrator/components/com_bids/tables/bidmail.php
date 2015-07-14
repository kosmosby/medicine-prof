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

class JTableBidMail extends JTable {

    var $id         = null;
    var $mail_type  = null;
    var $content    = null;
    var $subject    = null;
    var $enabled    = null;

    function __construct( &$db ) {

        parent::__construct( '#__bid_mails', 'mail_type', $db );
    }

    function store( $updateNulls=false ) {

        $k = $this->_tbl_key;

        if ($this->$k) {
                $ret = $this->_db->updateObject($this->_tbl, $this, $this->_tbl_key, $updateNulls);
        } else {
                $ret = $this->_db->insertObject($this->_tbl, $this, $this->_tbl_key);
        }

        if (!$ret) {
                $this->_error = strtolower(get_class($this))."::store failed <br />" . $this->_db->getErrorMsg();
                return false;
        } else {
                return true;
        }
    }


}
?>
