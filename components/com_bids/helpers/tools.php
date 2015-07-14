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



defined('_JEXEC') or die('Restricted access');

class BidsHelperTools {

    function cloack_email($mail_address) {
        $cfg=BidsHelperTools::getConfig();

        if ($cfg->bid_opt_enable_antispam_bot) {
            if ($cfg->bid_opt_choose_antispam_bot == "recaptcha") {
                require(JPATH_COMPONENT_SITE . DS . 'helpers' . DS . 'recaptcha' . DS . 'recaptchalib.php');
                $mail = recaptcha_mailhide_url("01WxCXdKklKdG2JpOlMY15jw==", "2198178B23BFFB00CBAEA6370CE7A0B2", $mail_address);
                return "<a href=\"$mail.\" onclick=\"window.open('$mail', '', 'toolbar=0,scrollbars=0,location=0,statusbar=0, menubar=0,resizable=0,width=500,height=300');	return false;\" title=\"Reveal this e-mail address\">" . JText::_("COM_BIDS_SHOW_EMAIL") . "</a>";
            } elseif ($cfg->bid_opt_choose_antispam_bot == "joomla") {

                // Discutable if use Content Mail Plugin or ... just JHTML_('email.cloac' it does the same .. just global configuration is in question
                $plugin = JPluginHelper::getPlugin('content', 'emailcloak');
                $pluginParams = new JParameter($plugin->params);
                require_once (JPATH_SITE . DS . 'plugins' . DS . 'content' . DS . 'emailcloak.php');
                plgContentEmailCloak($mail_address, $pluginParams);
                return $mail_address;
            } elseif ($cfg->bid_opt_choose_antispam_bot == "smarty") {
                $smarty = new JTheFactorySmarty();
                require_once(SMARTY_DIR . "plugins/function.mailto.php");
                return BidsSmarty::smarty_rbids_print_encoded(array("address" => $mail_address, "encode" => "hex"), $smarty);
            }
        } else {
            return $mail_address;
        }
    }

    static function getMenuItemId($needles) {

        $Itemid = JRequest::getInt('Itemid');

        return $Itemid;

        if (!is_array($needles)) {
            if ($needles)
                $needles = array($needles);
            else
                $needles = array();
        }
        $needles['option'] = 'com_bids';

        $menus = JApplication::getMenu('site', array());
        $items = $menus->getItems('query',$needles);

        if (!count($items))
            return $Itemid; //no extension menu items

        $match = reset($items); //fallback first encountered Menuitem

        foreach ($items as $item) {
            if ($match->access != 0 && $item->access == 0) {
                $match = $item; //even better fallback is one that has public access
                continue;
            }

            $xssmatch1 = array_intersect_assoc($item->query, $needles);
            $xssmatch2 = array_intersect_assoc($match->query, $needles);
            if (count($xssmatch1) > count($xssmatch2)) { //better needlematch
                $match = $item; //even better fallback is one that has public access
                continue;
            }
        }

        return $match->id ? $match->id : $Itemid;
    }

    function auctionDatetoIso($date) {

        $cfg = BidsHelperTools::getConfig();

        switch($cfg->bid_opt_date_format) {
            case 'Y-m-d':
                if(preg_match('#([0-9]+)-([0-9]+)-([0-9]+)#',$date,$a)) {
                    list($y,$m,$d) = array($a[1], $a[2], $a[3]);
                }
                break;
            case 'm/d/Y':
                if(preg_match('#([0-9]+)/([0-9]+)/([0-9]+)#',$date,$a)) {
                    list($y,$m,$d) = array($a[3], $a[1], $a[2]);
                }
                break;
            case 'd/m/Y':
                if(preg_match('#([0-9]+)/([0-9]+)/([0-9]+)#',$date,$a)) {
                    list($y,$m,$d) = array($a[3], $a[2], $a[1]);
                }
                break;
            case 'd.m.Y':
                if(preg_match('#([0-9]+).([0-9]+).([0-9]+)#',$date,$a)) {
                    list($y,$m,$d) = array($a[3], $a[2], $a[1]);
                }
                break;
            case 'D, F d Y':
                list($y,$m,$d) = explode('-',date('Y-m-d',strtotime($date)));
                break;
        }

        if(!isset($y) && !isset($m) && !isset($d)) {
            return null;
        }

        return $y.'-'.$m.'-'.$d;
    }

