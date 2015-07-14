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

jimport('joomla.application.component.view');

class JBidsAdminView extends JView {

    function __construct($config = array()) {

        parent::__construct($config);

        $cfg=BidsHelperTools::getConfig();
        $this->assignRef('cfg',$cfg);

        JHTML::stylesheet('com_bids.css', JURI::root() . 'administrator/components/com_bids/css/');
    }

    function display($tpl=null) {

        $this->addToolBar();

        BidsHelperAdmin::subMenuHelper();

        parent::display($tpl);
    }

    //to be overridden
    function addToolBar() {}
}
