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


// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.controller');

class JBidsAdminController extends JController {

    //if request has &tmpl=component, the redirect response will have the same (kind of ugly)
    protected $_tmpl;

    function __construct($config=array()) {

        parent::__construct($config);

        JLoader::register('JBidsAdminView', JPATH_COMPONENT_ADMINISTRATOR . DS . 'views' . DS . 'view.php');

        if ('component' == JRequest::getVar('tmpl')) {
            $this->_tmpl = 'component';
        }

        $this->registerTask('newauction','editoffer');


        $this->registerDefaultTask('offers');
    }

    function redirect() {

        if ($this->_tmpl && $this->redirect) {
            $uri = JURI::getInstance($this->redirect);
            $uri->setVar('tmpl', $this->_tmpl);
            $this->redirect = $uri->toString();
        }

        parent::redirect();
    }


    function users() {
        $view = $this->getView('userlist');
        $view->display();
    }

    function detailuser() {
        $view = $this->getView('userdetails');
        $view->display();
    }

    function blockuser() {

        $cid = JRequest::getVar("cid", array());
        if (count($cid) < 1) {
            $this->setRedirect('index.php?option=com_bids&task=users', JText::_('COM_BIDS_SELECT_AN_USER'));
        }

        $profile = BidsHelperTools::getUserProfileObject();
        $profile->setFieldValue('block',1,$cid);

        $this->setRedirect('index.php?option=com_bids&task=users', JText::_('COM_BIDS_USER_BLOCKED'));
    }

    function unblockuser() {

        $cid = JRequest::getVar("cid", array());
        if (count($cid) < 1) {
            $this->setRedirect('index.php?option=com_bids&task=users', JText::_('COM_BIDS_SELECT_AN_USER'));
        }

        $profile = BidsHelperTools::getUserProfileObject();
        $profile->setFieldValue('block',0,$cid);

        $this->setRedirect('index.php?option=com_bids&task=users', JText::_('COM_BIDS_USER_UNBLOCKED'));
    }

    function toggleverify() {

        $cid = JRequest::getVar("cid", array());
        if (count($cid) < 1) {
            $this->setRedirect('index.php?option=com_bids&task=users', JText::_('COM_BIDS_SELECT_AN_USER'));
        }

        $profile = BidsHelperTools::getUserProfileObject();
        $profile->setFieldValue('verified','1-#fieldName',$cid);

        $this->setRedirect('index.php?option=com_bids&task=users', JText::_('COM_BIDS_SUCCES'));
    }

    function togglepowerseller() {

        $cid = JRequest::getVar("cid", array());
        if (count($cid) < 1) {
            $this->setRedirect('index.php?option=com_bids&task=users', JText::_('COM_BIDS_SELECT_AN_USER'));
        }

        foreach($cid as $id) {
            $profile = BidsHelperTools::getUserProfileObject($id);
            $profile->setFieldValue('powerseller','1-#fieldName',$id);
        }

        $this->setRedirect('index.php?option=com_bids&task=users', JText::_('COM_BIDS_SUCCES'));
    }
    function offers() {
        $view = $this->getView('auctionlist');
        $view->display();
    }

    function editoffer() {

        $cid = JRequest::getVar('cid', array(), 'default', 'array');
        $id = JRequest::getInt('id',0);
        if (!$id) {
            $id = @$cid[0];
        }

        $model = $this->getModel('auction');
        $model->load($id);

        $model->loadAuctionFromSession();

        $view = $this->getView('auctiondetails');
        $view->setModel($model,true);
        $view->display($id);
    }

    function editOffer1()
    {

        $app = JFactory::getApplication();
        $input = $app->input;

        $id = $input->getInt('id', 0);

        $model = $this->getModel('auction');
        $model->load($id);

        $model->loadAuctionFromSession();

        $view = $this->getView('editauction');
        $view->setModel($model);
        $view->display();
    }