    function includeCbApi() {

        $filePath = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_comprofiler'.DS.'plugin.foundation.php';
        if (!file_exists($filePath)) {
            echo 'CB not installed!';
            return;
        }

        include_once( $filePath );
    }

    function ImportFromCSV( $withids=0) {

        $database = JFactory::getDBO();
        $my = JFactory::getUser();
        $cfg=BidsHelperTools::getConfig();

        jimport('joomla.filesystem.file');
        jimport('joomla.filesystem.archive');

        $tmpdir = uniqid('images_');
        $base_Dir = JPath::clean(JPATH_ROOT . DS . 'media') . DS;
        $extractdir = JPath::clean($base_Dir . $tmpdir) . DS;

        define('_TMP_DIR', JPATH_ROOT . "/media/");
        $shipzones = null;
        if ($cfg->bid_opt_multiple_shipping) {
            $database->setQuery("SELECT id,name FROM `#__bid_shipment_zones`");
            $shipzones = $database->loadObjectList("name");
        }

        $database->setQuery("SELECT name FROM #__bid_currency");
        $allowedCurrencies = $database->loadResultArray();

        $auction = JTable::getInstance('auction');
        $errors = array();
        $msg = '';
        if ($_FILES['csv']['tmp_name']) {
            $csv_fname = $_FILES['csv']['name'];
            $ext = substr($csv_fname, strrpos($csv_fname, '.') + 1, strlen($csv_fname));
            if ($ext != "csv") {
                $msg .= bid_err_csv_ext_not_allowed;
            }
            $upload_path = _TMP_DIR . $csv_fname;
            if (!move_uploaded_file($_FILES['csv']['tmp_name'], $upload_path)) {
                $msg .= bid_err_csv_import_error;
            }
            @chmod($upload_path, 0777);
            if (!file_exists($upload_path)) {
                $msg .= bid_err_file_not_there;
            }
            $handle = fopen($upload_path, "r");
            set_time_limit(0);
            $i = 1;

            if ($_FILES['arch']['tmp_name']) {
                $upload_arch = $base_Dir . $_FILES['arch']['name'];
                if (!move_uploaded_file($_FILES['arch']['tmp_name'], $upload_arch)) {
                    $msg .= JText::_('COM_BIDS_ERR_CSV_IMPORT_ERROR');
                }
                @chmod($upload_arch, 0777);
                $archivename = $upload_arch;
                @mkdir($extractdir);
                $archivename = JPath::clean($archivename);

                $archiver = JArchive::getAdapter('zip');
                $archiver->extract($archivename, $extractdir);

                //echo $archivename;
                $ret = $archiver->extract($archivename, $extractdir);
                if ($ret == 0) {
                    die('Unrecoverable error ' . $archiver->get('error.message') . '"');
                }
            }

            while (($data = fgetcsv($handle, 30000, "\t")) !== FALSE) {

                $params['add_picture'] = $data[16];
                $params['auto_accept_bin'] = $data[17];
                $params['bid_counts'] = $data[18];
                $params['max_price'] = $data[19];
                if (is_array($params)) {
                    $txt = array();
                    foreach ($params as $k => $v) {
                        $txt[] = "$k=$v";
                    }
                    $auction->params = implode("\n", $txt);
                }
                $auction->id = null;
                $auction->title = strip_tags($data[1]);
                $auction->shortdescription = strip_tags($data[2]);
                $auction->description = preg_replace('/<script[^>]*?>.*?<\/script>/si', '', $data[3]); //de testat
                $auction->initial_price = floatval($data[6]);
                if(!in_array($data[7], $allowedCurrencies)) {
                    JError::raiseNotice(0, 'Currency '.$data[7].' not allowed!');
                    continue;
                }
                $auction->currency = $data[7];

                $auction->BIN_price = floatval($data[8]);
                $auction->auction_type = $data[9];
                switch ($auction->auction_type) {
                    case 'public':
                        $auction->auction_type = 1;
                        break;
                    case 'private':
                        $auction->auction_type = 2;
                        break;
                }
                $auction->automatic = $data[10];

                $auction->shipment_info = $data[12];
                $auction->start_date = date("Y-m-d H:i:s", strtotime($data[13]));
                $auction->end_date = date("Y-m-d H:i:s", strtotime($data[14]));
                $auction->published = $data[20];
                $auction->SetCategory($data[21]);
                if ($withids && intval($data[0]) > 0) {
                    $database->setQuery('SELECT id FROM #__users WHERE id='.$database->quote($data[0]));
                    $userid = $database->loadResult();
                    if(!$userid) {
                        $userid = $my->id;
                    }
                } else {
                    $userid = $my->id;
                }
                $auction->userid = $userid;

                $auction->auction_nr = time() . rand(100, 999);
                // min increase column missing
                $auction->min_increase = $cfg->bid_opt_min_increase;

                $auction->payment_method = $data[11];

                //$err = $admin ? $auction->check() : array();
                //$err = $auction->check();
                //TODO: auction validating through model

                $arr_pics = array();
                $k = 22;//pozitia lu first aux picture
                $j = 32;
                // J ia valoarea lui "Shipment"
                if ($shipzones != null) {
                    $sh = array_search("Shipment", $data);
                    if ($sh != false) {
                        $j = $sh;
                    }
                }

                for ($m = $k; $m < $j; $m++) {
                    if ($data[$m]) {
                        $arr_pics[] = $data[$m];
                    }
                }
                if ($data[15]) {
                    array_unshift($arr_pics, $data[4]); //add what used to be the "main picture"
                }

                $err = array();

                if (count($err) <= 0) {

                    $auction->store(true);

                        $nrfiles = 0;
                        for ($m = 0; $m < count($arr_pics); $m++) {
                            if ($nrfiles >= $cfg->bid_opt_maxnr_images)
                                continue;
                            if (file_exists($extractdir . $arr_pics[$m])) {
                                if (filesize($extractdir . $arr_pics[$m]) > $cfg->bid_opt_max_picture_size * 1024) {
                                    $msg.=$arr_pics[$m] . "- " . JText::_('COM_BIDS_ERR_IMAGESIZE_TOO_BIG') . "<br><br>";
                                    continue;
                                }
                                $fname = $arr_pics[$m];
                                $ext = JFile::getExt($fname);
                                if ( !in_array( $ext, array('JPEG','JPG','GIF','PNG') ) ) {
                                    $msg .= JText::_('COM_BIDS_ERR_NOT_ALLOWED_EXT') . ': ' . $fname;
                                    continue;
                                }
                                $file_name = $fname;
                                $pic = JTable::getInstance('bidimage');
                                $pic->auction_id = $auction->id;
                                $pic->picture = $file_name;
                                $pic->modified = gmdate('Y-m-d H:i:s');
                                $pic->store();
                                $file_name = $auction->id . "_img_$pic->id.$ext";
                                $pic->picture = $file_name;
                                $pic->store();
                                $path = AUCTION_PICTURES_PATH . DS . "$file_name";
                                if (rename(JPATH_ROOT . DS . "media" . DS . $tmpdir . DS . $arr_pics[$m], $path)) {
                                    BidsHelperTools::resize_image($file_name, $cfg->bid_opt_thumb_width, $cfg->bid_opt_thumb_height, 'resize');
                                    BidsHelperTools::resize_image($file_name, $cfg->bid_opt_medium_width, $cfg->bid_opt_medium_height, 'middle');
                                } else {
                                    $msg.=$arr_pics[$m] . "- " . JText::_('COM_BIDS_ERR_UPLOAD_FAILED') . "<br><br>";
                                }
                                $nrfiles++;
                            }
                        }
                    //arch pics

                    // m ia valoarea indexului lui "Shipment" + 1
                    if ($shipzones != null) {

                        $arr_ships = array();
                        $m = array_search("Shipment", $data);
                        $m = $m + 1;
                        while ($m && $data[$m]) {
                            if (!$data[$m]) {
                                break;
                            } else {
                                $ship_id = false;
                                if (isset($shipzones[$data[$m]]))
                                    $ship_id = $shipzones[$data[$m]]->id;
                                if ($ship_id !== false) {
                                    $arr_ships[] = "(NULL, {$ship_id} , {$data[$m + 1]}, {$auction->id})";
                                }
                            }
                            $m = $m + 2;
                        }

                        if (count($arr_ships)) {
                            $sql_ships = "INSERT INTO #__bid_shipment_prices VALUES " . implode(",", $arr_ships);
                            $database->setQuery($sql_ships);
                            $database->query();
                        }
                    }
                }else
                    $errors[] = "$i : " . join('<br>', $err);
                $i++;
            }
            if (file_exists($extractdir)) {
                JFolder::delete($extractdir);
            }
            fclose($handle);
            if (file_exists($base_Dir . $_FILES['csv']['name']))
                unlink($base_Dir . $_FILES['csv']['name']);
            if (file_exists($base_Dir . $_FILES['csv']['name']))
                unlink($base_Dir . $_FILES['arch']['name']);
        }

        return $errors;
    }

