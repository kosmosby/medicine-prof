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


class com_bidsInstallerScript {

    protected $installer;

    function preflight($route,$adapter) {

        jimport('joomla.filesystem.file');

        $currentApplicationFile = JPATH_COMPONENT_ADMINISTRATOR.DS.'thefactory'.DS.'application'.DS.'application.class.php';
        if(JFile::exists($currentApplicationFile)) {
            require_once $currentApplicationFile;

            $currentApplication = JTheFactoryApplication::getInstance();
            $currentVersion = (string) $currentApplication->getIniValue('version');

            if($currentVersion < '3.0.0' ) {
                JError::raiseError(500,'Can not upgrade from version '.$currentVersion.'!');
                return false;
            }
        }

        require_once($adapter->getParent()->getPath('source').DS.'administrator'.DS.'components'.DS.'com_bids'.DS.'thefactory'.DS.'installer'.DS.'installer.php');
        require_once($adapter->getParent()->getPath('source').DS.'components'.DS.'com_bids'.DS.'installer'.DS.'bids_installer.php');

        $this->installer = new TheFactoryBIDSInstaller('com_bids',$adapter);
    }

    function install($adapter) {

        $installer = $this->installer;

        $installer->AddSQLFromFile('install.bids.inserts.sql');

        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');

        JFolder::move(JPATH_SITE.DS.'components'.DS.'com_bids'.DS.'templates-dist',
            JPATH_SITE.DS.'components'.DS.'com_bids'.DS.'templates');
        JFile::move(JPATH_SITE.DS.'components'.DS.'com_bids'.DS.'options.php-dist',
            JPATH_SITE.DS.'components'.DS.'com_bids'.DS.'options.php');
        JFolder::create(JPATH_SITE.DS.'images'.DS.'auctions');

        $installer->AddMenuItem("Auction Factory MENU","List Auctions", "list-auctions", "index.php?option=com_bids&task=listauctions", 1 );
        $installer->AddMenuItem("Auction Factory MENU","Categories", "categories-auctions", "index.php?option=com_bids&task=listcats", 1 );
        $installer->AddMenuItem("Auction Factory MENU","Search", "search-auctions", "index.php?option=com_bids&task=search", 1 );
        $installer->AddMenuItem("Auction Factory MENU","New Auction", "post-offer", "index.php?option=com_bids&task=newauction", 2 );
        $installer->AddMenuItem("Auction Factory MENU","My Auctions", "my-auctions", "index.php?option=com_bids&task=myauctions", 2 );
        $installer->AddMenuItem("Auction Factory MENU","Watchlist", "watchlist", "index.php?option=com_bids&task=mywatchlist", 2 );
        $installer->AddMenuItem("Auction Factory MENU","My Bids", "my-bids", "index.php?option=com_bids&task=mybids", 2 );
        $installer->AddMenuItem("Auction Factory MENU","Profile", "profile-auctions", "index.php?option=com_bids&task=userdetails", 2 );

        $installer->AddCBPlugin('Bids MyAuctions','My Auctions','bids.myauctions','getmyauctionsTab');
        $installer->AddCBPlugin('Bids MyBids','My Bids','bids.mybids','getmybidsTab');
        $installer->AddCBPlugin('Bids MyRatings','My Ratings','bids.myratings','getmyratingsTab');
        $installer->AddCBPlugin('Bids MySettings','My Settings','bids.mysettings','getmysettingsTab');
        $installer->AddCBPlugin('Bids MyTaskpad','My Taskpad','bids.mytaskpad','myTaskPad');
        $installer->AddCBPlugin('Bids MyWatchlist','My Watchlist','bids.mywatchlist','getmywatchlistTab');
        $installer->AddCBPlugin('Bids MyWonBids','My Won Bids','bids.mywonbids','getmywonbidsTab');

        $installer->AddMessage("Thank you for purchasing <strong>Auctions Factory</strong>");

        $installer->AddMessageFromFile('install.notes.txt');

        $installer->AddMessage("Please set up your <strong>Auctions Factory</strong> in the <a href='".JURI::root()."administrator/index.php?option=com_bids&task=settingsmanager'>admin panel</a></p>");
        $installer->AddMessage("Visit us at <a target='_blank' href='http://www.thefactory.ro'>thefactory.ro</a> to learn  about new versions and/or to give us feedback<br>");
        $installer->AddMessage("&copy; 2006-".date('Y')." thefactory.ro");

        $installer->insertDefaultCategory();

        $installer->install();
    }

    function update($adapter) {

        $installer = $this->installer;

        $installer->upgrade();
        $installer->AddMessage("<h1>Your upgrade from Auction Factory v. ".$installer->versionprevious." to v. ".$installer->version." has finished</h1>");
        $installer->AddMessage($installer->askTemplateOverwrite());
    }

    function postflight($type,&$adapter) {

        //type = install, update OR discover_install

        $db = JFactory::getDbo();
        $app = JFactory::getApplication();

        //finish install menu items
        $q = $db->getQuery(true);
        $q->select('extension_id')
            ->from('#__extensions')
            ->where('type=\'component\' AND element=\'com_bids\'');
        $db->setQuery($q);
        $extension_id = $db->loadResult();

        $q = $db->getQuery(true);
        $q->update('#__menu')
            ->set('component_id='.$extension_id)
            ->where('link LIKE \'index.php?option=com_bids%\'');
        $db->setQuery($q);
        $db->query();

        $error = is_array($this->installer->errors)?implode("<br/>",$this->installer->errors):$this->installer->errors;
        $warning = is_array($this->installer->warnings)?implode("<br/>",$this->installer->warnings):$this->installer->warnings;
        $message = is_array($this->installer->message) ? implode("\r\n",$this->installer->message):$this->installer->message;

        if ($error) JError::raiseWarning(100, $error);
        if ($warning) JError::raiseNotice(1, $warning);

        $session = JFactory::getSession();
        $session->set('com_bids_install_msg',$message);
    }
}