    function closed() {

        $cid = JRequest::getVar('cid', array(), '', 'array');
        if(empty($cid)) {
            $cid[] = JRequest::getInt('id');
        }

        $auction = JTable::getInstance('auction');
        foreach($cid as $id) {
            $auction->load($id);
            $auction->close_by_admin = 1;
            $auction->closed_date = gmdate('Y-m-d H:i:s');
            $auction->published = 0;
            $auction->store();
    
            JTheFactoryEventsHelper::triggerEvent('onAuctionBanned',array($auction));
        }

        $this->setRedirect("index.php?option=com_bids&task=offers", count($cid) . ' ' . JText::_("COM_BIDS_AUCTIONS_CLOSED"));
    }

    function opened() {

        $cid = JRequest::getVar('cid', array(), '', 'array');
        if (empty($cid) ) {
            $cid[] = JRequest::getInt('id');
        }

        $auction = JTable::getInstance('auction');
        foreach($cid as $id) {
            $auction->load($id);
            $auction->close_by_admin = 0;
            $auction->closed_date = gmdate('Y-m-d H:i:s');
            $auction->published = 1;
            $auction->store();

            JTheFactoryEventsHelper::triggerEvent('onAfterSaveAuctionSuccess',array($auction));
        }

        $this->setRedirect("index.php?option=com_bids&task=offers", count($cid) . JText::_("COM_BIDS_AUCTIONS_OPENED"));
    }

    function resethits() {
        $database = JFactory::getDBO();

        $cid = JRequest::getVar('cid', array(), '', 'array');
        $id = $cid[0];

        $database->setQuery("UPDATE #__bid_auctions SET hits='0' WHERE id='$id'");
        $database->query();

        $this->setRedirect(JURI::root() . "administrator/index.php?option=com_bids&task=editoffer&id=$id", JText::_('COM_BIDS_SUCCES'));
    }

    function category() {
        $database =  JFactory::getDBO();
        $cid = JRequest::getVar('cid', array(), '', 'array');
        $id = JRequest::getInt("id");

        $category = JRequest::getInt('category', 0);

        if (!$id) {
            $id = $cid[0];
        }

        if ($category >= 0) {
            $database->setQuery("update #__bid_auctions set cat='$category' where id='$id'");
            $database->query();
        }

        $this->setRedirect(JURI::root() . "administrator/index.php?option=com_bids&task=editoffer&id=$id", JText::_('COM_BIDS_SUCCES'));
    }