    function createXLS($result) {

        ob_clean();
        ob_start();
        set_time_limit(0);
        error_reporting(0);
        require_once JPATH_COMPONENT_SITE . '/libraries/pear/PEAR.php';
        require_once (JPATH_ROOT . '/components/com_bids/libraries/Excel/Writer.php');
        // Creating a workbook
        $workbook = new Spreadsheet_Excel_Writer();
        $workbook->setTempDir(JPATH_ROOT.DS.'tmp');
        $worksheet = $workbook->addWorksheet('Exported Auctions');
        $BIFF = new Spreadsheet_Excel_Writer_BIFFwriter();
        $format = new Spreadsheet_Excel_Writer_Format($BIFF);
        $format->setBold(1);
        $format->setAlign('center');

        $colNr = 0;

        $worksheet->write(0, $colNr++, "User", $format);
        $worksheet->write(0, $colNr++, "Title", $format);
        $worksheet->write(0, $colNr++, "Short description");
        $worksheet->write(0, $colNr++, "Description");
        $worksheet->write(0, $colNr++, "Initial price");
        $worksheet->write(0, $colNr++, "Currency");
        $worksheet->write(0, $colNr++, "BIN price");
        $worksheet->write(0, $colNr++, "Auction type");
        $worksheet->write(0, $colNr++, "Automatic");
        $worksheet->write(0, $colNr++, "Payment");
        $worksheet->write(0, $colNr++, "Shipment Info");
        $worksheet->write(0, $colNr++, "Start date");
        $worksheet->write(0, $colNr++, "End date");
        $worksheet->write(0, $colNr++, "Param: picture");
        $worksheet->write(0, $colNr++, "Param: add_picture");
        $worksheet->write(0, $colNr++, "Param: auto_accept_bin");
        $worksheet->write(0, $colNr++, "Param: bid_counts");
        $worksheet->write(0, $colNr++, "Param: max_price");
        $worksheet->write(0, $colNr++, "Published");
        $worksheet->write(0, $colNr++, "Category");
        $worksheet->write(0, $colNr++, "Highest Bid");
        for ($i = 0; $i < count($result); $i++) {
            $colNr = 0;
            $worksheet->write($i + 1, $colNr++, $result[$i]->username);
            $worksheet->write($i + 1, $colNr++, $result[$i]->title);
            $worksheet->write($i + 1, $colNr++, $result[$i]->shortdescription);
            $worksheet->write($i + 1, $colNr++, $result[$i]->description);
            $worksheet->write($i + 1, $colNr++, $result[$i]->initial_price);
            $worksheet->write($i + 1, $colNr++, $result[$i]->currency);
            $worksheet->write($i + 1, $colNr++, $result[$i]->BIN_price);
            switch ($result[$i]->auction_type) {
                case 1:
                    $worksheet->write($i + 1, $colNr++, 'public');
                    break;
                case 2:
                    $worksheet->write($i + 1, $colNr++, 'private');
                    break;
                case 3:
                    $worksheet->write($i + 1, $colNr++, 'BIN only');
                    break;
            }
            $worksheet->write($i + 1, $colNr++, $result[$i]->automatic);
            $worksheet->write($i + 1, $colNr++, $result[$i]->payment_method);
            $worksheet->write($i + 1, $colNr++, $result[$i]->shipment_info);
            $worksheet->write($i + 1, $colNr++, $result[$i]->start_date);
            $worksheet->write($i + 1, $colNr++, $result[$i]->end_date);
            $params = explode("\n", $result[$i]->params);
            $tmp = explode("=", $params[0]); // picture param
            $worksheet->write($i + 1, $colNr++, $tmp[1]);
            $tmp = explode("=", $params[1]); // add_picture
            $worksheet->write($i + 1, $colNr++, $tmp[1]);
            $tmp = explode("=", $params[2]); // auto_accept_bin
            $worksheet->write($i + 1, $colNr++, $tmp[1]);
            $tmp = explode("=", $params[3]); // bid_counts
            $worksheet->write($i + 1, $colNr++, $tmp[1]);
            $tmp = explode("=", $params[4]); // max_price
            $worksheet->write($i + 1, $colNr++, $tmp[1]);

            $worksheet->write($i + 1, $colNr++, $result[$i]->published);
            $worksheet->write($i + 1, $colNr++, $result[$i]->catname);
            $worksheet->write($i + 1, $colNr++, $result[$i]->highest_bid);

            $worksheet->setColumn(0, 0, 9);
            $worksheet->setColumn(1, 12, 25);
        }
        $workbook->close();
        $attachment = ob_get_clean();

        return $attachment;
    }

