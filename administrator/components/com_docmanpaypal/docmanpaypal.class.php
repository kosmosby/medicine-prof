<?php
error_reporting(0);
defined('_JEXEC') or die('Access Denied');

JTable::addIncludePath( JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_docmanpaypal' . DS . 'tables' );               

/**
* @author Deian Motov <bbhsoft@gmail.com>
* @name DOCman PayPal Class
* @copyright Under GPL license
* @example $dmp = new docmanpaypal;
*/
	$mainframe = JFactory::getApplication();
	$my = JFactory::getUser();
	$jc = new JConfig();
	$mosConfig_db = $jc->db;
	$mosConfig_dbprefix = $jc->dbprefix;
	$mosConfig_host = $jc->host;
	$mosConfig_password = $jc->password;
	$mosConfig_user = $jc->user;
	
	$componentdir = '/components/com_docmanpaypal';

	//echo $mosConfig_dbprefix;
	## Defines only available in PHP 5, created for PHP4
    if(!defined('PHP_URL_SCHEME')) define('PHP_URL_SCHEME', 1);
    if(!defined('PHP_URL_HOST')) define('PHP_URL_HOST', 2);
    if(!defined('PHP_URL_PORT')) define('PHP_URL_PORT', 3);
    if(!defined('PHP_URL_USER')) define('PHP_URL_USER', 4);
    if(!defined('PHP_URL_PASS')) define('PHP_URL_PASS', 5);
    if(!defined('PHP_URL_PATH')) define('PHP_URL_PATH', 6);
    if(!defined('PHP_URL_QUERY')) define('PHP_URL_QUERY', 7);                       
    if(!defined('PHP_URL_FRAGMENT')) define('PHP_URL_FRAGMENT', 8); 
    
        function parse_url_compat($url, $component=NULL){
       
        if(!$component) return parse_url($url);
       
        ## PHP 5
        if(phpversion() >= 5)
            return @parse_url($url, $component);
        ## PHP 4
        $bits = parse_url($url);
       
        switch($component){
            case PHP_URL_SCHEME: return $bits['scheme'];
            case PHP_URL_HOST: return $bits['host'];
            case PHP_URL_PORT: return $bits['port'];
            case PHP_URL_USER: return $bits['user'];
            case PHP_URL_PASS: return $bits['pass'];
            case PHP_URL_PATH: return $bits['path'];
            case PHP_URL_QUERY: return $bits['query'];
            case PHP_URL_FRAGMENT: return $bits['fragment'];
        }
       
    }
    
class docmanpaypal {
	var $currencies = array(

        'AUD' => array('name' => "Australian Dollar", 'symbol' => "A$", 'ASCII' => "A&#36;"),

        'CAD' => array('name' => "Canadian Dollar", 'symbol' => "$", 'ASCII' => "&#36;"),

        'CZK' => array('name' => "Czech Koruna", 'symbol' => "Kč", 'ASCII' => ""),

        'DKK' => array('name' => "Danish Krone", 'symbol' => "Kr", 'ASCII' => ""),

        'EUR' => array('name' => "Euro", 'symbol' => "€", 'ASCII' => "&#128;"),

        'HKD' => array('name' => "Hong Kong Dollar", 'symbol' => "$", 'ASCII' => "&#36;"),

        'HUF' => array('name' => "Hungarian Forint", 'symbol' => "Ft", 'ASCII' => ""),

        'ILS' => array('name' => "Israeli New Sheqel", 'symbol' => "₪", 'ASCII' => "&#8361;"),

        'JPY' => array('name' => "Japanese Yen", 'symbol' => "¥", 'ASCII' => "&#165;"),

        'MXN' => array('name' => "Mexican Peso", 'symbol' => "$", 'ASCII' => "&#36;"),

        'NOK' => array('name' => "Norwegian Krone", 'symbol' => "Kr", 'ASCII' => ""),

        'NZD' => array('name' => "New Zealand Dollar", 'symbol' => "$", 'ASCII' => "&#36;"),

        'PHP' => array('name' => "Philippine Peso", 'symbol' => "₱", 'ASCII' => ""),

        'PLN' => array('name' => "Polish Zloty", 'symbol' => "zł", 'ASCII' => ""),

        'GBP' => array('name' => "Pound Sterling", 'symbol' => "£", 'ASCII' => "&#163;"),

        'SGD' => array('name' => "Singapore Dollar", 'symbol' => "$", 'ASCII' => "&#36;"),

        'SEK' => array('name' => "Swedish Krona", 'symbol' => "kr", 'ASCII' => ""),

        'CHF' => array('name' => "Swiss Franc", 'symbol' => "CHF", 'ASCII' => ""),

        'TWD' => array('name' => "Taiwan New Dollar", 'symbol' => "NT$", 'ASCII' => "NT&#36;"),

        'THB' => array('name' => "Thai Baht", 'symbol' => "฿", 'ASCII' => "&#3647;"),

        'USD' => array('name' => "U.S. Dollar", 'symbol' => "$", 'ASCII' => "&#36;"));
    
    function groupName($group_id) {
	    return $this->singleResult("select title from #__usergroups where id = $group_id limit 1");
    }
	function singleResult($sql) {
		$database = JFactory::getDBO(); 
		$database->setQuery($sql);
		return $database->loadResult();
	}
	function adminGetFilesInfo($search, $lim,$lim0) {
		$my = JFactory::getUser();
		$database = JFactory::getDBO(); 
		$myid = $my->id;
		//sync the ids
		
		if ($search != '') {
			$where = " and dm.title like '%$search%'";
		}
		
		$database->setQuery("select count(*) from #__docman_documents");
		$dmcount = $database->loadResult();
		$database->setQuery("select count(*) from #__docmanpaypal");
		$dmpcount = $database->loadResult();
		$database->setQuery("select sum(docman_document_id) from #__docman_documents");
		$dmsum = $database->loadResult();
		$database->setQuery("select sum(id) from #__docmanpaypal");
		$dmpsum = $database->loadResult();
		
		if ($dmpcount < $dmcount || $dmsum != $dmpsum) {
			$database->setQuery("select `docman_document_id` from `#__docman_documents`");
			$tmp = $database->loadAssocList();
				foreach ($tmp as $row) {
					$database->setQuery('select count(*) from #__docmanpaypal where id = ' . $row['docman_document_id']);
					if ($database->loadResult() == 0) {
						$database->setQuery("insert into #__docmanpaypal values ($row[docman_document_id],$myid, 0, 1, 99999, '0',0,'');");
						$database->query();
					}
				}
		}
		
		$database->setQuery("SELECT dm.docman_document_id AS id, dmp.vendor, dmp.offlineGood AS offlineGood, dmp.buttons AS buttons, dm.title AS name, dm.storage_path AS filename, cat.title AS category, dmp.price AS price, dmp.downloadslimit AS downloadslimit, dmp.saleslimit AS saleslimit
FROM  `#__docman_documents` dm,  `#__docman_categories` cat,  `#__docmanpaypal` dmp
WHERE dm.docman_category_id = cat.docman_category_id
AND dmp.id = dm.docman_document_id $where",$lim0,$lim);
		$ret = $database->loadAssocList();
		if (!$ret) {
			die(mysql_error());
		}
		return $ret;
	}
		function userGetFilesInfo() {
		$my = JFactory::getUser();
		$database = JFactory::getDBO(); 
		$myid = $my->id;
		//sync the ids

		$database->setQuery("SELECT sum(id) as dmsum, count(id) as dmcount FROM `#__docman` WHERE 1");
		$tmp = $database->loadAssocList();
		$database->setQuery("SELECT sum(id) as dmpsum, count(id) as dmpcount FROM `#__docmanpaypal` WHERE 1");
		$tmp1 = $database->loadAssocList();
		extract($tmp[0]);
		extract($tmp1[0]);
		//print_r($dmcount);
		//die();
		unset($tmp);
		if ($dmpcount != $dmcount || $dmsum != $dmpsum) {
			$database->setQuery("select `id` from `#__docman`");
			$tmp = $database->loadAssocList();
			foreach ($tmp as $row) {
				$database->setQuery("insert into #__docmanpaypal values ($row[id],$myid, 0);");
				$database->query();
			}
		}
		$database->setQuery("SELECT dm.id as id, dm.dmname as name, dm.dmfilename as filename, cat.title as category, dmp.price as price,  dmp.downloadslimit as downloadslimit, dmp.saleslimit as saleslimit FROM `#__docman` dm, `#__categories` cat, `#__docmanpaypal` dmp where dm.catid = cat.id and dmp.id = dm.id");
		$ret = $database->loadAssocList();
		if (!$ret) {
			$GLOBALS['DEBUG'] .= mysql_error() . "\n";
		}
		return $ret;
	}
	/**
	 * We pass an array to this and will generate one big query to update the prices
	 *
	 * @param array $prices
	 */
	function updatePrices($prices,$downloadslimit,$saleslimit,$offlineGood,$vendor,$buttons) {
		$my = JFactory::getUser();
		$database = JFactory::getDBO(); 
		$myid = $my->id;
		foreach ($prices as $id => $price) {
			if (!is_numeric($id) or !is_numeric($price)) {
				echo "<h1>ERROR: You have typed non-numeric characters in some of the fields, please go back and correct that!</h1>";
				return false;
			}
			$database->setQuery("update #__docmanpaypal set `price` = '$price', user_id = $myid where `id` = '$id';");
			if ($database->query() == false) {
				return false;
			}
		}
		foreach ($downloadslimit as $id => $downloadlimit) {
			if (!is_numeric($id) or !is_numeric($downloadlimit)) {
				echo "<h1>ERROR: You have typed non-numeric characters in some of the fields, please go back and correct that!</h1>";
				return false;
			}
			$database->setQuery("update #__docmanpaypal set `downloadslimit` = '$downloadlimit', user_id = $myid where `id` = '$id';");
			if ($database->query() == false) {
				return false;
			}
		}
		foreach ($saleslimit as $id => $salelimit) {
			if (!is_numeric($id) or !is_numeric($salelimit)) {
				echo "<h1>ERROR: You have typed non-numeric characters in some of the fields, please go back and correct that!</h1>";
				return false;
			}
			$database->setQuery("update #__docmanpaypal set `saleslimit` = '$salelimit', user_id = $myid where `id` = '$id';");
			if ($database->query() == false) {
				return false;
			}
		}
		foreach ($offlineGood as $id => $offlineGood) {
			if (!is_numeric($id) or !is_numeric($offlineGood)) {
				echo "<h1>ERROR: You have typed non-numeric characters in some of the fields, please go back and correct that!</h1>";
				return false;
			}
			$database->setQuery("update #__docmanpaypal set `offlineGood` = '$offlineGood', user_id = $myid where `id` = '$id';");
			if ($database->query() == false) {
				return false;
			}
		}
		foreach ($vendor as $id => $vendor) {
			if (!is_numeric($id) or !is_numeric($vendor)) {
				echo "<h1>ERROR: You have typed non-numeric characters in some of the fields, please go back and correct that!</h1>";
				return false;
			}
			$database->setQuery("update #__docmanpaypal set `vendor` = '$vendor', user_id = $myid where `id` = '$id';");
			if ($database->query() == false) {
				return false;
			}
		}
		foreach ($buttons as $id => $buttons) {
			$database->setQuery("update #__docmanpaypal set `buttons` = '$buttons', user_id = $myid where `id` = '$id';");
			if ($database->query() == false) {
				return false;
			}
		}
	}
	/**
	 * Returns configuration as array
	 *
	 */
	function getConfig($other_id = null) {
		$my = JFactory::getUser();
		$mainframe = JFactory::getApplication();
		$mosConfig_dbprefix = $mainframe->getCfg('dbprefix');
		$database = JFactory::getDBO(); 
		$myid = !empty($other_id) ? $other_id : $my->id;
		
//		$result = mysql_query("select * from $mosConfig_dbprefix" . "docmanpaypalconfig");
//		while ($row = @mysql_fetch_assoc($result)) {
//			$cfg[$row['name']] = $row['value'];
//		}
		$database->setQuery("select * from #__docmanpaypalconfig");
		$result = $database->loadAssocList();
		foreach ($result as $row) {
			$cfg[$row['name']] = $row['value'];
		}
		return $cfg;
	}
	function saveConfig() {
		$my = JFactory::getUser();
		$database = JFactory::getDBO(); 
		$cfgvars = array('allow_resellers',
'use_shopping_cart',
'merchant_header',
'allow_coupons',
'moneybookers_processing_page',
'paypal_processing_page',
'email_link',
'free_download_after_seconds',
'moneybookers_notifyemail',
'moneybookers_email',
'moneybookers_currency',
'merchants',
'paypalemail',
'sandbox',
'currency',
'notifyemail',
'cancelurl',
'license',
'live_site',
'free_for_usertypes',
'thankyoupagecode',
'saleslimitpage',
'downloadslimitpage',
'reseller_usertypes',
'moreThanOnce',
'googleCheckout_email',
'googleCheckout_notifyemail',
'googleCheckout_currency',
'googleCheckout_processing_page',
'googleCheckout_MerchantID',
'googleCheckout_description',
'useVat',
'vatPercent',
'ordercanceledpage',
'emailDelivery',
'emailDeliverySubject',
'emailDeliveryBody',
'emailDeliveryMaxSizeInMB',
'emailDeliveryDownloadLink',
'requireRegistration',
'emailDeliveryToAdmin',
'priceFormat',
'buttonStaysBuyNow',
'free_for_docman_groups',
'paypalCountry',
'encryptPDF',
'pdfOrientation',
'netcash_username',
'netcash_password',
'netcash_pin',
'netcash_terminal',
'netcash_notifyemail',
'netcash_processing_page',
'authorizenet_login_id',
'authorizenet_transaction_key',
'authorizenet_md5_setting',
'authorizenet_processing_page',
'authorizenet_notifyemail',
'authorizenet_test_mode',
'useCart',
'pdfPreviewPages',
'Call2Pay',	
'MobilePay',
'eBank2Pay',
'micropaymentDeHeader',
'thankyoupagecodeCart'
		);
		foreach ($cfgvars as $v) {
			if (isset($_REQUEST[$v])) {
				//$vval = JRequest::getVar($v,'',$_REQUEST,null,4);//mosGetParam($_REQUEST,'content','',_MOS_ALLOWRAW)
				$vval = JRequest::getVar($v,'',null,null,4);
				if (is_array($vval)) {
					$vval = implode(',',$vval);
				}
				$vval = $database->Quote($vval);
				$database->setQuery("select count(*) from #__docmanpaypalconfig where name = '$v'");
				$cnt = $database->loadResult();
				if ($cnt < 1) {
					$sql = "INSERT INTO #__docmanpaypalconfig VALUES ('$v','" . $my->id . "',$vval);";
					$database->setQuery($sql);
					$database->query();
				} else {
					$sql = "UPDATE #__docmanpaypalconfig SET `value` = $vval where name = '$v'";
					//echo "$v = $vval<br />";
					$database->setQuery($sql);
					$database->query();
				}
			}
		}
	}
	/**
	 * This function will hack DOCman's download.php file so we can make the payments before download
	 *
	 */
	function hackDOCman() {
		//TODO: Searches? Take them from original package.
 		jimport( 'joomla.filesystem.file' );
 		$JFile = new JFile();
 		$mainframe = JFactory::getApplication();
 		
 		$files[] = array('file' => '/components/com_docman/views/document/tmpl/document.html.php', 'search' => '<? if (!object(\'user\')->isAuthentic() || $document->canPerform(\'download\')):', 'replace' => '
	<?php
	include(JPATH_SITE . \'/components/com_docmanpaypal/buy_now_button_large.php\');	
	?>
                <? if ((!object(\'user\')->isAuthentic() || $document->canPerform(\'download\')) && $show_download == true):');
 		$files[] = array('file' => '/components/com_docman/views/document/tmpl/manage.html.php', 'search' => '<div class="btn-toolbar">
    <? if ($show_download): ?>', 'replace' => '<div class="btn-toolbar">
	<?php
	include(JPATH_SITE . \'/components/com_docmanpaypal/buy_now_button.php\');	
	?>
	    <? if ($show_download): ?>');

 		/*$files[] = array('file' => '/components/com_docman/views/download/html.php', 'search' => '        if (!$document->id) {
            throw new KViewException(\'Document not found\');
        }

        if (!KRequest::get(\'get.force_download\', \'int\')', 'replace' => '       if (!$document->id) {
            throw new KViewException(\'Document not found\');
        }
        include(JPATH_SITE . \'/components/com_docmanpaypal/hack.docman.php\');
        
        if (!KRequest::get(\'get.force_download\', \'int\')');*/
 		foreach ($files as $arr) {
	 		$file = JFile::read(JPATH_SITE . $arr['file']);
	 		$file2 = $file;
	 		$file = str_replace("\r", "", $file);
	 		$file = str_replace($arr['search'], $arr['replace'], $file);
	 		if ($file != $file2) {
		 		JFile::write(JPATH_SITE . $arr['file'],$file);
		 		//$mainframe->enqueueMessage('File updated - ' .$arr['file'] . '<br />' . $arr['search'] . '<br />' . $arr['replace']);
	 		}
 		}
	}
	/**
	 * get the price of the download based on id
	 *
	 * @param integer $id
	 * @return float
	 */
	function getPrice($id) {
		$database = JFactory::getDBO(); 
		$database->setQuery("select sum(price) from #__docmanpaypal where id IN ($id);");
		return $database->loadResult();
	}
	/**
	 * get the name of the download based on id
	 *
	 * @param integer $id
	 * @return float
	 */
	function getName($id) {
		$database = JFactory::getDBO(); 
		$database->setQuery("select dmname from #__docman_documents where docman_document_id = '$id';");
		return $database->loadResult();
	}
	function isValidSessionKey($session,$moreThanOnce = 0) {
		$database = JFactory::getDBO(); 
		$database->setQuery("select count(*) from #__docmanpaypalsessions where session = '$session';");
		$cnt = $database->loadResult();
		if ($cnt > 0) {
			if ($moreThanOnce < 1) {
				$database->setQuery("delete from #__docmanpaypalsessions where session = '$session';");
				$database->query();
			}
			return true;
		} else {
			return false;
		}
	}
	function bbh_enc($str) {
		$out = base64_encode($str);
		$out = $this->rotN($out);
		$out = base64_encode($out);
		$out = $this->rotN($out);
		$out = base64_encode($out);
		$out = $this->rotN($out);
		$out = base64_encode($out);
		$out = $this->rotN($out);
		return $out;
	}
	function bbh_dec($out) {
		$out = $this->rotN($out,-11);
		$out = base64_decode($out);
		$out = $this->rotN($out,-11);
		$out = base64_decode($out);
		$out = $this->rotN($out,-11);
		$out = base64_decode($out);
		$out = $this->rotN($out,-11);
		$out = base64_decode($out);
		return $out;	
	}
	function rotN($s, $n = 11){
		$s2 = "";
		for($i = 0; $i < strlen($s); $i++){
			$char2 = $char = ord($s{$i});
			$cap = $char & 32;
			$char &= ~ $cap;
			$char = $char > 64 && $char < 123 ? (($char - 65 + $n) % 26 + 65) : $char;
			$char |= $cap;
			if($char < 65 && $char2 > 64 || ($char > 90 && $char < 97 && ($char2 < 91 || $char2 > 96))) $char += 26;
			else if($char > 122 && $char2 < 123) $char -= 52;
			if(strtoupper(chr($char2)) === chr($char2)) $char = strtoupper(chr($char)); else $char = strtolower(chr($char));
			$s2 .= $char;
		}
		return $s2;
	}
	function getUserTypes() {
		$database = JFactory::getDBO();
		$database->setQuery("SELECT *  FROM `#__usergroups`");
		return $database->loadAssocList();
	}
	function getDOCmanGroups() {
// 		$database = JFactory::getDBO();
// 		$database->setQuery("SELECT *  FROM `#__docman_groups`");
// 		return $database->loadAssocList();
		return false;
	}
	function getMerchants() {
		return array('PayPal','Micropayment.de'/*,'Moneybookers','Google Checkout','Netcash.co.za','Authorize.Net'*/);
	}
	function getVersion() {
		return "3.1";
	}
	function countDownload($order_id,$doc_id,$src) {
		$my = JFactory::getUser();
		$database = JFactory::getDBO();
		$database->setQuery("update #__docmanpaypaldownloads set downloads = downloads + 1 where order_id = '$order_id' and `id` = $doc_id limit 1");
		return $database->query();
	}
/*    function mosLoadAdminModules( $position='left', $style=0 )
    {
        jimport('joomla.html.pane');
        $modules    = JModuleHelper::getModules($position);
        $pane       = JPane::getInstance('sliders');
        echo $pane->startPane("content-pane");

        foreach ($modules as $module)
        {
            $title = $module->title ;
            echo $pane->startPanel( $title, "$position-panel" );
            echo JModuleHelper::renderModule($module);
            echo $pane->endPanel();
        }

        echo $pane->endPane();
    }*/
    function doEmailDelivery($email,$doc_id,$order_id,$key) {
    		 @ini_set('memory_limit','1024M');
    		 $database = JFactory::getDBO();
			 
			 @$x = explode(",",$doc_id);
			 if (is_array($x) == false) {
			 	$x[] = $doc_id;
			 	$mode = 'single';
			 } else {
			 	$mode = 'cart';
			 }
		     
			 $mail = JFactory::getMailer();
		     $mail->IsHTML(true);
		     $app = JFactory::getApplication();
		     //$conf = JFactory::getConfig();
		     $emailBody = $this->cfg['emailDeliveryBody'];
		     $maxEmailDeliverySize = $this->cfg['emailDeliveryMaxSizeInMB'] * 1024 * 1024;
		     
		     $downloadLink = JURI::base() . "index.php?option=com_docmanpaypal&task=doc_download&gid=" . $doc_id . "&order_id=$order_id&key=$key&mode=$mode&Itemid=" . (int)JRequest::getVar('Itemid');
			 foreach ($x as $doc_id) {
		         $database->setQuery("select storage_path from #__docman_documents where docman_document_id = $doc_id limit 1");
		         $dmfilename = $database->loadResult();
		         $attachment = $this->docman_path .DS . $dmfilename;
		         //3.2.2 Encrypted PDF files
		         if ($this->cfg['encryptPDF'] == 1 && pathinfo($attachment, PATHINFO_EXTENSION) == 'pdf') {
					//password for the pdf file (I suggest using the email adress of the purchaser)
					$password = $email;
					
					//name of the original file (unprotected)
					$origFile = $attachment;
					
					//name of the destination file (password protected and printing rights removed)
					$destFile = dirname($attachment) . DS . basename($attachment,'.pdf') . "_protected.pdf";
					@unlink($destFile);
		         	require_once('fpdi' . DS . 'FPDI_Protection.php');
		         	$pdf = new FPDI_Protection();
		         	if ($this->cfg['pdfOrientation'] == 'portrait') {
			         	$pdf->FPDF('P', 'in', array('8','11'));
		         	} else {
			         	$pdf->FPDF('P', 'in', array('11','8'));
		         	}
		         	$pagecount = $pdf->setSourceFile($attachment);
			        for ($loop = 1; $loop <= $pagecount; $loop++) {
						$tplidx = $pdf->importPage($loop);
						$pdf->addPage();
						$pdf->useTemplate($tplidx);
					}
					$pdf->SetProtection(array(), $password);
					$pdf->Output($destFile, 'F');
					//pdfEncrypt($origFile, $password, $destFile );
					$attachment = $destFile;
					//mail('deian@motov.net','DEbug',"$attachment, $origFile, $destFile");
		         }
		         
		         if ($maxEmailDeliverySize > filesize($attachment)) {
			         $mail->addAttachment($attachment);
		         } else {
		         	$theFileIsLarge = true;
		         }
			 }
	         
	         if ($this->cfg['emailDeliveryDownloadLink'] == 0) {
	         	$emailBody = str_replace('%downloadlink%','',$emailBody);
	         }
	         if ($this->cfg['emailDeliveryDownloadLink'] == 1) {
	         	$emailBody = str_replace('%downloadlink%',$downloadLink,$emailBody);
	         }
	         if ($this->cfg['emailDeliveryDownloadLink'] == 2) {
	         	if ($theFileIsLarge) {
	         		$emailBody = str_replace('%downloadlink%',$downloadLink,$emailBody);
	         	} else {
	         		$emailBody = str_replace('%downloadlink%','',$emailBody);
	         	}
	         } 
	         if ($this->cfg['emailDeliveryToAdmin'] == 1) {
	         	$database->setQuery("SELECT #__users.email, #__usergroups.title from #__users left join #__user_usergroup_map on #__users.id = #__user_usergroup_map.user_id left join #__usergroups on #__usergroups.id = #__user_usergroup_map.group_id where #__usergroups.title = 'Super Users';");
	         	$result = $database->loadAssocList();
	         	foreach ($result as $row) {
	         		$mail->addRecipient($row['email']);
	         	}
	         }
	         $mail->setBody($emailBody);
	         $mail->setSubject($this->cfg['emailDeliverySubject']);
	         if ($app->getCfg('mailer') == 'smtp') {
	 			$mail->useSMTP($app->getCfg('smtpauth'),$app->getCfg('smtphost'),$app->getCfg('smtpuser'),$app->getCfg('smtppass'),$app->getCfg('smtpsecure'),$app->getCfg('smtpport'));
	 		 }
	 		 $mail->setSender(array($app->getCfg('mailfrom'), $app->getCfg('fromname')));
	         $mail->addRecipient($email);
	         $mail->Send();
    }
    function vatCalc($price) {
    	return round($price * $this->cfg['vatPercent'] / 100,2);
    }
    function makeBlankOrder($dm_id,$mode) {
    	$database = JFactory::getDBO();
    	$my = JFactory::getUser();
		$user_id = $my->id;
		
		if ($mode == 'single') {
			$database->setQuery("select created_by as dmsubmitedby, title as dmname from #__docman_documents where docman_document_id = '$dm_id' limit 1");
			$result = $database->loadAssocList();
			extract($result[0]);		
		} else {
			$session = JFactory::getSession(); 
			$cart = $session->get('cart');
			$dmname = 'Cart Order';
			$dmsubmitedby = 0;
			$dm_id = implode(',',$cart['id']);
		}

		$key = md5(rand(1,100000) . time() . date("Y-m-d"));
    	$sql = "insert into #__docmanpaypalorders (

`order_id` ,
`user_id` ,
`buyer_id` ,
`file_id`,
`item_name` ,
`first_name` ,
`last_name` ,
`organization` ,
`address` ,
`city` ,
`state` ,
`zip` ,
`country` ,
`phone` ,
`email` ,
`comments`,
`price`,
`datetime`,
`mc_currency`,
`completed`,
`key`,
`transaction`,
`merchant`
)
VALUES (
NULL , '$dmsubmitedby', '$user_id','$dm_id', " . $database->Quote($dmname) . ", 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '', 'N/A', '" . date("Y-m-d H:i:s") . "', 'N/A', '0','$key','','');";
    	$database->setQuery($sql);
    	//echo $database->getQuery();
    	$database->query(); 
    	return array('id' => $database->insertid(),'key' => $key);
    }
    function completeOrder($order_id,$mode = 'single') {
    	$database = JFactory::getDBO();
    	$my = JFactory::getUser();
    	$sql = "UPDATE `#__docmanpaypalorders` SET  `completed` =  '1' WHERE `order_id` = $order_id ;";
    	$database->setQuery($sql);
    	$return = $database->query();
    	//mail('deian@motov.net','DEBUG QUERY',$sql);
    	$order = $this->getOrder($order_id);
   		@$ids = explode(",",$order->file_id);
   		if (is_array($ids) == false) {
   			$ids[] = $orders->file_id;
   		}
   		$database->setQuery("delete from #__docmanpaypaldownloads where order_id = {$order->order_id}");
   		$database->query();
   		foreach ($ids as $id) {
   			$database->setQuery("insert into #__docmanpaypaldownloads values ({$order->order_id},$id,0);");
   			//mail('deian@motov.net','debug complete',$database->getQuery());
   			$return = $database->query();
   		}
    	return $return;
    }
    function canDownload($doc_id,$key,$countIt = true) {
    	//die('canDownload executed...');
    	$return = true;
    	$database = JFactory::getDBO();
    	$mainframe = JFactory::getApplication();
    	$my = JFactory::getUser();
    	if ($key == '' && $my->id > 0) {
    		$database->setQuery("select `key` from #__docmanpaypalorders where file_id = $doc_id OR file_id like '$doc_id,%' or file_id like '%,$doc_id,%' or file_id like '%,$doc_id' and buyer_id = {$my->id} order by order_id desc limit 1");
    		$key = $database->loadResult();
    	}
    	if ($key == false) {
    		return false;
    	}
    	$sql = "SELECT dmpo. * , dmp. * 
FROM #__docmanpaypalorders dmpo, #__docmanpaypal dmp
WHERE dmpo.buyer_id =  '{$my->id}'
AND dmpo.`key` = " . $database->Quote($key) . "
AND dmp.id = $doc_id
ORDER BY dmpo.order_id DESC 
LIMIT 1;";
    	$database->setQuery($sql);
    	//mail('deian@motov.net','DEBUG QUERY',$database->getQuery());
		//die($database->getQuery());
    	$result = $database->loadAssocList();
		if ($result == false) {
			return false;
		}

		$r = $result[0];
		
		$database->setQuery("select downloads from #__docmanpaypaldownloads where order_id = {$r[order_id]} and `id` = $doc_id");
		$r['downloads'] = $database->loadResult();
		
		if ($r['downloads'] >= $r['downloadslimit'] and $r['downloadslimit'] > 0) {
			//$mainframe->redirect('index.php?option=com_docmanpaypal&task=downloadslimit&id=' . $doc_id);
			$return = false; //TODO: Redirect to the downloads limit hit page.
		} else if ($r['downloads'] > 0 && $this->cfg['moreThanOnce'] < 1) {
			$return = false;
		} else {
			if ($countIt) {
				$this->countDownload($r['order_id'],$doc_id,$r['src']);
			}
		}
		if ($r['completed'] < 1) {
			$return = false;
		}
		//mail('deian@motov.net','DEBUG ARRAY',print_r($result,true) . "\n\n\n\n$r[downloads] $r[downloadslimit]");
		return $return;
    }
    function updateOrder($order_id,$array) {
    	$database = JFactory::getDBO();
    	$sql = "update #__docmanpaypalorders set";
    	$database = JFactory::getDBO();


    	foreach ($array as $k => $v) {
    		$sqlArray[] = "`$k` = " . $database->Quote($v);
    	}
    	$sql .= implode(', ',$sqlArray);
    	$sql .= " where order_id = $order_id limit 1";
    	$database->setQuery($sql);
    	//mail('deian@motov.net','DEBUG UPDATE ORDER SQL' . __LINE__,$database->getQuery() . print_r($array,true));
    	return $database->query();
    }
    function formatTransactionLink($transaction) {
    	$database = JFactory::getDBO();
    	$database->setQuery("select merchant from #__docmanpaypalorders where transaction = '$transaction' limit 1");
    	$merchant = $database->loadResult();
    	if ($merchant == 'PayPal') {
    		return '<a href="https://www.paypal.com/en/cgi-bin/webscr?cmd=_view-a-trans&id=' . $transaction . '" target="_blank">' . $transaction . '</a>';
    	}
    	if ($merchant == 'Moneybookers') {
    		return $transaction;
    	}
    	if ($merchant == 'Netcash') {
    		return $transaction;
    	}
    }
    function isInDOCmanFreeDownloadGroup($my_id = null) {
    	$database = JFactory::getDBO();
    	if ($my_id == null) {
    		$my = JFactory::getUser();
    		$my_id = $my->id;
    	}
    	$database->setQuery("select value from #__docmanpaypalconfig where name = 'free_for_docman_groups'");
    	$free_for_docman_groups = $database->loadResult();
    	if ($free_for_docman_groups != '') {
    		$database->setQuery("SELECT *
FROM #__docman_groups
WHERE groups_id
IN ($free_for_docman_groups)");
    		$result = $database->loadAssocList();
    		foreach ($result as $row) {
    			$x = explode(',',$row['groups_members']);
    			foreach ($x as $id) {
    				$idsArray[] = $id;
    			}
    		}
    		if (@in_array($my_id,$idsArray) && $my_id > 0) { //3.1.9 R9 
    			//mail('deian@motov.net','IDS ARRAY',print_r($idsArray,true));
    			return true;
    		} else {
    			return false;
    		}
    	} else {
    		return false;
    	}
    	//$database->setQuery("select merchant from #__docmanpaypalorders where transaction = '$transaction' limit 1");
    	
    }
    function getCategories() {
    	$database = JFactory::getDBO();
    	$database->setQuery("select * from #__categories where section = 'com_docman'");
    	return $database->loadObjectList();
    }
	function random_color(){
	    mt_srand((double)microtime()*1000000);
	    $c = '';
	    while(strlen($c)<6){
	        $c .= sprintf("%02X", mt_rand(0, 255));
	    }
	    return $c;
	}
	function cartAdd($id) {
		$session = JFactory::getSession();
		$db = JFactory::getDBO();
		$item = $this->getItem($id);
		//return print_r($item,true);
		$cart = $session->get('cart');
		if (@in_array($id,$cart['id']) == false) {
			@$cart['items']++;
			@$cart['total'] += $item->price;
			@$cart['id'][] = $item->id;
		}
		return $session->set('cart',$cart);
	}
	function cartShow() {
		global $currency_symbols;
		//$lang = JFactory::getLanguage();
		$session = JFactory::getSession();
		$cart = $session->get('cart');
		if ($cart['total'] == 0) {
			return _DMP_CART_IS_EMPTY;
		} else {
//			return "<b>$cart[items]</b> for " . $currency_symbols[$this->cfg['currency']] . ' ' . number_format($cart['total'],2);
			return "{$cart[items]} " . _DMP_ITEMS . " ({$currency_symbols[$this->cfg['currency']]}" . number_format($cart['total'],2) . " {$this->cfg['currency']})";
		}
		//return print_r($session->get('cart'),true);
	}
	function getItem($id) {
		$db = JFactory::getDBO();
		$db->setQuery("SELECT *
FROM `#__docmanpaypal` d
LEFT JOIN #__docmanpaypalvendors v ON d.vendor = v.vendor_id
LEFT JOIN #__docman_documents dm ON d.id = dm.docman_document_id where dm.docman_document_id = $id
LIMIT 1");

		$obj = $db->loadObject();
		$obj->dmname = $obj->title;
		$obj->id = $obj->docman_document_id;
		if ($obj->mypercent > 0) {
			$obj->paypalemail = docmanpaypal::cfg('paypalemail');
		}
		return $obj;
	}
	function cfg($name) {
		$db = JFactory::getDBO();
		$db->setQuery("select value from #__docmanpaypalconfig where name = '$name' limit 1");
		return $db->loadResult();
	}
	function cartEmpty() {
		$session = JFactory::getSession();
		$session->set('cart',null);
	}
	function getOrder($order_id) {
		$db = JFactory::getDBO();
		$db->setQuery("select * from #__docmanpaypalorders where order_id = '$order_id' limit 1");
		return $db->loadObject();
	}
	function getVendors() {
		$db = JFactory::getDBO();
		$db->setQuery("select * from #__docmanpaypalvendors where 1");
		return $db->loadObjectList();
	}
	function objectsToSelect($objects,$name,$title,$id,$currentValue) {
		$options[] = JHTML::_('select.option', "0", _DMP_NONE );
		 foreach ($objects as $row) {
		 	$options[] = JHTML::_('select.option', $row->{$id}, $row->{$title} );
		 }
		 echo JHTML::_('select.genericlist', $options, $name, null, 'value', 'text', $currentValue);
	}
	function vendor_exists($vendor) {
		$db = JFactory::getDBO();
		$db->setQuery("select count(*) from #__docmanpaypalvendors where paypalemail = '$vendor'");
		return $db->loadResult();
	}
	function sendExpiredEmail() {
		$mail = JFactory::getMailer();
		$database = JFactory::getDBO();
		$mail->IsHTML(true);
		//$mail->to = array();
		//$conf = JFactory::getConfig();
		$app = JFactory::getApplication();
		
		$database->setQuery("select email from #__users where usertype = 'Super Administrator'");
		$result = $database->loadAssocList();
		foreach ($result as $row) {
			$mail->addRecipient($row['email']);
		}
		
		$mail->setSubject("Order could not start, PayPal IPN for DOCman expired");
		if ($app->getCfg('mailer') == 'smtp') {
	 					$mail->useSMTP($app->getCfg('smtpauth'),$app->getCfg('smtphost'),$app->getCfg('smtpuser'),$app->getCfg('smtppass'),$app->getCfg('smtpsecure'),$app->getCfg('smtpport'));
	 	}
		
		$mail->setBody('<p>Dear User,</p>
<p>Thank you for testing PayPal IPN for DOCman for Joomla 1.5.</p>
<p>The trial version period is now over and your site is unable to process orders (your files remain protected)!</p>
<p>To obtain a full version please follow <a href="http://motov.net/downloads/paypal-paid-downloads/paypal-ipn-for-docman-3-2-5-stable/download/doc_download" target="_blank">this link</a>.</p>
<p>Thank you!</p>');
	 	$mail->setSender(array($app->getCfg('mailfrom'), $app->getCfg('fromname')));
		$mail->Send();
	}
	function __construct() {
		$this->constructRun = true;
		$database = JFactory::getDBO(); 
		$mainframe = JFactory::getApplication();
		global $mosConfig_dbprefix;
		$sql = "select value from #__" . "docmanpaypalconfig where name = 'live_site' limit 1";
		$live_site = $this->singleResult($sql);
		//echo "MY SITE IS: $live_site";
		$this->mydomain = parse_url_compat($live_site,PHP_URL_HOST);
		$this->mydomain = str_replace('www.','',strtolower($this->mydomain));
		$this->hasLicense = true;
		$this->docman_path = JPATH_SITE . '/' . docmanpaypal::singleResult('select path from #__files_containers where slug = \'docman-files\' limit 1');
		$this->cfg = $this->getConfig();
		$this->isTrial = 0;
/*		$installDate = base64_decode(file_get_contents(JPATH_SITE . DS . 'libraries' . DS . 'dtd.xml'));
		$expDate = strtotime("+2 weeks",strtotime($installDate));
		if (time() > $expDate && $this->isTrial) {
			$this->expired = 1;
		} else {
			$this->expired = 0;
		}*/
	}
}
$tabscode = '<link rel="stylesheet" type="text/css" href="/components/com_docmanpaypal/tabcontent.css" />
<script type="text/javascript" src="/components/com_docmanpaypal/tabcontent.js"></script>';

$paypalCountries['0'] = '-- Optional --';
$paypalCountries['AF'] = 'AFGHANISTAN';
$paypalCountries['AX'] = 'ÅLAND ISLANDS';
$paypalCountries['AL'] = 'ALBANIA';
$paypalCountries['DZ'] = 'ALGERIA';
$paypalCountries['AS'] = 'AMERICAN SAMOA';
$paypalCountries['AD'] = 'ANDORRA';
$paypalCountries['AO'] = 'ANGOLA';
$paypalCountries['AI'] = 'ANGUILLA';
$paypalCountries['AQ'] = 'ANTARCTICA';
$paypalCountries['AG'] = 'ANTIGUA AND BARBUDA';
$paypalCountries['AR'] = 'ARGENTINA';
$paypalCountries['AM'] = 'ARMENIA';
$paypalCountries['AW'] = 'ARUBA';
$paypalCountries['AU'] = 'AUSTRALIA';
$paypalCountries['AT'] = 'AUSTRIA';
$paypalCountries['AZ'] = 'AZERBAIJAN';
$paypalCountries['BS'] = 'BAHAMAS';
$paypalCountries['BH'] = 'BAHRAIN';
$paypalCountries['BD'] = 'BANGLADESH';
$paypalCountries['BB'] = 'BARBADOS';
$paypalCountries['BY'] = 'BELARUS';
$paypalCountries['BE'] = 'BELGIUM';
$paypalCountries['BZ'] = 'BELIZE';
$paypalCountries['BJ'] = 'BENIN';
$paypalCountries['BM'] = 'BERMUDA';
$paypalCountries['BT'] = 'BHUTAN';
$paypalCountries['BO'] = 'BOLIVIA';
$paypalCountries['BA'] = 'BOSNIA AND HERZEGOVINA';
$paypalCountries['BW'] = 'BOTSWANA';
$paypalCountries['BV'] = 'BOUVET ISLAND';
$paypalCountries['BR'] = 'BRAZIL';
$paypalCountries['IO'] = 'BRITISH INDIAN OCEAN TERRITORY';
$paypalCountries['BN'] = 'BRUNEI DARUSSALAM';
$paypalCountries['BG'] = 'BULGARIA';
$paypalCountries['BF'] = 'BURKINA FASO';
$paypalCountries['BI'] = 'BURUNDI';
$paypalCountries['KH'] = 'CAMBODIA';
$paypalCountries['CM'] = 'CAMEROON';
$paypalCountries['CA'] = 'CANADA';
$paypalCountries['CV'] = 'CAPE VERDE';
$paypalCountries['KY'] = 'CAYMAN ISLANDS';
$paypalCountries['CF'] = 'CENTRAL AFRICAN REPUBLIC';
$paypalCountries['TD'] = 'CHAD';
$paypalCountries['CL'] = 'CHILE';
$paypalCountries['CN'] = 'CHINA';
$paypalCountries['CX'] = 'CHRISTMAS ISLAND';
$paypalCountries['CC'] = 'COCOS (KEELING) ISLANDS';
$paypalCountries['CO'] = 'COLOMBIA';
$paypalCountries['KM'] = 'COMOROS';
$paypalCountries['CG'] = 'CONGO';
$paypalCountries['CD'] = 'CONGO, THE DEMOCRATIC REPUBLIC OF';
$paypalCountries['CK'] = 'COOK ISLANDS';
$paypalCountries['CR'] = 'COSTA RICA';
$paypalCountries['CI'] = 'COTE D\'IVOIRE';
$paypalCountries['HR'] = 'CROATIA';
$paypalCountries['CU'] = 'CUBA';
$paypalCountries['CY'] = 'CYPRUS';
$paypalCountries['CZ'] = 'CZECH REPUBLIC';
$paypalCountries['DK'] = 'DENMARK';
$paypalCountries['DJ'] = 'DJIBOUTI';
$paypalCountries['DM'] = 'DOMINICA';
$paypalCountries['DO'] = 'DOMINICAN REPUBLIC';
$paypalCountries['EC'] = 'ECUADOR';
$paypalCountries['EG'] = 'EGYPT';
$paypalCountries['SV'] = 'EL SALVADOR';
$paypalCountries['GQ'] = 'EQUATORIAL GUINEA';
$paypalCountries['ER'] = 'ERITREA';
$paypalCountries['EE'] = 'ESTONIA';
$paypalCountries['ET'] = 'ETHIOPIA';
$paypalCountries['FK'] = 'FALKLAND ISLANDS (MALVINAS)';
$paypalCountries['FO'] = 'FAROE ISLANDS';
$paypalCountries['FJ'] = 'FIJI';
$paypalCountries['FI'] = 'FINLAND';
$paypalCountries['FR'] = 'FRANCE';
$paypalCountries['GF'] = 'FRENCH GUIANA';
$paypalCountries['PF'] = 'FRENCH POLYNESIA';
$paypalCountries['TF'] = 'FRENCH SOUTHERN TERRITORIES';
$paypalCountries['GA'] = 'GABON';
$paypalCountries['GM'] = 'GAMBIA';
$paypalCountries['GE'] = 'GEORGIA';
$paypalCountries['DE'] = 'GERMANY';
$paypalCountries['GH'] = 'GHANA';
$paypalCountries['GI'] = 'GIBRALTAR';
$paypalCountries['GR'] = 'GREECE';
$paypalCountries['GL'] = 'GREENLAND';
$paypalCountries['GD'] = 'GRENADA';
$paypalCountries['GP'] = 'GUADELOUPE';
$paypalCountries['GU'] = 'GUAM';
$paypalCountries['GT'] = 'GUATEMALA';
$paypalCountries['GG'] = 'GUERNSEY';
$paypalCountries['GN'] = 'GUINEA';
$paypalCountries['GW'] = 'GUINEA-BISSAU';
$paypalCountries['GY'] = 'GUYANA';
$paypalCountries['HT'] = 'HAITI';
$paypalCountries['HM'] = 'HEARD ISLAND AND MCDONALD ISLANDS';
$paypalCountries['VA'] = 'HOLY SEE (VATICAN CITY STATE)';
$paypalCountries['HN'] = 'HONDURAS';
$paypalCountries['HK'] = 'HONG KONG';
$paypalCountries['HU'] = 'HUNGARY';
$paypalCountries['IS'] = 'ICELAND';
$paypalCountries['IN'] = 'INDIA';
$paypalCountries['ID'] = 'INDONESIA';
$paypalCountries['IR'] = 'IRAN, ISLAMIC REPUBLIC OF';
$paypalCountries['IQ'] = 'IRAQ';
$paypalCountries['IE'] = 'IRELAND';
$paypalCountries['IM'] = 'ISLE OF MAN';
$paypalCountries['IL'] = 'ISRAEL';
$paypalCountries['IT'] = 'ITALY';
$paypalCountries['JM'] = 'JAMAICA';
$paypalCountries['JP'] = 'JAPAN';
$paypalCountries['JE'] = 'JERSEY';
$paypalCountries['JO'] = 'JORDAN';
$paypalCountries['KZ'] = 'KAZAKHSTAN';
$paypalCountries['KE'] = 'KENYA';
$paypalCountries['KI'] = 'KIRIBATI';
$paypalCountries['KP'] = 'KOREA, DEMOCRATIC PEOPLE\'S REPUBLIC OF';
$paypalCountries['KR'] = 'KOREA, REPUBLIC OF';
$paypalCountries['KW'] = 'KUWAIT';
$paypalCountries['KG'] = 'KYRGYZSTAN';
$paypalCountries['LA'] = 'LAO PEOPLE\'S DEMOCRATIC REPUBLIC';
$paypalCountries['LV'] = 'LATVIA';
$paypalCountries['LB'] = 'LEBANON';
$paypalCountries['LS'] = 'LESOTHO';
$paypalCountries['LR'] = 'LIBERIA';
$paypalCountries['LY'] = 'LIBYAN ARAB JAMAHIRIYA';
$paypalCountries['LI'] = 'LIECHTENSTEIN';
$paypalCountries['LT'] = 'LITHUANIA';
$paypalCountries['LU'] = 'LUXEMBOURG';
$paypalCountries['MO'] = 'MACAO';
$paypalCountries['MK'] = 'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF';
$paypalCountries['MG'] = 'MADAGASCAR';
$paypalCountries['MW'] = 'MALAWI';
$paypalCountries['MY'] = 'MALAYSIA';
$paypalCountries['MV'] = 'MALDIVES';
$paypalCountries['ML'] = 'MALI';
$paypalCountries['MT'] = 'MALTA';
$paypalCountries['MH'] = 'MARSHALL ISLANDS';
$paypalCountries['MQ'] = 'MARTINIQUE';
$paypalCountries['MR'] = 'MAURITANIA';
$paypalCountries['MU'] = 'MAURITIUS';
$paypalCountries['YT'] = 'MAYOTTE';
$paypalCountries['MX'] = 'MEXICO';
$paypalCountries['FM'] = 'MICRONESIA, FEDERATED STATES OF';
$paypalCountries['MD'] = 'MOLDOVA, REPUBLIC OF';
$paypalCountries['MC'] = 'MONACO';
$paypalCountries['MN'] = 'MONGOLIA';
$paypalCountries['MS'] = 'MONTSERRAT';
$paypalCountries['MA'] = 'MOROCCO';
$paypalCountries['MZ'] = 'MOZAMBIQUE';
$paypalCountries['MM'] = 'MYANMAR';
$paypalCountries['NA'] = 'NAMIBIA';
$paypalCountries['NR'] = 'NAURU';
$paypalCountries['NP'] = 'NEPAL';
$paypalCountries['NL'] = 'NETHERLANDS';
$paypalCountries['AN'] = 'NETHERLANDS ANTILLES';
$paypalCountries['NC'] = 'NEW CALEDONIA';
$paypalCountries['NZ'] = 'NEW ZEALAND';
$paypalCountries['NI'] = 'NICARAGUA';
$paypalCountries['NE'] = 'NIGER';
$paypalCountries['NG'] = 'NIGERIA';
$paypalCountries['NU'] = 'NIUE';
$paypalCountries['NF'] = 'NORFOLK ISLAND';
$paypalCountries['MP'] = 'NORTHERN MARIANA ISLANDS';
$paypalCountries['NO'] = 'NORWAY';
$paypalCountries['OM'] = 'OMAN';
$paypalCountries['PK'] = 'PAKISTAN';
$paypalCountries['PW'] = 'PALAU';
$paypalCountries['PS'] = 'PALESTINIAN TERRITORY, OCCUPIED';
$paypalCountries['PA'] = 'PANAMA';
$paypalCountries['PG'] = 'PAPUA NEW GUINEA';
$paypalCountries['PY'] = 'PARAGUAY';
$paypalCountries['PE'] = 'PERU';
$paypalCountries['PH'] = 'PHILIPPINES';
$paypalCountries['PN'] = 'PITCAIRN';
$paypalCountries['PL'] = 'POLAND';
$paypalCountries['PT'] = 'PORTUGAL';
$paypalCountries['PR'] = 'PUERTO RICO';
$paypalCountries['QA'] = 'QATAR';
$paypalCountries['RE'] = 'REUNION';
$paypalCountries['RO'] = 'ROMANIA';
$paypalCountries['RU'] = 'RUSSIAN FEDERATION';
$paypalCountries['RW'] = 'RWANDA';
$paypalCountries['SH'] = 'SAINT HELENA';
$paypalCountries['KN'] = 'SAINT KITTS AND NEVIS';
$paypalCountries['LC'] = 'SAINT LUCIA';
$paypalCountries['PM'] = 'SAINT PIERRE AND MIQUELON';
$paypalCountries['VC'] = 'SAINT VINCENT AND THE GRENADINES';
$paypalCountries['WS'] = 'SAMOA';
$paypalCountries['SM'] = 'SAN MARINO';
$paypalCountries['ST'] = 'SAO TOME AND PRINCIPE';
$paypalCountries['SA'] = 'SAUDI ARABIA';
$paypalCountries['SN'] = 'SENEGAL';
$paypalCountries['CS'] = 'SERBIA AND MONTENEGRO';
$paypalCountries['SC'] = 'SEYCHELLES';
$paypalCountries['SL'] = 'SIERRA LEONE';
$paypalCountries['SG'] = 'SINGAPORE';
$paypalCountries['SK'] = 'SLOVAKIA';
$paypalCountries['SI'] = 'SLOVENIA';
$paypalCountries['SB'] = 'SOLOMON ISLANDS';
$paypalCountries['SO'] = 'SOMALIA';
$paypalCountries['ZA'] = 'SOUTH AFRICA';
$paypalCountries['GS'] = 'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS';
$paypalCountries['ES'] = 'SPAIN';
$paypalCountries['LK'] = 'SRI LANKA';
$paypalCountries['SD'] = 'SUDAN';
$paypalCountries['SR'] = 'SURINAME';
$paypalCountries['SJ'] = 'SVALBARD AND JAN MAYEN';
$paypalCountries['SZ'] = 'SWAZILAND';
$paypalCountries['SE'] = 'SWEDEN';
$paypalCountries['CH'] = 'SWITZERLAND';
$paypalCountries['SY'] = 'SYRIAN ARAB REPUBLIC';
$paypalCountries['TW'] = 'TAIWAN, PROVINCE OF CHINA';
$paypalCountries['TJ'] = 'TAJIKISTAN';
$paypalCountries['TZ'] = 'TANZANIA, UNITED REPUBLIC OF';
$paypalCountries['TH'] = 'THAILAND';
$paypalCountries['TL'] = 'TIMOR-LESTE';
$paypalCountries['TG'] = 'TOGO';
$paypalCountries['TK'] = 'TOKELAU';
$paypalCountries['TO'] = 'TONGA';
$paypalCountries['TT'] = 'TRINIDAD AND TOBAGO';
$paypalCountries['TN'] = 'TUNISIA';
$paypalCountries['TR'] = 'TURKEY';
$paypalCountries['TM'] = 'TURKMENISTAN';
$paypalCountries['TC'] = 'TURKS AND CAICOS ISLANDS';
$paypalCountries['TV'] = 'TUVALU';
$paypalCountries['UG'] = 'UGANDA';
$paypalCountries['UA'] = 'UKRAINE';
$paypalCountries['AE'] = 'UNITED ARAB EMIRATES';
$paypalCountries['GB'] = 'UNITED KINGDOM';
$paypalCountries['US'] = 'UNITED STATES';
$paypalCountries['UM'] = 'UNITED STATES MINOR OUTLYING ISLANDS';
$paypalCountries['UY'] = 'URUGUAY';
$paypalCountries['UZ'] = 'UZBEKISTAN';
$paypalCountries['VU'] = 'VANUATU';
$paypalCountries['VE'] = 'VENEZUELA';
$paypalCountries['VN'] = 'VIET NAM';
$paypalCountries['VG'] = 'VIRGIN ISLANDS, BRITISH';
$paypalCountries['VI'] = 'VIRGIN ISLANDS, U.S.';
$paypalCountries['WF'] = 'WALLIS AND FUTUNA';
$paypalCountries['EH'] = 'WESTERN SAHARA';
$paypalCountries['YE'] = 'YEMEN';
$paypalCountries['ZM'] = 'ZAMBIA';
$paypalCountries['ZW'] = 'ZIMBABWE';
?>