    function set_featured() {
        $database =  JFactory::getDBO();
        $cid = JRequest::getVar('cid', array(), '', 'array');
        $id = JRequest::getInt("id");

        $feat = JRequest::getString('featured', '');

        if (!$id)
            $id = $cid[0];
        if ($feat) {
            $database->setQuery("update #__bid_auctions set featured='$feat' where id='$id'");
            $database->query();
        }

        $this->setRedirect(JURI::root() . "administrator/index.php?option=com_bids&task=editoffer&id=$id", JText::_('COM_BIDS_SUCCES'));
    }
    function dashboard() {

        $db = JFactory::getDbo();

        $query = "SELECT COUNT(*) FROM #__bid_auctions WHERE published=1 AND close_offer = 0 AND close_by_admin = 0 AND end_date>=UTC_TIMESTAMP()";
        $db->setQuery($query);
        $nr_active = $db->loadResult();

        $query = "SELECT COUNT(*) FROM #__bid_auctions WHERE published=1 AND close_offer = 0 AND close_by_admin = 0 AND end_date<UTC_TIMESTAMP()";
        $db->setQuery($query);
        $nr_expired = $db->loadResult();

        $query = "SELECT COUNT(*) FROM #__bid_auctions WHERE published=1 AND close_offer = 1 AND close_by_admin = 0";
        $db->setQuery($query);
        $nr_closed = $db->loadResult();



        $query = "SELECT COUNT(*) FROM #__bid_auctions WHERE published=0 ";
        $db->setQuery($query);
        $nr_unpublished = $db->loadResult();

        $query = "SELECT COUNT(*) FROM #__bid_auctions WHERE published=0 AND close_by_admin = 0";
        $db->setQuery($query);
        $nr_cancelled = $db->loadResult();

        $query = "SELECT COUNT(*) FROM #__bid_auctions WHERE close_by_admin = 1";
        $db->setQuery($query);
        $nr_blocked = $db->loadResult();



        $query = "SELECT COUNT(distinct userid) FROM #__bid_auctions";
        $db->setQuery($query);
        $nr_a_users = $db->loadResult();

        $query = "SELECT COUNT(*) FROM #__users";
        $db->setQuery($query);
        $nr_r_users = $db->loadResult();

        $query = "SELECT COUNT(1) FROM #__bids WHERE cancel=0";
        $db->setQuery($query);
        $nr_active_bids = $db->loadResult();

        $query = "SELECT COUNT(1) FROM #__bids WHERE cancel=0 AND accept=1";
        $db->setQuery($query);
        $nr_accepted_bids = $db->loadResult();

        // Get latest 5 auctions
        $query = "SELECT `a`.`id`,
						 `a`.`title`,
						 `a`.`start_date`,
						 `a`.`currency`,
						 `a`.`end_date`,
						 MIN(`bids`.`bid_price`) AS `min_bid`,
						 MAX(`bids`.`bid_price`) AS `max_bid`,
						 `u`.`id` AS `user_id`,
						 `u`.`username` AS `owner`
					   FROM `#__bid_auctions` AS `a`
					   LEFT JOIN `#__users` AS `u` ON `u`.`id` = `a`.`userid`
					   LEFT JOIN `#__bids` AS `bids` ON `bids`.`auction_id` = `a`.`id`
					   GROUP BY `a`.`id`
					   ORDER BY `a`.`id` DESC
					   LIMIT 5
                                 ";
        $db->setQuery($query);
        $latest5auctions = $db->loadObjectList();

        // Get latest 5 payments
        $query = "SELECT `paylog`.`id`,
						 `paylog`.`orderid`,
						 `paylog`.`amount`,
						 `paylog`.`currency`,
						 `paylog`.`payment_method`,
						 `paylog`.`status`,
						 `paylog`.`date`,
						 `paylog`.`userid`,
						 `u`.`username`
					FROM `#__bid_payment_log` AS `paylog`
					LEFT JOIN `#__users` AS `u` ON `u`.`id` = `paylog`.`userid`
				   ORDER BY `paylog`.`id` DESC
				   LIMIT 5
			";
        $db->setQuery($query);
        $latest5payments = $db->loadObjectList();

        // table ordering
        $lists['nr_active'] = $nr_active;
        $lists['nr_expired'] = $nr_expired;
        $lists['nr_closed'] = $nr_closed;

        $lists['nr_unpublished'] = $nr_unpublished;
        $lists['nr_cancelled'] = $nr_cancelled;
        $lists['nr_blocked'] = $nr_blocked;

        $lists['nr_r_users'] = $nr_r_users;
        $lists['nr_a_users'] = $nr_a_users;

        $lists["nr_active_bids"] = $nr_active_bids;
        $lists["nr_accepted_bids"] = $nr_accepted_bids;

        $view = $this->getView('dashboard');
        $view->assignRef('latest5auctions', $latest5auctions);
        $view->assignRef('latest5payments', $latest5payments);
        $view->assignRef('lists', $lists);

        $view->display();
    }
    function installtemplates() {

        jimport('joomla.filesystem.folder');
        if (JFolder::exists(JPATH_SITE . DS . 'components' . DS . 'com_bids' . DS . 'templates-dist')) {
            JFolder::copy(JPATH_SITE . DS . 'components' . DS . 'com_bids' . DS . 'templates-dist', JPATH_SITE . DS . 'components' . DS . 'com_bids' . DS . 'templates', '', true);
            $message = JText::_('COM_BIDS_TEMPLATES_OVERWRITTEN');
        } else {
            $message = JText::_('COM_BIDS_NEW_TEMPLATES_NOT_FOUND_PLEASE_CHECK_INSTALLATION_KIT');
        }
        $this->setRedirect('index.php?option=com_bids&task=settingsmanager', $message);
    }