    function resize_image($picture, $width, $height, $prefix) {

        require_once(JPATH_COMPONENT_SITE . DS . 'thefactory' . DS . 'front.images.php');
        $imgTrans = new JTheFactoryImages();
        return $imgTrans->resize_image(AUCTION_PICTURES_PATH . DS . $picture, $width, $height, $prefix);
    }

    function resize_to_filesize($file, $outputfile, $maxfilesize) {
        require_once(JPATH_COMPONENT_SITE . DS . 'thefactory' . DS . 'front.images.php');
        $imgTrans = new JTheFactoryImages();

        return $imgTrans->resize_to_filesize(AUCTION_PICTURES_PATH . DS . $file, $outputfile, $maxfilesize);
    }

    static function getProfileMode() {

        $cfg=BidsHelperTools::getConfig();
        if ($cfg->bid_opt_profile_mode) {
            return $cfg->bid_opt_profile_mode;
        }

        return 'component';
    }

    static function getUserProfileObject($userid=0) {

        $profile_mode = self::getProfileMode();

        return BidsHelperProfile::getInstance($profile_mode,$userid);
    }

    static function redirectToProfile($id=null) {

        $userprofile = self::getUserProfileObject();

        return $userprofile->getProfileLink($id);
    }

    static function &getCategoryModel()
    {
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_bids'.DS.'thefactory'.DS.'category'.DS.'models');
        $catModel= JModelLegacy::getInstance('Category','JTheFactoryModel');
        return $catModel;
    }
    static function &getCategoryTable()
    {
        JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_bids'.DS.'thefactory'.DS.'category'.DS.'tables');
        $catTable= JTable::getInstance('Category','JTheFactoryTable');
        return $catTable;
    }

    static function &getBidsACL() {

        static $acl=null;
        if(is_object($acl)) return $acl;
        require_once(JPATH_SITE.DS.'components'.DS.'com_bids'.DS.'bids.acl.php');
        $acl = new JBidsACL();
        return $acl;
    }
    static function getItemsPerPage()
    {
        $jconfig = JFactory::getConfig();
        $cfg = BidsHelperTools::getConfig();

        return $cfg->bid_opt_nr_items_per_page>0 ? $cfg->bid_opt_nr_items_per_page : $jconfig->getValue('config.list_limit');         
    }

    static function getConfig() {

        if(!class_exists('bidconfig')) {
            require JPATH_ROOT.DS.'components'.DS.'com_bids'.DS.'options.php';
        }

        return new BidConfig();
    }
}