    function settingsmanager() {
        $view = $this->getView('settingspanel');
        $view->display();
    }

    function purgeAuctions() {
        $database =  JFactory::getDBO();
        set_time_limit(0);
        $query = "SELECT count(id) as nr FROM #__bid_auctions
				WHERE close_offer=1 or close_by_admin=1 ";
        $database->setQuery($query);
        $nr = $database->loadResult();

        JToolBarHelper::title('');

        if (!$nr) {
            JError::raiseNotice(601,JText::_("COM_BIDS_NO_EXPIRED_AUCTIONS_TO_PURGE"));
            $this->setRedirect("index.php?option=com_bids&task=auctionmanager");
        } else {
            echo "
                <strong>".JText::sprintf('COM_BIDs_CONFIRM_PURGE',$nr)."</strong><br /><br />
                <table width='300'>
                    <tr>
                        <td align='center'>
                            <input type='button' onclick='document.location=\"index.php?option=com_bids&task=auctionmanager\"' value='".JText::_("COM_BIDS_NO")."'>
                            &nbsp;&nbsp;
                            <input type='button' onclick='document.location=\"index.php?option=com_bids&task=dopurgeauctions\"' value='".JText::_("COM_BIDS_YES")."'>
                        </td>
                    </tr>
                </table>";
        }
    }

    function dopurgeAuctions() {
        $database =  JFactory::getDBO();
        @set_time_limit(0);

        //Close all auctions without a parent user (deleted users?)
        $query = "update #__bid_auctions a left join #__users b on a.userid=b.id set close_by_admin = 1 where b.id is null";
        $database->setQuery($query);
        $database->query();

        $query = "SELECT id FROM #__bid_auctions
				WHERE close_offer =1 or close_by_admin =1 ";
        $database->setQuery($query);
        $idx = $database->loadObjectList();
        $auction =  JTable::getInstance('auction');

        for ($i = 0; $i < count($idx); $i++) {
            $auction->delete($idx[$i]->id);
        }


        $this->setRedirect('index.php?option=com_bids&task=offers', JText::_('COM_BIDS_SUCCES'));
    }

    function auctionManager() {
        $view = $this->getView('tools');
        $view->display();
    }

    function integration() {
        $cfg= BidsHelperTools::getConfig();

        $profile_modes = array();
		$profile_modes[] = JHTML::_('select.option', 'component', 'Component Profile');
		$profile_modes[] = JHTML::_('select.option', 'cb', 'Community Builder');
		$profile_modes[] = JHTML::_('select.option', 'love', 'Love Factory');

        $registration_modes = array();
        $registration_modes[] = JHTML::_('select.option', 'joomla', 'Joomla Registration');
        $registration_modes[] = JHTML::_('select.option', 'component', 'Component Registration');

        $view = $this->getView('integration');
        $view->assign('profile_select_list',JHTML::_("select.genericlist",$profile_modes,'profile_mode','' ,'value', 'text',$cfg->bid_opt_profile_mode));
        $view->assign('registration_select_list', JHTML::_("select.genericlist", $registration_modes,
            'registration_mode', '',
            'value', 'text', $cfg->bid_opt_registration_mode));
        $view->assign('current_profile_mode',$cfg->bid_opt_profile_mode);
        $view->assign('configure_link',"index.php?option=com_bids&task=integrationconfiguration");

        $view->display();
    }

    function IntegrationConfiguration()
    {
        $profile_mode=BidsHelperTools::getProfileMode();
        if ($profile_mode=='component')
        {
            $view=$this->getView('settings','html');
            $view->display('integration');
            return;

        } else {

    	    $MyApp = JTheFactoryApplication::getInstance();
            $integrationClass='JTheFactoryIntegration'.ucfirst($profile_mode);
            JLoader::register($integrationClass,$MyApp->app_path_admin.'integration/'.$profile_mode.'.php');

            $controller_class='JTheFactoryIntegration'.$profile_mode.'Controller';
            $controller=new $controller_class;
            $controller->execute('display');
            $this->setRedirect($controller->redirect);
            return;
        }

    }

    function changeProfileIntegration() {

        $MyApp= JTheFactoryApplication::getInstance();

        $cfg= BidsHelperTools::getConfig();
        $cfg->bid_opt_profile_mode = JRequest::getVar('profile_mode');

        $cfg->bid_opt_registration_mode = JRequest::getVar('registration_mode');

        JTheFactoryHelper::modelIncludePath('config');

        $formxml=JPATH_ROOT.DS."administrator".DS."components".DS.APP_EXTENSION.DS. $MyApp->getIniValue('configxml');
        $model= JModel::getInstance('Config','JTheFactoryModel',array('formxml'=>$formxml));

        $model->save($cfg);

		$this->setRedirect( "index.php?option=com_bids&task=integration",JText::_("COM_BIDS_SETTINGS_SAVED") );
    }

    function miscLists() {

        $view = $this->getView('listspanel');
        $view->display();
    }

    function Statistics() {
        $view = $this->getView('statistics');
        $view->display();
    }

    function bid_stats() {
        $app = JFactory::getApplication();
        $database =  JFactory::getDBO();

        $filter_user = JRequest::getInt('filter_user', null);
        $filter_orderid = JRequest::getString('filter_orderid', "b.modified");
        $filter_orderdir = JRequest::getWord('filter_orderdir', "DESC");
        $filter_refno = JRequest::getVar('refno', '');

        $whereA[] = "a.id>0";
        $limit = JRequest::getInt('limit', $app->getCfg('list_limit'));
        $limitstart = JRequest::getInt('limitstart', 0);

        if ($filter_user) {
            $whereA[] = " b.userid = $filter_user ";
        }
        if ( $filter_refno ) {
            $whereA[] = " a.auction_nr = ".$database->quote($filter_refno);
        }
        if ($filter_orderid) {
            $orderA[] = " $filter_orderid $filter_orderdir ";
        }
        $where = implode(' AND ', $whereA);
        $order = implode(' , ', $orderA);

        $q = "SELECT count(*) FROM #__bid_log AS b LEFT JOIN #__bid_auctions AS a ON b.auction_id=a.id WHERE {$where} ";
        $database->setQuery($q);
        $total = $database->loadResult();

        jimport('joomla.html.pagination');
        $pageNav = new JPagination($total, $limitstart, $limit);

        $q = "SELECT
                    b.*,
                    a.title AS offer,
                    a.BIN_price,
                    a.currency,
                    a.auction_nr,
                    u.username,
                    p.max_proxy_price,
                    bids.accept
                FROM #__bid_log AS b
                LEFT JOIN #__bids AS bids
                    ON b.auction_id=bids.auction_id AND b.userid=bids.userid AND b.bid_price=bids.bid_price
                LEFT JOIN #__bid_auctions AS a
                    ON b.auction_id = a.id
                LEFT JOIN #__users AS u
                    ON b.userid = u.id
                LEFT JOIN #__bid_proxy AS p
                    ON b.id_proxy = p.id
                WHERE ".$where."
                GROUP BY b.id
                ORDER BY ".$order;
        $database->setQuery($q,$limitstart,$limit);

        $rows = $database->loadObjectList();

//        var_dump($rows);exit;

        $view = $this->getView('bidslist','html');
        $view->assignRef('bids',$rows);
        $view->assignRef('pageNav',$pageNav);
        $view->display();
    }

    function write_admin_message() {

        $id_auction = JRequest::getInt('auction_id', 0);

        $auction = JTable::getInstance('auction');
        if (!$id_auction || !$auction->load($id_auction)) {
            JError::raiseWarning(401,JText::_("COM_BIDS_AUCTION_NOT_EXIST_ERROR")." Id:$id_auction");
            return;
        }

        $usr = JFactory::getUser($auction->userid);
        $auction->username = $usr->username;

        $view = $this->getView('messages','html');
        $view->assignRef('auction',$auction);
        $view->display('admin');
    }

    function send_message_auction() {

        global $mainframe;
        $database =  JFactory::getDBO();
        $my =  JFactory::getUser();

        $id_auction = JRequest::getInt('id_auction', 0);
        $message = JRequest::getVar('message', '');

        if (!$message) {
            JError::raiseWarning(401, JText::_('COM_BIDS_ERR_MESSAGE_EMPTY'));
            return;
        }

        $auction =  JTable::getInstance('auction');
        if (!$auction->load($id_auction))
            return;

        $auction->newmessages = 1;
        $auction->store();
        $auction->sendNewMessage($message);

        $this->setRedirect('index.php?option=com_bids&task=editoffer&id=' . $id_auction, JText::_('COM_BIDS_ALT_MESSAGE_SENT'));
    }
    function installOpt()
    {

    }

    function restorebackup() {

        jimport('joomla.filesyste.file');

        $overwriteImages = JRequest::getInt('overwrite_imgs', 0);
        $localFile = JRequest::getVar('local_file', '');

        $backupFile = $localFile;
        if (JFile::upload($_FILES['backup']['tmp_name'], JPATH_ROOT . DS . 'tmp' . DS . 'auction_restore.zip')) {
            $backupFile = JPATH_ROOT . DS . 'tmp' . DS . 'auction_restore.zip';
        }

        if(!JFile::exists($backupFile)) {
            return JError::raiseError(1, 'Invalid backup file!');
        }

        $model = $this->getModel('backuprestore');


        $model->extractBackup($backupFile);

        if ($overwriteImages) {
            $model->restoreImages();
        }

        /**
         * Safety Backup for current database
         */
        $aSqlData = $model->backupSQL();

        $safetyBackupFile = JPATH_ROOT . DS . "media" . APP_EXTENSION . DS . 'auction_sql_' . date('d-M-Y_H-i-s') . '.zip';
        $zip =  JArchive::getAdapter('zip');
        $zip->create($safetyBackupFile, $aSqlData);
        $msg = ' A sql backup was made before restoration here <span style="color:green;">' . $safetyBackupFile . '</span>.';
        JError::raiseNotice(1, $msg);

        /**
         * END Safety Backup Actual Database
         */

        $msg = $model->restoreDatabase();
        if ($msg !== true) {
            JError::raiseError(1, $msg);
        }
        $model->cleanRestore();

        JFactory::getApplication()->redirect("index.php?option=" . APP_EXTENSION, "Backup restored!");
        return true;
    }

    function backupform() {

        $view =$this->getView('backupform');
        $view->display();
    }

    function  backup() {

        $download_backup = JRequest::getInt('download_backup', 0);
        $remove_backup = JRequest::getInt('remove_backup', 0);

        $model = $this->getModel('backuprestore');
        $backupFile = $model->backup();
        $fileName = JFile::getName($backupFile);

        if ($download_backup) {

            $backupSize = filesize($backupFile);

            ob_end_clean();

            header("Pragma: public");
            header("Expires: 0");
            //HTTP/1.1
            header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
            //HTTP/1.0
            header("Content-Type: application/x-compressed");
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header("Content-Length: " . $backupSize);
            header("Content-size: " . $backupSize);
            header('Content-Transfer-Encoding: binary');

            readfile($backupFile);
            if ($remove_backup) {
                JFile::delete($backupFile);
            }
            jexit();
        } else {
            JError::raiseNotice(1, 'Backup saved! Download here <span style="color:green;">' . $backupFile . '</span>.');
            JFactory::getApplication()->redirect("index.php?option=com_bids&task=auctionmanager");
        }
    }

    function installCB() {
        require_once(JPATH_SITE . DS . 'components' . DS . 'com_bids' . DS . 'installer' . DS . 'bids_installer.php');
        $installer = new TheFactoryBIDSInstaller('com_bids', JPATH_SITE . DS . 'components' . DS . 'com_bids' . DS . 'installer', 'bids.xml');
        $installer->AddCBPlugin('Auction Factory - My Auctions', 'My Auctions', 'auction_my_auctions', 'getmyauctionsTab');
        $installer->AddCBPlugin('Auction Factory - My Bids', 'My Bids', 'auction_my_bids', 'getmybidsTab');
        $installer->AddCBPlugin('Auction Factory - My Ratings', 'My Ratings', 'auction_my_ratings', 'getmyratingsTab');
        $installer->AddCBPlugin('Auction Factory - My Settings', 'My Settings', 'auction_my_settings', 'getmysettingsTab');
        $installer->AddCBPlugin('Auction Factory - My Taskpad', 'My Taskpad', 'auction_my_taskpad', 'myTaskPad');
        $installer->AddCBPlugin('Auction Factory - My Watchlist', 'My Watchlist', 'auction_my_watchlist', 'getmywatchlistTab');
        $installer->AddCBPlugin('Auction Factory - My Won Bids', 'My Won Bids', 'auction_my_wonbids', 'getmywonbidsTab');
        $installer->install();
        $this->setRedirect('index.php?option=com_bids&task=integration', JText::_('COM_BIDS_CB_PLUGINS_INSTALLED'));
    }

    function exportToXls() {
        $database =  JFactory::getDBO();

        $sqlQ =
            'SELECT
                a.*, d.title AS catname, e.name as username,
                MAX(bbids.bid_price) as highest_bid
            FROM #__bid_auctions a
            LEFT JOIN #__bids as bbids ON bbids.auction_id=a.id
            LEFT JOIN #__categories d ON a.cat = d.id
            LEFT JOIN #__bid_users e ON a.userid = e.userid
            GROUP BY a.id';

        $database->setQuery($sqlQ);
        $result = $database->loadObjectList();

        $filename = 'Auction_Factory_Export_' . JHtml::date('now','d-M-Y_H-i-s') . '.xls';
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
        header("Pragma: public");
        ob_clean();

        echo BidsHelperTools::createXLS($result);
        ob_end_flush();
        exit;
    }

    function importcsv() {

        $err = BidsHelperTools::ImportFromCSV(1);
        if (count($err) == 0) {
            $this->setRedirect('index.php?option=com_bids&task=showadmimportform', JText::_('COM_BIDS_SUCCES'));
        } else {
            $msg = '';
            foreach ($err as $e) {
                $msg .= JText::_('COM_BIDS_LINE') . '&nbsp;' . $e . '<br />';
            }
            $this->setRedirect('index.php?option=com_bids&task=showadmimportform', $msg, 'notice');
        }
    }

    function showadmimportform() {
        $view = $this->getView('importcsv');
        $view->display();
    }

    function showrestoreform() {
        $view = $this->getView('restorebackup');
        $view->display();
    }

    function PurgeCache() {
        jimport('joomla.filesystem.folder');         
    	$dir= AUCTION_TEMPLATE_CACHE;
        if(!JFolder::exists($dir)) {
            JFolder::create($dir);
        }
    	$requested_by = $_SERVER['HTTP_REFERER'];
    	if(JFolder::exists($dir)) {
    		if(is_writable($dir)) {
    			$smarty = new JTheFactorySmarty();
    			$smarty->clear_compiled_tpl();
    			$this->setRedirect($requested_by, JText::_("COM_BIDS_CACHED_CLEARED") );
    		} else {
                $this->setRedirect($requested_by, JText::_("COM_BIDS_PERMISSION_UNAVAILABLE_FOR_THIS") );
            }
    	} else {
            $this->setRedirect($requested_by, JText::_("COM_BIDS_CACHE_FOLDER_DOESNT_EXIST") );
        }

    }

    function bidshistory() {

        $db = JFactory::getDbo();
        $input = JFactory::getApplication()->input;

        $auctionId = $input->getInt('id',0);

        $auction = JTable::getInstance('auction');
        $auction->load($auctionId);

        $query = $db->getQuery(true);
        $query->select('b.*,u.username')
                ->from('#__bids AS b')
                ->leftJoin('#__users AS u ON b.userid=u.id')
                ->where('b.auction_id='.$db->quote($auctionId));
        $db->setQuery($query);

        $bids = $db->loadObjectList();

        $view = $this->getView('bidshistory');

        $view->assign('bids',$bids);
        $view->assign('auction',$auction);

        $view->display();
    }

    public function Cronjob_Info()
    {
        $cfg = JTheFactoryHelper::getConfig();
        $db = JFactory::getDBO();
        $db->setQuery("select * from #__bid_cronlog where event='cron' order by logtime desc limit 1");
        $log = $db->loadObject();

        $view = $this->getView('settingspanel');
        $view->assignref('cfg', $cfg);
        $view->assignref('cronlog', $log);
        $view->display('cronsettings');
    }

    public function showCatQuickAdd() {

        $view = $this->getView('catquickadd');
        $view->display();
    }

    public function quicksavecats() {

        $input = JFactory::getApplication()->input;

        $catstext = $input->getVar('catstext');
        $parentId = $input->getVar('parent');

        BidsHelperCategory::saveCategories($catstext,$parentId);

        //close modal
        $document = JFactory::getDocument();
        $js = 'window.parent.SqueezeBox.close()';
        $document->addScriptDeclaration($js);
    }

    function saveauction()
    {
        $id = JRequest::getInt('id', 0);

        $auctionmodel = $this->getModel('auction');
        $auctionmodel->bind(JRequest::get('post', JREQUEST_ALLOWHTML), JRequest::get('files'),true);

        $auction = $auctionmodel->get('auction');

        $extra = '';
        if ($id) {
            $redirectTask = 'editauction';
            $extra = '&id=' . $id;
        } else {
            $redirectTask = 'newauction';
        }
        $redirectURL = JRoute::_('index.php?option=com_bids&task=' . $redirectTask . $extra, false);

        if (!$auctionmodel->save()) {
            $auctionmodel->saveToSession(); //for refilling fields on edit
            $this->setRedirect($redirectURL);
            return;
        }

        $auctionmodel->clearSavedSession();

        $this->setRedirect('index.php?option=com_bids&task=offers', JText::_('COM_BIDS_SUCCES'));
    }

    function RefreshCategory()
    {
        $cfg = BidsHelperTools::getConfig();

        $id = JRequest::getInt('id', 0);
        $oldid = JRequest::getInt('oldid', 0);
        $isRepost = $oldid && !$id;

        $auctionmodel = $this->getModel('auction');
        $auctionmodel->bind(JRequest::get('post', JREQUEST_ALLOWHTML), JRequest::get('files'), true);
        $auctionmodel->saveToSession();

        $auction = $auctionmodel->get('auction');

        $extra = '';
        if ($isRepost) {
            $redirectTask = 'republish';
        } elseif ($id) {
            $redirectTask = 'editoffer';
            $extra = '&id=' . $id;
        } else {
            $redirectTask = 'newauction';
            $extra = ('catpage' == $cfg->bid_opt_category_page) ? '&category_selected=' . $auction->cat : '';
        }
        $redirectURL = JRoute::_('index.php?option=com_bids&task=' . $redirectTask . $extra, false);

        $this->setRedirect($redirectURL);
    }

    function jsgen() {

        $input = JFactory::getApplication()->input;
        $view = $input->getCmd('view','');

        ob_clean();
        switch($view) {
            case 'editauction':
                require JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'javascript.php';
                break;
        }
    }
}
