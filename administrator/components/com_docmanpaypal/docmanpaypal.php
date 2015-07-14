<?php

defined('_JEXEC') or die('Access Denied');
/**
* @author Deian Motov <bbhsoft@gmail.com>
* @name DOCman PayPal Administration
* @copyright Under GPL license
* @example $dmp = new docmanpaypal;
*/

defined('_JEXEC') or die('Access Denied');
$database = JFactory::getDBO();
$option = 'com_docmanpaypal';

$lang = JFactory::getLanguage();
$l = substr($lang->_lang,0,2);

if (!defined('DS')) { define('DS',DIRECTORY_SEPARATOR); }
JSubMenuHelper::addEntry(JText::_('COM_DOCMANPAYPAL_CONFIGURATION'), 'index.php?&option=com_docmanpaypal&task=config',JRequest::getVar('task') == 'config');
JSubMenuHelper::addEntry(JText::_('COM_DOCMANPAYPAL_MODIFY_PRICES'), 'index.php?&option=com_docmanpaypal&task=modifyprice',JRequest::getVar('task') == 'modifyprice');
JSubMenuHelper::addEntry(JText::_('COM_DOCMANPAYPAL_MERCHANT_CONFIGURATION'), 'index.php?&option=com_docmanpaypal&task=merchantconfig',JRequest::getVar('task') == 'merchantconfig');
JSubMenuHelper::addEntry(JText::_('COM_DOCMANPAYPAL_ORDERS'), 'index.php?&option=com_docmanpaypal&task=mySales',JRequest::getVar('task') == 'mySales');
JSubMenuHelper::addEntry(JText::_('COM_DOCMANPAYPAL_GENERATE_SALE'), 'index.php?&option=com_docmanpaypal&task=generateSale',JRequest::getVar('task') == 'generateSale');
JSubMenuHelper::addEntry(JText::_('COM_DOCMANPAYPAL_STATISTICS'), 'index.php?&option=com_docmanpaypal&task=stats',JRequest::getVar('task') == 'stats');
JSubMenuHelper::addEntry(JText::_('COM_DOCMANPAYPAL_VENDORS'), 'index.php?&option=com_docmanpaypal&task=vendors',JRequest::getVar('task') == 'vendors');
JSubMenuHelper::addEntry(JText::_('COM_DOCMANPAYPAL_HELP'), 'index.php?&option=com_docmanpaypal&task=help',JRequest::getVar('task') == 'help');


$langFile = JPATH_ROOT . DS . 'components'. DS . 'com_docmanpaypal' . DS . 'lang'. DS . $l . '.php';

if (file_exists($langFile)) {
	include_once($langFile);
} else {
	include_once JPATH_ROOT . DS . 'components'. DS . 'com_docmanpaypal' . DS .'lang'. DS .'en.php';
}

$document = &JFactory::getDocument();
$css = JURI::base() .'/components/com_docmanpaypal/dmp.css';
$document->addStyleSheet( $css, 'text/css', null, array() );
$mainframe =& JFactory::getApplication();
jimport( 'joomla.filesystem.folder' );

if (JFolder::exists(JPATH_SITE . '/components/com_docman') == false) {
	$mainframe->redirect("index.php",'You must have DOCman 1.4.x or DOCman 1.5.x installed to use this software, go to Joomlatools.eu to obtain your copy.');
	exit();
}


$my = &JFactory::getUser();
//$v15    = version_compare($GLOBALS['_VERSION']->RELEASE, '1.5', '>=');
$v15 = true;
$task = JRequest::getVar('task','config');
require_once('docmanpaypal.class.php');

$componentdir = DS.'administrator' . $componentdir;
$dm = new docmanpaypal();
if (@$dm->constructRun == false) {
	$dm->__construct();
}

//$result = $dm->adminGetFilesInfo();
switch ($task) {
	case "hackdocman":
		if ($v15) {
			JToolBarHelper::title(JText::_("Patching DOCman..."),'docmanPayPalLogo');
		}
		$dm->hackDOCman();
		break;
	case "modifyprice":
		if ($v15) {
			JToolBarHelper::title("DOCman PayPal IPN " . $dm->getVersion() . ' ' . JText::_("COM_DOCMANPAYPAL_DMP_MODIFYPRICES"),'docmanPayPalLogo');
			JToolBarHelper::apply('saveprices');
			JToolBarHelper::cancel('config');
		}
		jimport('joomla.html.pagination');
		JHtml::_('script', 'system/core.js', false, true);

		$lim   = $mainframe->getUserStateFromRequest("$option.limit", 'limit', 15, 'int'); //I guess getUserStateFromRequest is for session or different reasons
		$lim0  = JRequest::getVar('limitstart', 0, '', 'int');
		$search = JRequest::getVar('search');
		if ($search!= '') {
			$where = " and dm.dmname like '%$search%'";
		}		
		$result = $dm->adminGetFilesInfo(JRequest::getVar('search'),$lim,$lim0);
		$database->setQuery("SELECT count(id) from #__docmanpaypal where 1 $where");
		$pageNav = new JPagination( $database->loadResult(), $lim0, $lim );
		
		?>
<link rel="stylesheet" href="components/com_docmanpaypal/css/redmond/jquery-ui-1.8.4.custom.css" type="text/css" media="all" />
<script src="components/com_docmanpaypal/js/jquery-1.4.2.min.js" type="text/javascript"></script>
<script src="components/com_docmanpaypal/js/jquery-ui-1.8.4.custom.min.js" type="text/javascript"></script>
<script src="components/com_docmanpaypal/js/iphone-style-checkboxes.js" type="text/javascript"></script>

<script>
jQuery.noConflict();
jQuery(document).ready(function() {
    jQuery(':checkbox').iphoneStyle();
});



</script>

<form action="index.php" method="post" name="adminForm" id="adminForm" class="adminForm">
<p class="pbtn">
<?php echo JText::_('COM_DOCMANPAYPAL_SEARCH'); ?>: <input type="text" class="input-small" name="search" value="<?php echo JRequest::getVar('search'); ?>" />
<input type="submit" value="Submit"  class="btn">
<?php echo JText::_("COM_DOCMANPAYPAL_DMP_DOWNLOADSPERSALE"); ?>: <input type="text" class="input-small" name="bulkDownloads" id="bulkDownloads" /> <input type="button" class="btn" value="Set" onClick="jQuery('.downloads').val(jQuery('#bulkDownloads').val()); jQuery('.downloads').effect('highlight', {}, 3000);" />
<?php echo JText::_("COM_DOCMANPAYPAL_DMP_SETALLSALES"); ?>: <input type="text" class="input-small" name="bulkSales" id="bulkSales" /> <input type="button" class="btn" value="Set" onClick="jQuery('.sales').val(jQuery('#bulkSales').val()); jQuery('.sales').effect('highlight', {}, 3000);" />
<?php echo JText::_("COM_DOCMANPAYPAL_DMP_SETALLPRICES"); ?>: <input type="text" class="input-small" name="bulkPrice" id="bulkPrice" /> <input type="button" class="btn" value="Set" onClick="jQuery('.price').val(jQuery('#bulkPrice').val()); jQuery('.price').effect('highlight', {}, 3000);" />
</p>
<table width="100%" border="0" cellspacing="1" id="adminlist" class="table table-bordered table-striped">
<thead>
<tr>
<th width="14%" align="center"><?php echo JText::_("COM_DOCMANPAYPAL_DMP_NAME"); ?></th>
<th width="28%" align="center"><?php echo JText::_("COM_DOCMANPAYPAL_DMP_FILENAME"); ?></th>
<th width="17%" align="center"><?php echo JText::_("COM_DOCMANPAYPAL_DMP_CATEGORY"); ?></th>
<th width="12%" align="center"><?php echo JText::_("COM_DOCMANPAYPAL_DMP_DOWNLOADSLIMIT"); ?></th>
<th width="12%" align="center"><?php echo JText::_("COM_DOCMANPAYPAL_DMP_SALESLIMIT"); ?></th>
<th width="2%" align="center"><?php echo JText::_("COM_DOCMANPAYPAL_DMP_PRICE"); ?></th>
<th width="5%" align="center"><?php echo JText::_("COM_DOCMANPAYPAL_DMP_OFFLINEGOOD"); ?></th>
<th width="5%" align="center"><?php echo JText::_("COM_DOCMANPAYPAL_DMP_VENDOR"); ?></th>
<th width="5%" align="center">ID</th>
</tr>
</thead>
<?php

$vendors = $dm->getVendors();
if (is_array($vendors)) {
	foreach ($result as $row) {
	?>
	 <tr class="row0">
	  <td><?php echo $row['name']; ?><br /><small></small>
	  <div class="cbtns<?php echo $row['id']; ?>" style="display:none;">
	  <textarea rows="2" cols="30" name="buttons[<?php echo $row['id'];?>]"><?php echo $row['buttons'];?></textarea>
	  </div>
	  </td>
	
	  <td><?php echo $row['filename']; ?></td>
	  <td><?php echo $row['category']; ?></td>
	  <td><input type="text" size="4" name="downloadslimit[<?php echo $row['id'];?>]" value="<?php echo isset($row['downloadslimit']) ? $row['downloadslimit'] : '0'; ?>" class="downloads input-small"  /></td>
	  <td><input type="text" size="4" name="saleslimit[<?php echo $row['id'];?>]" value="<?php echo isset($row['saleslimit']) ? $row['saleslimit'] : '0'; ?>" class="sales input-small"  /></td>
	  <td>
	  
	  <div class="input-prepend" style="width:90px;">
	  <span class="add-on">$</span>
	  <input class="price input-mini" style="width:40px;" id="appendedPrependedInput" type="text" name="price[<?php echo $row['id'];?>]" value="<?php echo $row['price']; ?>">
	  </div>
	  
	  <td>
	<input type="hidden" name="offlineGood[<?php echo $row['id']; ?>]" value="0" />
	<input type="checkbox" name="offlineGood[<?php echo $row['id']; ?>]" value="1" <?php if ($row['offlineGood'] == 1) { echo 'checked="checked"'; } ?> />
	  </td>
	  <td>
	<?php
	echo $dm->objectsToSelect($vendors,'vendor[' .  $row['id'] . ']','name','vendor_id',$row['vendor']);
	?>  
	  
	  </td>
	  <td>
	  <?php echo $row['id']; ?>
	  </td>
	 </tr>
	<?php
	}
}
?>
  <tfoot>
    <tr>
      <td colspan="9"><?php echo $pageNav->getListFooter(); ?></td>
    </tr>
  </tfoot>
</table>
<input type="hidden" name="option" value="<?php echo $option; ?>" />
<input type="hidden" name="task" value="modifyprice" />
</form>
<pre>

</pre>
<?php
		break;
	case "saveprices":
		if ($v15) {
			JToolBarHelper::title(JText::_("DOCman PayPal IPN Prices Saved!"),'docmanPayPalLogo');
		}
		$dm->updatePrices(JRequest::getVar('price'),JRequest::getVar('downloadslimit'),JRequest::getVar('saleslimit'),JRequest::getVar('offlineGood'),JRequest::getVar('vendor'),JRequest::getVar('buttons'));
		$mainframe->redirect('index.php?&option=com_docmanpaypal&task=modifyprice',JText::_("COM_DOCMANPAYPAL_DMP_PRICESUPDATED"));
		//echo "<h1>" . JText::_("COM_DOCMANPAYPAL_DMP_OPERATION_COMPLETED") . "</h1>";
		break;
	case "saveconfig":
		if ($v15) {
			JToolBarHelper::title(JText::_("DOCman PayPal IPN Configuration Saved!"),'docmanPayPalLogo');
		}
		$dm->saveConfig();
		$mainframe->redirect('index.php?&option=com_docmanpaypal&task=config',JText::_("COM_DOCMANPAYPAL_DMP_CONFIGURATIONSAVED"));
		//echo "<h1>" . JText::_("COM_DOCMANPAYPAL_DMP_OPERATION_COMPLETED") . "</h1>";
		break;
	case "config":
		$dm->hackDOCman();
		
		if ($v15) {
			JToolBarHelper::title(JText::_("DOCman PayPal IPN " . $dm->getVersion() . ' ' . JText::_("COM_DOCMANPAYPAL_DMP_CONFIGURATION")),'docmanPayPalLogo');
			JToolBarHelper::apply('saveconfig');
			//JToolBarHelper::help('loadhelp');
		}
		
		$dmpcfg = $dm->getConfig();
		//var_dump($dmpcfg);

?>
<link rel="stylesheet" href="components/com_docmanpaypal/css/redmond/jquery-ui-1.8.4.custom.css" type="text/css" media="all" />
<script src="components/com_docmanpaypal/js/jquery-1.4.2.min.js" type="text/javascript"></script>
<script src="components/com_docmanpaypal/js/jquery-ui-1.8.4.custom.min.js" type="text/javascript"></script>
<script src="components/com_docmanpaypal/js/iphone-style-checkboxes.js" type="text/javascript"></script>

<script>
jQuery.noConflict();
jQuery(document).ready(function() {
    jQuery(':checkbox').iphoneStyle();
});
</script>


<form name="adminForm" id="adminForm" method="post" action="index.php">
  <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td width="15" valign="top"><h1><?php echo JText::_("COM_DOCMANPAYPAL_DMP_GENERALCONFIGURATION"); ?>:</h1>
        <table width="500" class="adminlist table table-bordered table-striped" style="width:500px">
        <thead>
          <tr>
            <th><?php echo JText::_("COM_DOCMANPAYPAL_DMP_NAME"); ?></th>
            <th><?php echo JText::_("COM_DOCMANPAYPAL_DMP_VALUE"); ?></th>
          </tr>
        </thead>
        <tr>
          <td align="right"><strong>
            <?php echo JText::_("COM_DOCMANPAYPAL_DMP_MERCHANTSTOUSE"); ?>
          </strong>
			<?php echo JText::_("COM_DOCMANPAYPAL_DMP_MERCHANTSTOUSE_TIP"); ?>
          </td>
          <td><select name="merchants[]" size="4" multiple>
            <?php

$merchants = $dm->getMerchants();
$merchants_use = explode(',',$dmpcfg['merchants']);
foreach ($merchants as $tmp) {
?>
            <option value="<?php echo $tmp;; ?>" <?php if (in_array($tmp,$merchants_use)) { echo " selected=\"selected\""; } ?>>
              <?php echo $tmp;; ?>
              </option>
            <?php
}

?>
          </select></td>
        </tr>
        <!--
        <tr>
          <td>Allow resellers:</td>
          <td><label>
            <select name="allow_resellers" id="allow_resellers">
              <option value="Yes" <?php if ($dmpcfg['allow_resellers'] == 'Yes') { echo "selected=\"selected\""; } ?>>Yes</option>
              <option value="No" <?php if ($dmpcfg['allow_resellers'] == 'No') { echo "selected=\"selected\""; } ?>>No</option>
            </select>
            </label>
            To allow users to use this, you need to install and enable the mod_dmpuser in the modules menu.
            <br />
            <br />
            User types that are allowed to be resellers:          
            <br />
            <br />
<select name="reseller_usertypes[]" size="5" multiple>
<?php
$usertypes = $dm->getUserTypes();


$selected_usertypes = explode(',',$dmpcfg['reseller_usertypes']);
foreach ($usertypes as $tmp) {
?>      
	<option value="<?php echo $tmp['name'];; ?>" <?php if (in_array($tmp['name'],$selected_usertypes)) { echo " selected=\"selected\""; } ?>><?php echo $tmp['name'];; ?></option>
<?php
}
?>        
      </select>
            </td>
        </tr>
        -->
        <tr>
          <td align="right" valign="top"><strong>
            <?php echo JText::_("COM_DOCMANPAYPAL_DMP_FREEDOWNLOADSFOR"); ?>
          </strong>
            <?php echo JText::_("COM_DOCMANPAYPAL_DMP_FREEDOWNLOADSFOR_TIP"); ?>
          </td>
          <td><select name="free_for_usertypes[]" size="5" multiple>
            <?php
$usertypes = $dm->getUserTypes();
$selected_usertypes = explode(',',$dmpcfg['free_for_usertypes']);
foreach ($usertypes as $tmp) {
?>
            <option value="<?php echo $tmp['title']; ?>" <?php if (in_array($tmp['title'],$selected_usertypes)) { echo " selected=\"selected\""; } ?>>
              <?php echo $tmp['title'];; ?>
              </option>
            <?php
}
?>
          </select></td>
        </tr>



        <tr>
          <td align="right" valign="top"><strong>
            <span class="editlinktip hasTip" title="<?php echo JText::_("COM_DOCMANPAYPAL_DMP_EMAIL_DELIVERY_TIP"); ?>"><?php echo JText::_("COM_DOCMANPAYPAL_DMP_EMAIL_DELIVERY"); ?></span>:
          </strong></td>
          <td>
<input type="hidden" name="emailDelivery" value="0" />
<input type="checkbox" name="emailDelivery" value="1" <?php if ($dmpcfg['emailDelivery'] == 1) { echo 'checked="checked"'; } ?> />

<!--<a href="#" onClick="jQuery('#emailDeliverySettings').slideToggle(); return false;"><?php echo JText::_("COM_DOCMANPAYPAL_DMP_COLLAPSESETTINGS"); ?></a>
<div id="emailDeliverySettings" style="display:none;">
--><?php echo JText::_("COM_DOCMANPAYPAL_DMP_MAXFILESIZEINMB"); ?>: <input type="text" width="3" name="emailDeliveryMaxSizeInMB" value="<?php echo $dmpcfg['emailDeliveryMaxSizeInMB']; ?>" size="3" /> MB
<br />
<input type="radio" name="emailDeliveryDownloadLink" value="0" <?php if ($dmpcfg['emailDeliveryDownloadLink'] == 0) { echo 'checked="checked"'; } ?> /><?php echo JText::_("COM_DOCMANPAYPAL_DMP_DO_NOT_SEND_LINK"); ?><br />
<input type="radio" name="emailDeliveryDownloadLink" value="1" <?php if ($dmpcfg['emailDeliveryDownloadLink'] == 1) { echo 'checked="checked"'; } ?> /><?php echo JText::_("COM_DOCMANPAYPAL_DMP_ALWAYS_DOWNLOAD_LINK"); ?><br />
<input type="radio" name="emailDeliveryDownloadLink" value="2" <?php if ($dmpcfg['emailDeliveryDownloadLink'] == 2) { echo 'checked="checked"'; } ?> /><?php echo JText::_("COM_DOCMANPAYPAL_DMP_LARGE_FILES_ONLY"); ?><br />

<input type="hidden" name="emailDeliveryToAdmin" value="0" /><br />
<?php echo JText::_("COM_DOCMANPAYPAL_DMP_SENDCOPYTOADMIN"); ?>
<input type="checkbox" name="emailDeliveryToAdmin" value="1" <?php if ($dmpcfg['emailDeliveryToAdmin'] == 1) { echo 'checked="checked"'; } ?> />


<!--</div>
		--></td>
        </tr>

        <tr>
          <td align="right" valign="top"><strong>
            <?php echo JText::_("COM_DOCMANPAYPAL_DMP_CART"); ?>:
          </strong></td>
          <td>
<input type="hidden" name="useCart" value="0" />
<input type="checkbox" name="useCart" value="1" <?php if ($dmpcfg['useCart'] == 1) { echo 'checked="checked"'; } ?> />
          </td>
        </tr>

        <tr>
          <td align="right" valign="top"><strong>
            <?php echo JText::_("COM_DOCMANPAYPAL_DMP_PDFPREVIEW"); ?>:
          </strong></td>
          <td>
<input type="text" name="pdfPreviewPages" value="<?php echo (int)$dmpcfg['pdfPreviewPages']; ?>" />
          </td>
        </tr>
        
        <tr>
          <td align="right" valign="top">
          <strong>
            <span class="editlinktip hasTip" title="<?php echo JText::_("COM_DOCMANPAYPAL_DMP_ENCRYPTPDF_TIP"); ?>"><?php echo JText::_("COM_DOCMANPAYPAL_DMP_ENCRYPTPDF"); ?></span>:
          </strong>
          </td>
          <td>
<input type="hidden" name="encryptPDF" value="0" />
<input type="checkbox" name="encryptPDF" value="1" <?php if ($dmpcfg['encryptPDF'] == 1) { echo 'checked="checked"'; } ?> />
<input type="radio" name="pdfOrientation" id="pdfOrientation" value="portrait" <?php if ($dmpcfg['pdfOrientation'] == 'portrait') { echo 'checked'; } ?>>
<label for="pdfOrientation"><?php echo JText::_("COM_DOCMANPAYPAL_DMP_PORTRAIT"); ?></label>
<input type="radio" name="pdfOrientation" id="pdfOrientation2" value="landscape" <?php if ($dmpcfg['pdfOrientation'] == 'landscape') { echo 'checked'; } ?>>
<label for="pdfOrientation2"><?php echo JText::_("COM_DOCMANPAYPAL_DMP_LANDSCAPE"); ?></label>

          </td>
        </tr>

        <tr>
          <td align="right" valign="top"><strong>
            <?php echo JText::_("COM_DOCMANPAYPAL_DMP_MORETHANONCE"); ?>
          </strong></td>
          <td>
<input type="hidden" name="moreThanOnce" value="0" />
<input type="checkbox" name="moreThanOnce" value="1" <?php if ($dmpcfg['moreThanOnce'] == 1) { echo 'checked="checked"'; } ?> />
          </td>
        </tr>
        
        <tr>
          <td align="right" valign="top"><strong>
            <span class="editlinktip hasTip" title="<?php echo JText::_("COM_DOCMANPAYPAL_DMP_BUTTONSTAYSBUYNOW_TIP"); ?>"><?php echo JText::_("COM_DOCMANPAYPAL_DMP_BUTTONSTAYSBUYNOW"); ?></span>
          </strong></td>
          <td>
<input type="hidden" name="buttonStaysBuyNow" value="0" />
<input type="checkbox" name="buttonStaysBuyNow" value="1" <?php if ($dmpcfg['buttonStaysBuyNow'] == 1) { echo 'checked="checked"'; } ?> />
          </td>
        </tr>
        
        <tr>
          <td align="right" valign="top"><strong>
            <?php echo JText::_("COM_DOCMANPAYPAL_DMP_USEVAT"); ?> <input name="vatPercent" type="text" class="input-mini" maxlength="2" value="<?php echo $dmpcfg['vatPercent']; ?>" />%
          </strong></td>
          <td>
<input type="hidden" name="useVat" value="0" />
<input type="checkbox" name="useVat" value="1" <?php if ($dmpcfg['useVat'] == 1) { echo 'checked="checked"'; } ?> />
          </td>
        </tr>
        
        <tr>
          <td align="right" valign="top"><strong>
            <?php echo JText::_("COM_DOCMANPAYPAL_DMP_REQUIREREGISTRATION"); ?>
          </strong></td>
          <td>
<input type="hidden" name="requireRegistration" value="0" />
<input type="checkbox" name="requireRegistration" value="1" <?php if ($dmpcfg['requireRegistration'] == 1) { echo 'checked="checked"'; } ?> />
          </td>
        </tr>
     
        <tr>
          <td align="right" valign="top"><strong>
            <?php echo JText::_("COM_DOCMANPAYPAL_DMP_PRICE_FORMAT"); ?>
          </strong></td>
          <td>
          	<input type="hidden" id="priceFormat" name="priceFormat" value="<?php echo isset($dmpcfg['priceFormat']) ? htmlentities($dmpcfg['priceFormat']) : ''; ?>" />
			<div id="priceFormatEditor" style="display:none;"><textarea name="priceFormatTextArea" id="priceFormatTextArea" onChange="jQuery('#priceFormat').val(jQuery('#priceFormatTextArea').val());" style="width:300px; height:100px;"><?php echo isset($dmpcfg['priceFormat']) ? $dmpcfg['priceFormat'] : ''; ?></textarea></div>
			<a href="javascript:void(0);" onClick="jQuery('#priceFormatTextArea').val(jQuery('#priceFormat').val()); jQuery('#priceFormatEditor').dialog({ resizable: false, width:325, height:200, title: '<?php echo JText::_("COM_DOCMANPAYPAL_DMP_PRICE_FORMATEDITOR"); ?>',modal: true, buttons: { 'Close': function() { jQuery('#priceFormat').val(jQuery('#priceFormatTextArea').val()); jQuery('#priceFormatEditor').dialog('close'); } } });"><?php echo JText::_("COM_DOCMANPAYPAL_DMP_CLICKTOEDIT"); ?></a>
          </td>
        </tr><!--
                
        <tr>
          <td align="right" width="50%"><strong>
            <?php echo JText::_("COM_DOCMANPAYPAL_DMP_FREEDOWNLOADSNEWUSERS"); ?>
          </strong><br />
          <?php echo JText::_("COM_DOCMANPAYPAL_DMP_FREEDOWNLOADSNEWUSERSINFO"); ?>
          </td>
          <td><input type="text" name="free_download_after_seconds" id="free_download_after_seconds" value="<?php echo $dmpcfg['free_download_after_seconds']; ; ?>">
            </td>
        </tr>
        
      --></table>

<center>
<!-- http://www.LiveZilla.net Chat Button Link Code --><div style="text-align:center;width:191px;"><a href="javascript:void(window.open('http://motov.net/livezilla/chat.php','','width=590,height=580,left=0,top=0,resizable=yes,menubar=no,location=no,status=yes,scrollbars=yes'))"><img src="http://motov.net/livezilla/image.php?id=01" width="191" height="69" border="0" alt="LiveZilla Live Help"></a><noscript><div><a href="http://motov.net/livezilla/chat.php" target="_blank">Start Live Help Chat</a></div></noscript><div style="margin-top:2px;"><a href="http://www.livezilla.net" target="_blank" title="LiveZilla Live Chat" style="font-size:10px;color:#bfbfbf;text-decoration:none;font-family:verdana,arial,tahoma;">LiveZilla Live Chat</a></div></div><!-- http://www.LiveZilla.net Chat Button Link Code --><!-- http://www.LiveZilla.net Tracking Code --><div id="livezilla_tracking" style="display:none"></div><script type="text/javascript">
<!-- DON'T PLACE IN HEAD ELEMENT -->
var script = document.createElement("script");script.type="text/javascript";var src = "http://motov.net/livezilla/server.php?request=track&output=jcrpt&nse="+Math.random();setTimeout("script.src=src;document.getElementById('livezilla_tracking').appendChild(script)",1);</script><!-- http://www.LiveZilla.net Tracking Code -->
<?php 
if ($dm->isTrial) {
?>
<a href="http://motov.net/docman-paypal.html" target="_blank"><img src="<?php JURI::base(); ?>/administrator/components/com_docmanpaypal/images/trial_box.png" /></a>
<?php 
}
?>
</center>

<input type="hidden" name="live_site" value="<?php echo JURI::base(); ?>" />
      </td>
      <td width="15" valign="top">&nbsp;</td>
      <td valign="top"><h1><?php echo JText::_("COM_DOCMANPAYPAL_DMP_PAGEEDITOR"); ?>:      
        
      </h1>
        <script type="text/javascript">
	jQuery(function() {
		jQuery("#tabs").tabs();
	});
	</script>
  
  
  
<div id="tabs">
  <ul>
		<li><a href="#tabs-1"><?php echo JText::_("COM_DOCMANPAYPAL_DMP_THANK_YOU_PAGE"); ?></a></li>
		<li><a href="#tabs-2"><?php echo JText::_("COM_DOCMANPAYPAL_DMP_MERCHANT_SELECT_PAGE"); ?></a></li>
		<li><a href="#tabs-3"><?php echo JText::_("COM_DOCMANPAYPAL_DMP_SALES_LIMIT_PAGE"); ?></a></li>
		<li><a href="#tabs-4"><?php echo JText::_("COM_DOCMANPAYPAL_DMP_DOWNLOADS_LIMIT_PAGE"); ?></a></li>
		<li><a href="#tabs-5"><?php echo JText::_("COM_DOCMANPAYPAL_DMP_ORDER_CANCELED_PAGE"); ?></a></li>
		<li><a href="#tabs-6"><?php echo JText::_("COM_DOCMANPAYPAL_DMP_EMAIL_DELIVERY"); ?></a></li>
	</ul>
	<div id="tabs-1">
		
            <?php
		$editor = JFactory::getEditor();
		$params = array( 'smilies'=> '0' ,
		                 'style'  => '1' ,  
		                 'layer'  => '0' , 
		                 'table'  => '0' ,
		                 'clear_entities'=>'0'
		                 );
		$editor = JFactory::getEditor();
		
        echo $editor->display( 'thankyoupagecode', $dmpcfg['thankyoupagecode'], '100%', '400', '20', '20');
        echo '<div style="float:none;"><br /><br /><h3>' . JText::_("COM_DOCMANPAYPAL_DMP_THANKYOUPAGECODECART") . '</h3></div>';
		$params = array( 'smilies'=> '0' ,
		                 'style'  => '1' ,  
		                 'layer'  => '0' , 
		                 'table'  => '0' ,
		                 'clear_entities'=>'0'
		                 );
        echo $editor->display( 'thankyoupagecodeCart', $dmpcfg['thankyoupagecodeCart'], '100%', '400', '20', '20');
        ?>
          
		<br />
	</div>
	<div id="tabs-2">
		
            <?php
        echo $editor->display( 'merchant_header', $dmpcfg['merchant_header'], '100%', '400', '20', '20');
        //editorArea( 'merchant_header', $dmpcfg['merchant_header'] , 'merchant_header', '100%;', '300', '75', '20' ) ;
        ?>
      
	<br />
	</div>
	<div id="tabs-3">
		<?php
          echo $editor->display( 'saleslimitpage', $dmpcfg['saleslimitpage'], '100%', '400', '20', '20');
        //editorArea( 'saleslimitpage', $dmpcfg['saleslimitpage'] , 'saleslimitpage', '100%;', '300', '75', '20' ) ;
        ?>
        <br />
	</div>
    <div id="tabs-4">
    <?php
          echo $editor->display( 'downloadslimitpage', $dmpcfg['downloadslimitpage'], '100%', '400', '20', '20');
        //editorArea( 'downloadslimitpage', $dmpcfg['downloadslimitpage'] , 'downloadslimitpage', '100%;', '300', '75', '20' ) ;
        ?>
	<br />
    </div>
    <div id="tabs-5">
    <?php
          echo $editor->display( 'ordercanceledpage', $dmpcfg['ordercanceledpage'], '100%', '400', '20', '20');
        //editorArea( 'downloadslimitpage', $dmpcfg['downloadslimitpage'] , 'downloadslimitpage', '100%;', '300', '75', '20' ) ;
        ?>
	<br />
    </div>
    <div id="tabs-6">
    <b><?php echo JText::_("COM_DOCMANPAYPAL_DMP_EMAIL_DELIVERY_SUBJECT"); ?></b>  <input type="text" name="emailDeliverySubject" value="<?php echo $dmpcfg['emailDeliverySubject']; ?>" style="width:100%;" /><br /><br />
    <?php
          echo $editor->display( 'emailDeliveryBody', $dmpcfg['emailDeliveryBody'], '100%', '400', '20', '20');
        //editorArea( 'downloadslimitpage', $dmpcfg['downloadslimitpage'] , 'downloadslimitpage', '100%;', '300', '75', '20' ) ;
        ?>
	<br />
    </div>
</div></td>
    </tr>
  </table>
    <input name="task" type="hidden" id="task" value="saveconfig">
    <input name="option" type="hidden" id="option" value="<?php echo $option;; ?>">
<br />
    <?php
		//$dm->mosLoadAdminModules('dmpconfig');
		break;
	case "merchantconfig":
	$dmpcfg = $dm->getConfig();
	if ($v15) {
		JToolBarHelper::title(JText::_("DOCman PayPal IPN " . $dm->getVersion() . ' ' . JText::_("COM_DOCMANPAYPAL_DMP_MERCHANTCONFIGURATION")),'docmanPayPalLogo');
		JToolBarHelper::apply('saveconfig');
		JToolBarHelper::cancel('config');
	}
?>
</h3>
</form>
	<form action="" method="post" name="adminForm" id="adminForm">
<img border="0" src="components/com_docmanpaypal/paypal.gif" /><h2>PayPal.com - Configuration</h2>
	  <table width="500" border="0" class="adminlist table table-striped">
      <thead>
        <tr>
          <th width="50%">Name</th>
          <th>Value</th>
        </tr>
       </thead>
        <tr>
          <td align="right">PayPal Email:</td>
          <td><label>
            <input name="paypalemail" type="text" id="paypalemail" value="<?php echo $dmpcfg['paypalemail']; ; ?>">
            </label>          </td>
        </tr>
        <tr>
          <td align="right">Notify Email:</td>
          <td><label>
            <input name="notifyemail" type="text" id="notifyemail" value="<?php echo $dmpcfg['notifyemail']; ; ?>">
            </label>          </td>
        </tr>
        <tr>
          <td align="right">Sandbox Mode:</td>
          <td><label>
            <select name="sandbox" id="sandbox">
              <option value="Yes" <?php if ($dmpcfg['sandbox'] == 'Yes') { echo "selected=\"selected\""; } ?>>Yes</option>
              <option value="No" <?php if ($dmpcfg['sandbox'] == 'No') { echo "selected=\"selected\""; } ?>>No</option>
            </select>
            </label>          </td>
        </tr>
        <tr>
          <td align="right">Currency</td>
        <td><select name="currency" id="currency">
              <option value="EUR" <?php if ($dmpcfg['currency'] == 'EUR') { echo "selected=\"selected\""; } ?>>Euro</option>
              <option value="USD" <?php if ($dmpcfg['currency'] == 'USD') { echo "selected=\"selected\""; } ?>>US Dollar</option>
              <option value="GBP" <?php if ($dmpcfg['currency'] == 'GBP') { echo "selected=\"selected\""; } ?>>Pounds Sterling</option>
              <option value="AUD" <?php if ($dmpcfg['currency'] == 'AUD') { echo "selected=\"selected\""; } ?>>Australian Dollar</option>
              <option value="CAD" <?php if ($dmpcfg['currency'] == 'CAD') { echo "selected=\"selected\""; } ?>>Canadian Dollar</option>
              <option value="JPY" <?php if ($dmpcfg['currency'] == 'JPY') { echo "selected=\"selected\""; } ?>>Japan Yen</option>
              <option value="NZD" <?php if ($dmpcfg['currency'] == 'NZD') { echo "selected=\"selected\""; } ?>>New Zealand Dollar</option>
              <option value="CHF" <?php if ($dmpcfg['currency'] == 'CHF') { echo "selected=\"selected\""; } ?>>Swiss Franc</option>
              <option value="HKD" <?php if ($dmpcfg['currency'] == 'HKD') { echo "selected=\"selected\""; } ?>>Hong Kong Dollar</option>
              <option value="SGD" <?php if ($dmpcfg['currency'] == 'SGD') { echo "selected=\"selected\""; } ?>>Singapore Dollar</option>
              <option value="SEK" <?php if ($dmpcfg['currency'] == 'SEK') { echo "selected=\"selected\""; } ?>>Sweden Krona</option>
              <option value="DKK" <?php if ($dmpcfg['currency'] == 'DKK') { echo "selected=\"selected\""; } ?>>Danish Krone</option>
              <option value="PLN" <?php if ($dmpcfg['currency'] == 'PLN') { echo "selected=\"selected\""; } ?>>New Zloty</option>
              <option value="NOK" <?php if ($dmpcfg['currency'] == 'NOK') { echo "selected=\"selected\""; } ?>>Norwegian Krone</option>
              <option value="HUF" <?php if ($dmpcfg['currency'] == 'HUF') { echo "selected=\"selected\""; } ?>>Forint</option>
              <option value="CZK" <?php if ($dmpcfg['currency'] == 'HUF') { echo "selected=\"selected\""; } ?>>Czech Koruna</option>
              <option value="THB" <?php if ($dmpcfg['currency'] == 'THB') { echo "selected=\"selected\""; } ?>>Thai Baht</option>
          </select></td>
        </tr>
        <tr>
        <td align="right">
        Force PayPal Location:
        </td>
        <td>
        <select name="paypalCountry">
<?php
foreach ($paypalCountries as $cc => $cn) {
	unset($selected);
	if ($dmpcfg['paypalCountry'] == $cc) {
		$selected = ' selected';
	}
	echo "<option value=\"$cc\"$selected>$cn</option>\r\n";
}
?>
		</select>
        </td>
        </tr>
        <tr>
          <td align="right">Processing page:</td>
          <td><?php
		$editor =& JFactory::getEditor();
		$params = array( 'smilies'=> '0' ,
		                 'style'  => '1' ,  
		                 'layer'  => '0' , 
		                 'table'  => '0' ,
		                 'clear_entities'=>'0'
		                 );
        echo $editor->display( 'paypal_processing_page', $dmpcfg['paypal_processing_page'], '100%', '300', '20', '20', true, $params );
        
        //editorArea( 'paypal_processing_page', $dmpcfg['paypal_processing_page'] , 'paypal_processing_page', '100%;', '300', '75', '20' ) ;
        ?></td>
        </tr>
      </table>
      
<img border="0" src="components/com_docmanpaypal/micropayment.de.png" />
<h2>Micropayment.de Configuration	</h2>
	  <table width="500" border="0" class="adminlist table table-striped">
      <thead>
        <tr>
          <th width="50%">Name</th>
          <th>Value</th>
        </tr>
       </thead>
        <tr>
          <td align="right">Call2Pay event URL:</td>
          <td><label>
            <input name="Call2Pay" type="text" id="Call2Pay" value="<?php echo $dmpcfg['Call2Pay']; ; ?>">
            </label>          </td>
        </tr>
        <tr>
          <td align="right">MobilePay event URL:</td>
          <td><label>
            <input name="MobilePay" type="text" id="MobilePay" value="<?php echo $dmpcfg['MobilePay']; ; ?>">
            </label>          </td>
        </tr>
        <tr>
          <td align="right">eBank2Pay event URL:</td>
          <td><label>
            <input name="eBank2Pay" type="text" id="eBank2Pay" value="<?php echo $dmpcfg['eBank2Pay']; ; ?>">
            </label>          </td>
        </tr>
        
        <tr>
          <td align="right">Header:</td>
          <td><?php
		$editor =& JFactory::getEditor();
		$params = array( 'smilies'=> '0' ,
		                 'style'  => '1' ,  
		                 'layer'  => '0' , 
		                 'table'  => '0' ,
		                 'clear_entities'=>'0'
		                 );
        echo $editor->display( 'micropaymentDeHeader', $dmpcfg['micropaymentDeHeader'], '100%', '300', '20', '20', true, $params );
        
        //editorArea( 'paypal_processing_page', $dmpcfg['paypal_processing_page'] , 'paypal_processing_page', '100%;', '300', '75', '20' ) ;
        ?></td>
        </tr>
      </table>
      
<?php 
/*

<img border="0" src="components/com_docmanpaypal/moneybookers.gif" /><h2>Moneybookers.com - Configuration</h2>
	  <table width="500" border="0" class="adminlist">
      <thead>
        <tr>
          <th width="50%">Name</th>
          <th>Value</th>
        </tr>
       </thead>
        <tr>
          <td align="right">Moneybookers Email:</td>
          <td><label>
            <input name="moneybookers_email" type="text" id="moneybookers_email" value="<?php echo $dmpcfg['moneybookers_email']; ; ?>">
            </label>          </td>
        </tr>
        <tr>
          <td align="right">Notify Email:</td>
          <td><label>
            <input name="moneybookers_notifyemail" type="text" id="moneybookers_notifyemail" value="<?php echo $dmpcfg['moneybookers_notifyemail']; ; ?>">
            </label>          </td>
        </tr>
        <tr>
          <td align="right">Currency</td>
          <td><select name="moneybookers_currency">
<option value="AUD" <?php if ($dmpcfg['moneybookers_currency'] == 'AUD') { echo "selected=\"selected\""; } ?>>Australian Dollar</option>
<option value="GBP" <?php if ($dmpcfg['moneybookers_currency'] == 'GBP') { echo "selected=\"selected\""; } ?>>British Pound</option>
<option value="BGN" <?php if ($dmpcfg['moneybookers_currency'] == 'BGN') { echo "selected=\"selected\""; } ?>>Bulgarian Leva</option>
<option value="CAD" <?php if ($dmpcfg['moneybookers_currency'] == 'CAD') { echo "selected=\"selected\""; } ?>>Canadian Dollar</option>
<option value="HRK" <?php if ($dmpcfg['moneybookers_currency'] == 'HRK') { echo "selected=\"selected\""; } ?>>Croatian kuna</option>
<option value="CZK" <?php if ($dmpcfg['moneybookers_currency'] == 'CZK') { echo "selected=\"selected\""; } ?>>Czech Koruna</option>
<option value="DKK" <?php if ($dmpcfg['moneybookers_currency'] == 'DKK') { echo "selected=\"selected\""; } ?>>Danish Krone</option>
<option value="EUR" <?php if ($dmpcfg['moneybookers_currency'] == 'EUR') { echo "selected=\"selected\""; } ?>>Euro</option>
<option value="HKD" <?php if ($dmpcfg['moneybookers_currency'] == 'HKD') { echo "selected=\"selected\""; } ?>>Hong Kong Dollar</option>
<option value="HUF" <?php if ($dmpcfg['moneybookers_currency'] == 'HUF') { echo "selected=\"selected\""; } ?>>Hungarian Forint</option>
<option value="ISK" <?php if ($dmpcfg['moneybookers_currency'] == 'ISK') { echo "selected=\"selected\""; } ?>>Iceland Krona</option>
<option value="INR" <?php if ($dmpcfg['moneybookers_currency'] == 'INR') { echo "selected=\"selected\""; } ?>>Indian Rupee</option>
<option value="ILS" <?php if ($dmpcfg['moneybookers_currency'] == 'ILS') { echo "selected=\"selected\""; } ?>>Israeli Shekel</option>
<option value="JPY" <?php if ($dmpcfg['moneybookers_currency'] == 'JPY') { echo "selected=\"selected\""; } ?>>Japanese Yen</option>
<option value="EEK" <?php if ($dmpcfg['moneybookers_currency'] == 'EEK') { echo "selected=\"selected\""; } ?>>Kroon</option>
<option value="LVL" <?php if ($dmpcfg['moneybookers_currency'] == 'LVL') { echo "selected=\"selected\""; } ?>>Latvian Lat</option>
<option value="LTL" <?php if ($dmpcfg['moneybookers_currency'] == 'LTL') { echo "selected=\"selected\""; } ?>>Lithuanian litas</option>
<option value="MYR" <?php if ($dmpcfg['moneybookers_currency'] == 'MYR') { echo "selected=\"selected\""; } ?>>Malaysian Ringgit</option>
<option value="TRY" <?php if ($dmpcfg['moneybookers_currency'] == 'TRY') { echo "selected=\"selected\""; } ?>>New Turkish Lira</option>
<option value="NZD" <?php if ($dmpcfg['moneybookers_currency'] == 'NZD') { echo "selected=\"selected\""; } ?>>New Zealand Dollar</option>
<option value="NOK" <?php if ($dmpcfg['moneybookers_currency'] == 'NOK') { echo "selected=\"selected\""; } ?>>Norwegian Krone</option>
<option value="PLN" <?php if ($dmpcfg['moneybookers_currency'] == 'PLN') { echo "selected=\"selected\""; } ?>>Polish Zloty</option>
<option value="RON" <?php if ($dmpcfg['moneybookers_currency'] == 'RON') { echo "selected=\"selected\""; } ?>>Romanian Leu New</option>
<option value="SGD" <?php if ($dmpcfg['moneybookers_currency'] == 'SGD') { echo "selected=\"selected\""; } ?>>Singapore Dollar</option>
<option value="SKK" <?php if ($dmpcfg['moneybookers_currency'] == 'SKK') { echo "selected=\"selected\""; } ?>>Slovakian Koruna</option>
<option value="ZAR" <?php if ($dmpcfg['moneybookers_currency'] == 'ZAR') { echo "selected=\"selected\""; } ?>>South-African Rand</option>
<option value="KRW" <?php if ($dmpcfg['moneybookers_currency'] == 'KRW') { echo "selected=\"selected\""; } ?>>South-Korean Won</option>
<option value="SEK" <?php if ($dmpcfg['moneybookers_currency'] == 'SEK') { echo "selected=\"selected\""; } ?>>Swedish Krona</option>
<option value="CHF" <?php if ($dmpcfg['moneybookers_currency'] == 'CHF') { echo "selected=\"selected\""; } ?>>Swiss Franc</option>
<option value="TWD" <?php if ($dmpcfg['moneybookers_currency'] == 'TWD') { echo "selected=\"selected\""; } ?>>Taiwan Dollar</option>
<option value="THB" <?php if ($dmpcfg['moneybookers_currency'] == 'THB') { echo "selected=\"selected\""; } ?>>Thailand Baht</option>
<option value="USD" <?php if ($dmpcfg['moneybookers_currency'] == 'USD') { echo "selected=\"selected\""; } ?>>U.S. Dollar</option>
</select></td>
        </tr>
        <tr>
          <td align="right">Processing page:</td>
          <td><?php
		$editor =& JFactory::getEditor();
		$params = array( 'smilies'=> '0' ,
		                 'style'  => '1' ,  
		                 'layer'  => '0' , 
		                 'table'  => '0' ,
		                 'clear_entities'=>'0'
		                 );
        echo $editor->display( 'moneybookers_processing_page', $dmpcfg['moneybookers_processing_page'], '100%', '300', '20', '20', true, $params );
        //editorArea( 'moneybookers_processing_page', $dmpcfg['moneybookers_processing_page'] , 'moneybookers_processing_page', '100%;', '300', '75', '20' ) ;
        ?></td>
        </tr>
      </table>

<img border="0" src="components/com_docmanpaypal/netcash.jpg" />
<h2>Netcash.Co.Za - Configuration</h2>
	  <table width="500" border="0" class="adminlist">
      <thead>
        <tr>
          <th width="50%">Name</th>
          <th>Value</th>
        </tr>
       </thead>
        <tr>
          <td align="right">Netcash Username:</td>
          <td><label>
            <input name="netcash_username" type="text" id="netcash_username" value="<?php echo $dmpcfg['netcash_username']; ; ?>">
            </label>          </td>
        </tr>
        <tr>
          <td align="right">Netcash Password:</td>
          <td><input name="netcash_password" type="text" id="netcash_password" value="<?php echo $dmpcfg['netcash_password']; ; ?>"></td>
        </tr>
        <tr>
          <td align="right">Netcash PIN:</td>
          <td><input name="netcash_pin" type="text" id="netcash_pin" value="<?php echo $dmpcfg['netcash_pin']; ; ?>"></td>
        </tr>
        <tr>
          <td align="right">Netcash Terminal Number:</td>
          <td><input name="netcash_terminal" type="text" id="netcash_terminal" value="<?php echo $dmpcfg['netcash_terminal']; ; ?>"></td>
        </tr>
        <tr>
          <td align="right">Notify Email:</td>
          <td><label>
            <input name="netcash_notifyemail" type="text" id="netcash_notifyemail" value="<?php echo $dmpcfg['netcash_notifyemail']; ; ?>">
            </label>          </td>
        </tr>
        <tr>
          <td align="right">Processing page:</td>
          <td><?php
		$editor =& JFactory::getEditor();
		$params = array( 'smilies'=> '0' ,
		                 'style'  => '1' ,  
		                 'layer'  => '0' , 
		                 'table'  => '0' ,
		                 'clear_entities'=>'0'
		                 );
        echo $editor->display( 'netcash_processing_page', $dmpcfg['netcash_processing_page'], '100%', '300', '20', '20', true, $params );
        //editorArea( 'netcash_processing_page', $dmpcfg['netcash_processing_page'] , 'netcash_processing_page', '100%;', '300', '75', '20' ) ;
        ?></td>
        </tr>
        <tr>
          <td align="right">Data URL:</td>
          <td>
            <?php echo JURI::root() . 'index.php?option=com_docmanpaypal&task2=ipn&merchant=Netcash.co.za'; ?>
            </td>
        </tr>
        <tr>
          <td align="right">Accept URL:</td>
          <td>
            <?php echo JURI::root() . 'index.php?'; ?>
            </td>
        </tr>
        <tr>
          <td align="right">Reject URL:</td>
          <td>
            <?php echo JURI::root() . 'index.php?&task=doc_download&task2=order_canceled&option=com_docmanpaypal'; ?>
            </td>
        </tr>
      </table>

<img border="0" src="components/com_docmanpaypal/authorizenet_logo.gif" />
<h2>Authorize.Net - Configuration</h2>
	  <table width="500" border="0" class="adminlist">
      <thead>
        <tr>
          <th width="50%">Name</th>
          <th>Value</th>
        </tr>
       </thead>
        <tr>
          <td align="right">Login ID:</td>
          <td><label>
            <input name="authorizenet_login_id" type="text" id="authorizenet_login_id" value="<?php echo $dmpcfg['authorizenet_login_id']; ; ?>">
            </label>          </td>
        </tr>
        <tr>
          <td align="right">Transaction Key:</td>
          <td><input name="authorizenet_transaction_key" type="text" id="authorizenet_transaction_key" value="<?php echo $dmpcfg['authorizenet_transaction_key']; ; ?>"></td>
        </tr>
        <tr>
          <td align="right">MD5 Setting:</td>
          <td><input name="authorizenet_md5_setting" type="text" id="authorizenet_md5_setting" value="<?php echo $dmpcfg['authorizenet_md5_setting']; ; ?>"></td>
        </tr>
        <tr>
          <td align="right">Notify Email:</td>
          <td><label>
            <input name="authorizenet_notifyemail" type="text" id="authorizenet_notifyemail" value="<?php echo $dmpcfg['authorizenet_notifyemail']; ; ?>">
            </label>          </td><!--
        </tr>
        <tr>
          <td align="right">Test Mode:</td>
          <td><label>
            <select name="authorizenet_test_mode" id="authorizenet_test_mode">
              <option value="1" <?php if ($dmpcfg['authorizenet_test_mode'] == '1') { echo "selected=\"selected\""; } ?>>Yes</option>
              <option value="0" <?php if ($dmpcfg['authorizenet_test_mode'] == '0') { echo "selected=\"selected\""; } ?>>No</option>
            </select>
            </label>          </td>
        </tr>        <tr>
          <td align="right">Page Header:</td>
          <td><?php
		$editor =& JFactory::getEditor();
		$params = array( 'smilies'=> '0' ,
		                 'style'  => '1' ,  
		                 'layer'  => '0' , 
		                 'table'  => '0' ,
		                 'clear_entities'=>'0'
		                 );
        echo $editor->display( 'authorizenet_processing_page', $dmpcfg['authorizenet_processing_page'], '100%', '300', '20', '20', true, $params );
        //editorArea( 'authorizenet_processing_page', $dmpcfg['authorizenet_processing_page'] , 'authorizenet_processing_page', '100%;', '300', '75', '20' ) ;
        ?></td>
        </tr>
      </table>   



 */


/*
<img border="0" src="<?php echo $componentdir;; ?>/googleCheckout.png" />
<h2>Google Checkout - Configuration</h2>
	  <table width="500" border="0" class="adminlist">
      <thead>
        <tr>
          <th width="50%">Name</th>
          <th>Value</th>
        </tr>
       </thead>
        <tr>
          <td>Google Checkout Email:</td>
          <td><label>
            <input name="googleCheckout_email" type="text" id="googleCheckout_email" value="<?php echo $dmpcfg['googleCheckout_email']; ; ?>">
            </label>          </td>
        </tr>
        <tr>
          <td>Notify Email:</td>
          <td><label>
            <input name="googleCheckout_notifyemail" type="text" id="googleCheckout_notifyemail" value="<?php echo $dmpcfg['googleCheckout_notifyemail']; ; ?>">
            </label>          </td>
        </tr>
        <tr>
          <td>Merchant ID:</td>
          <td><label>
            <input name="googleCheckout_MerchantID" type="text" id="googleCheckout_MerchantID" value="<?php echo $dmpcfg['googleCheckout_MerchantID']; ; ?>">
            </label>          </td>
        </tr>
        <tr>
          <td>Description:</td>
          <td><label>
            <input name="googleCheckout_description" type="text" id="googleCheckout_description" value="<?php echo $dmpcfg['googleCheckout_description']; ; ?>">
            </label>          </td>
        </tr>
        <tr>
          <td>Currency</td>
          <td><select name="googleCheckout_currency">
<option value="GBP" <?php if ($dmpcfg['googleCheckout_currency'] == 'GBP') { echo "selected=\"selected\""; } ?>>British Pound</option>
<option value="USD" <?php if ($dmpcfg['googleCheckout_currency'] == 'USD') { echo "selected=\"selected\""; } ?>>U.S. Dollar</option>
</select></td>
        </tr>
        <tr>
          <td>Processing page:</td>
          <td><?php
		$editor =& JFactory::getEditor();
		$params = array( 'smilies'=> '0' ,
		                 'style'  => '1' ,  
		                 'layer'  => '0' , 
		                 'table'  => '0' ,
		                 'clear_entities'=>'0'
		                 );
        echo $editor->display( 'googleCheckout_processing_page', $dmpcfg['googleCheckout_processing_page'], '100%', '300', '20', '20', true, $params );
        //editorArea( 'googleCheckout_processing_page', $dmpcfg['googleCheckout_processing_page'] , 'googleCheckout_processing_page', '100%;', '300', '75', '20' ) ;
        ?></td>
        </tr>
        <tr>
          <td>Integration IPN POST URL (HTML):</td>
          <td><b><?php echo $dmpcfg['live_site']; ?>/index.php?option=com_docmanpaypal&task=ipn&merchant=Google+Checkout</b></td>
        </tr>
      </table>      
<br>
 */
?>
<div align="center">
<label>
            <input type="submit" name="Save" id="Save" value="Save Merchant Configuration!">
            <input name="task" type="hidden" id="task" value="saveconfig">
            <input name="option" type="hidden" id="option" value="<?php echo $option;; ?>">
</label>
</div>    
</form>
<?php
		break;

	case "mySales":
		if ($v15) {
			JToolBarHelper::title(JText::_("DOCman PayPal IPN " . $dm->getVersion() . " Orders"),'docmanPayPalLogo');
		}
		
		if ($my->id > 0) {
		JHtml::_('script', 'system/core.js', false, true);
		
		echo "<h1>" . JText::_("COM_DOCMANPAYPAL_DMP_YOURSALES") . "</h1>" . JText::_("COM_DOCMANPAYPAL_DMP_YOURSALESEXPLAIN");
		?>
		<form action="index.php" method="get" name="adminForm" id="adminForm" class="adminForm">
				<table class="adminlist table table-bordered table-striped" width="100%" border="0" cellpadding="4" cellspacing="4">
		<thead>
			<tr>
				<th class="sectiontableheader" width="10" nowrap><?php echo JText::_("COM_DOCMANPAYPAL_DMP_ORDER"); ?></td>
				<th class="sectiontableheader"><?php echo JText::_("COM_DOCMANPAYPAL_DMP_ITEMNAME"); ?></th>
				<th class="sectiontableheader"><?php echo JText::_("COM_DOCMANPAYPAL_DMP_MERCHANT"); ?></th>
				<th class="sectiontableheader"><?php echo JText::_("COM_DOCMANPAYPAL_DMP_NAME"); ?></th>
				<th class="sectiontableheader"><?php echo JText::_("COM_DOCMANPAYPAL_DMP_ADDRESS"); ?></th>
				<th class="sectiontableheader"><?php echo JText::_("COM_DOCMANPAYPAL_DMP_DATE"); ?></th>
				<th class="sectiontableheader"><?php echo JText::_("COM_DOCMANPAYPAL_DMP_EMAIL"); ?></th>
				<th class="sectiontableheader"><?php echo JText::_("COM_DOCMANPAYPAL_DMP_AMOUNT"); ?></th>
				<th class="sectiontableheader"><?php echo JText::_("COM_DOCMANPAYPAL_DMP_DOWNLOAD"); ?> #</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$limit        = intval( JRequest::getVar( 'limit', 0 ) );
			$limitstart = intval( JRequest::getVar( 'limitstart', 0 ) ); 
			
			$database->setQuery( "SELECT count(*) from #__docmanpaypalorders where completed = 1");
			$total = $database->loadResult();
			
			$limit = $limit ? $limit : 15;
			if ( $total <= $limit ) {
			    $limitstart = 0;
			} 
			
			//require_once( $GLOBALS['mosConfig_absolute_path'] . '/includes/pageNavigation.php' );
			jimport('joomla.html.pagination');
			$pageNav = new JPagination( $total, $limitstart, $limit );
			
			//$pageNav = new mosPageNav( $total, $limitstart, $limit );
			//$link = "index.php?option=com_docmanpaypal&Itemid=$Itemid";
			$link = "index.php?option=com_docmanpaypal";
			
			$database->setQuery("SELECT o. * , sum( downloads ) AS downloads
FROM `#__docmanpaypalorders` o
JOIN #__docmanpaypaldownloads ON ( o.order_id = #__docmanpaypaldownloads.order_id )
WHERE completed =1
GROUP BY order_id order by order_id desc",$limitstart, $limit);
			//die($database->getQuery());
			$result = $database->loadAssocList();
			foreach ($result as $row) {
				$currency_symbol = !empty($currency_symbols[$row['mc_currency']]) ? $currency_symbols[$row['mc_currency']] : $row['mc_currency'];				
				echo "<tr>
				<td>$row[order_id]</td>
				<td>$row[item_name] (". $dm->formatTransactionLink($row['transaction']) . ")</td>
				<td>$row[merchant]</td>
				<td>$row[first_name] $row[last_name]</td>
				<td>$row[address], $row[city],<br />$row[zip], $row[country]</td>
				<td>$row[datetime]</td>
				<td>$row[email]</td>
				<td>$currency_symbol " . number_format($row['price'],2) . "</td>
				<td>$row[downloads]</td>
				</tr>";
			}
			?>
			  <tfoot>
			    <tr>
			      <td colspan="9"><?php echo $pageNav->getListFooter(); ?></td>
			    </tr>
			  </tfoot>
			</tbody>
		</table>

		<input type="hidden" name="option" id="option" value="com_docmanpaypal" />
		<input type="hidden" name="task" id="task" value="mySales" />
		</form>
		<?php
		} else {
			//jimport( 'joomla.application.component.controller' );
			$mainframe->redirect('/index.php',JText::_("COM_DOCMANPAYPAL_DMP_YOUNEEDTOLOGIN"));
		}
		break;
	case "coupons":
		if ($v15) {
			JToolBarHelper::title(JText::_("DOCman PayPal IPN " . $dm->getVersion() . " Coupons Codes"),'docmanPayPalLogo');
		}
		break;
	case "mi":
		if ($v15) {
			JToolBarHelper::title(JText::_("DOCman PayPal IPN " . $dm->getVersion() . " Micro Integrations"),'docmanPayPalLogo');
		}
		break;
	case "sms":
		if ($v15) {
			JToolBarHelper::title(JText::_("DOCman PayPal IPN " . $dm->getVersion() . " SMS Notification"),'docmanPayPalLogo');
		}
		break;
	case "generateSale":
		if ($v15) {
			JToolBarHelper::title(JText::_("DOCman PayPal IPN " . $dm->getVersion() . " Generate sale for a user"),'docmanPayPalLogo');
		}
		?>
<form action="index.php" method="post" name="adminForm" id="adminForm" class="adminForm">
<table width="100%" border="0" cellspacing="1" id="adminlist" class="adminlist table table-bordered">
<thead>
<tr>
<th width="50%" align="center"><?php echo JText::_("COM_DOCMANPAYPAL_DMP_USER"); ?></th>
<th width="50%" align="center"><?php echo JText::_("COM_DOCMANPAYPAL_DMP_FILE"); ?></th>
</tr>
</thead>
<tr>
<td class="row0"><select name="user_id"><?php
$database->setQuery("select id, concat(username, ' (', name, ')') as username from #__users");
$result = $database->loadAssocList();
foreach ($result as $row) {
	echo '<option value="' . $row['id'] . '">' . $row['username'] . '</option>';
}
?></select></td>
<td class="row0"><select name="dm_id"><?php
$database->setQuery("SELECT dm.docman_document_id as id, dm.title as name, dm.storage_path as filename, cat.title as category, dmp.price as price,  dmp.downloadslimit as downloadslimit, dmp.saleslimit as saleslimit FROM `#__docman_documents` dm, `#__docman_categories` cat, `#__docmanpaypal` dmp where dm.docman_category_id = cat.docman_category_id and dmp.id = dm.docman_document_id");

$result = $database->loadAssocList();
foreach ($result as $row) {
	echo '<option value="' . $row['id'] . '">' . $row['name'] . ' (' . $row['filename'] . ')</option>';
}
?></select></td>
</tr>
</table>
<input type="hidden" name="option" value="com_docmanpaypal" />
<input type="hidden" name="task" value="generateSale_process" />
<input type="submit" value="<?php echo JText::_("COM_DOCMANPAYPAL_DMP_ADDORDER"); ?>" class="btn btn-primary" />
</form>
		<?php
		break;
	case "generateSale_process":
		if ($v15) {
			JToolBarHelper::title(JText::_("DOCman PayPal IPN " . $dm->getVersion() . " Generating Sale for a user..."),'docmanPayPalLogo');
		}
		$user_id = JRequest::getVar('user_id');
		$dm_id = JRequest::getVar('dm_id');
		$database->setQuery("select created_by as dmsubmitedby, title as dmname from #__docman_documents where docman_document_id = '$dm_id' limit 1");
		$result = $database->loadAssocList();
		extract($result[0]);
		$key = md5(rand(1,100000) . date('Y-m-d h:i:s'));
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
`key`)
VALUES (
NULL , '$dmsubmitedby', '$user_id','$dm_id', '$dmname', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '', 'N/A', '" . date("Y-m-d H:i:s") . "', 'N/A', '1','$key');";
	$database->setQuery($sql);
	//echo $database->getQuery();
	$database->query();
	$database->setQuery("insert into #__docmanpaypaldownloads values (" . $database->insertid() . ", $dm_id, 0);");
	//echo $database->getQuery();
	$database->query();
	echo "<h1>Operation complete!</h1>";
		break;
	case "help";
		JToolBarHelper::title(JText::_("DOCman PayPal IPN " . $dm->getVersion() . " Help"),'docmanPayPalLogo');
	
		?>
<iframe width="100%" height="800" src="http://motov.net/docmanpaypal/help.php" border="0" style="border: 0px solid #ffffff;" />
		<?php
		break;
	case 'stats':
	error_reporting(0);
		JToolBarHelper::title(JText::_("DOCman PayPal IPN " . $dm->getVersion() . " Statistics"),'docmanPayPalLogo');
		$document->addScript('http://www.google.com/jsapi');
		$database->setQuery("SELECT date( `datetime` ) as `date`, mc_currency as currency, item_name, price
FROM `#__docmanpaypalorders`
WHERE price > 0 and completed = 1");
		$result = $database->loadObjectList();
		foreach ($result as $row) {
			@$chartArrayData[date('Y, n, j',strtotime($row->date))]['price'] += $row->price;
			@$chartArrayData[date('Y, n, j',strtotime($row->date))]['sales']++;
			@$chartArrayData[date('Y, n, j',strtotime($row->date))]['currency'] = $row->currency;
		}
		foreach ($chartArrayData as $k => $v) {
			$chartArray[] = "[new Date($k), $v[price], '$v[price] $v[currency]', '$v[sales] Sales']";
		}
		$document->addScriptDeclaration("      google.load('visualization', '1', {'packages':['annotatedtimeline']});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('date', 'Date');
        data.addColumn('number', 'Income:');
        data.addColumn('string', 'title1');
        data.addColumn('string', 'text1');
        data.addRows([
          " . implode(',
          ',$chartArray) . "
        ]);

        var chart = new google.visualization.AnnotatedTimeLine(document.getElementById('chart_div'));
        chart.draw(data, {displayAnnotations: true, displayExactValues: true, thickness: 2});
      }
		");
		echo '<h3>' . JText::_("COM_DOCMANPAYPAL_DMP_DAILYINCOMESTATS") . ':</h3>';
		?>
		    <div id='chart_div' style='width: 1000px; height: 440px;'></div>
		<?php 
		echo '<h3>' . JText::_("COM_DOCMANPAYPAL_DMP_TOPCOUNTRIES") . ':</h3>';
		$database->setQuery("select distinct(country) as c, (select count(country) from #__docmanpaypalorders where country = c) as sales from #__docmanpaypalorders where country != 'N/A' order by sales desc limit 20");
		$result = $database->loadObjectList();
		$imgUrl = 'http://chart.apis.google.com/chart?cht=p3&chs=750x300&chd=t:%%sales%%&chl=%%countries%%&chco=%%colors%%';
		foreach ($result as $obj) {
			$sales[] = $obj->sales;
			$countries[] = "($obj->sales) " . $obj->c;
			$colors[] = docmanpaypal::random_color();
		}

		$imgUrl = str_replace('%%sales%%',implode(',',$sales),$imgUrl);
		$imgUrl = str_replace('%%countries%%',implode('|',$countries),$imgUrl);
		$imgUrl = str_replace('%%colors%%',implode('|',$colors),$imgUrl);
		echo '<img src="' . $imgUrl . '" />';
		//top cities stats
		unset($colors,$sales);
		echo '<h3>' . JText::_("COM_DOCMANPAYPAL_DMP_TOPCITIES") . ':</h3>';
		$database->setQuery("select distinct(city) as c, (select count(city) from #__docmanpaypalorders where city = c) as sales from #__docmanpaypalorders where city != 'N/A' order by sales desc limit 20");
		$result = $database->loadObjectList();
		$imgUrl = 'http://chart.apis.google.com/chart?cht=p3&chs=750x300&chd=t:%%sales%%&chl=%%cities%%&chco=%%colors%%';
		foreach ($result as $obj) {
			$sales[] = $obj->sales;
			$cities[] = "($obj->sales) " . $obj->c;
			$colors[] = docmanpaypal::random_color();
		}
		$imgUrl = str_replace('%%sales%%',implode(',',$sales),$imgUrl);
		$imgUrl = str_replace('%%cities%%',implode('|',$cities),$imgUrl);
		$imgUrl = str_replace('%%colors%%',implode('|',$colors),$imgUrl);
		echo '<img src="' . $imgUrl . '" />';
		//top states stat
		unset($colors,$sales);
		echo '<h3>' . JText::_("COM_DOCMANPAYPAL_DMP_TOPSTATES") . ':</h3>';
		$database->setQuery("select distinct(state) as c, (select count(state) from #__docmanpaypalorders where state = c) as sales from #__docmanpaypalorders where state != 'N/A' and state != '' order by sales desc limit 20");
		$result = $database->loadObjectList();
		$imgUrl = 'http://chart.apis.google.com/chart?cht=p3&chs=750x300&chd=t:%%sales%%&chl=%%states%%&chco=%%colors%%';
		foreach ($result as $obj) {
			$sales[] = $obj->sales;
			$states[] = "($obj->sales) " . $obj->c;
			$colors[] = docmanpaypal::random_color();
		}
		$imgUrl = str_replace('%%sales%%',implode(',',$sales),$imgUrl);
		$imgUrl = str_replace('%%states%%',implode('|',$states),$imgUrl);
		$imgUrl = str_replace('%%colors%%',implode('|',$colors),$imgUrl);
		echo '<img src="' . $imgUrl . '" />';
		//start stat by product
		unset($colors,$sales);
		echo '<h3>' . JText::_("COM_DOCMANPAYPAL_DMP_TOPPRODUCTS") . ':</h3>';
		$database->setQuery("SELECT o.file_id, (
SELECT count( order_id )
FROM #__docmanpaypalorders
WHERE file_id = o.file_id
) AS sales, item_name
FROM #__docmanpaypalorders o
GROUP BY o.file_id");
		$result = $database->loadObjectList();
		$imgUrl = 'http://chart.apis.google.com/chart?cht=p3&chs=750x300&chd=t:%%sales%%&chl=%%item_names%%&chco=%%colors%%';
		foreach ($result as $obj) {
			$sales[] = $obj->sales;
			$item_names[] = "($obj->sales) " . $obj->item_name;
			$colors[] = docmanpaypal::random_color();
		}
		$imgUrl = str_replace('%%sales%%',implode(',',$sales),$imgUrl);
		$imgUrl = str_replace('%%item_names%%',implode('|',$item_names),$imgUrl);
		$imgUrl = str_replace('%%colors%%',implode('|',$colors),$imgUrl);
		echo '<img src="' . $imgUrl . '" />';
		//start stat by started against completed orders
		unset($colors,$sales);
		echo '<h3>' . JText::_("COM_DOCMANPAYPAL_DMP_STARTEDORDERS") . ' / ' . JText::_("COM_DOCMANPAYPAL_DMP_COMPLETEDORDERS") . ':</h3>';
		$database->setQuery("SELECT count(order_id) from #__docmanpaypalorders where 1");
		$totalOrders = $database->loadResult();
		$database->setQuery("SELECT count(order_id) from #__docmanpaypalorders where completed = 1");
		$completedOrders = $database->loadResult();
		$imgUrl = 'http://chart.apis.google.com/chart?cht=p3&chs=750x300&chd=t:' . $totalOrders . ',' . $completedOrders . '&chl=' . "($totalOrders) " . JText::_("COM_DOCMANPAYPAL_DMP_STARTEDORDERS") . '|' . "($completedOrders) " . JText::_("COM_DOCMANPAYPAL_DMP_COMPLETEDORDERS");
		echo '<img src="' . $imgUrl . '" />';
		break;
	case "dmpsfh":
		?>
<link rel="stylesheet" href="components/com_docmanpaypal/css/redmond/jquery-ui-1.8.4.custom.css" type="text/css" media="all" />
<script src="components/com_docmanpaypal/js/jquery-1.4.2.min.js" type="text/javascript"></script>
<script src="components/com_docmanpaypal/js/jquery-ui-1.8.4.custom.min.js" type="text/javascript"></script>
<link rel="stylesheet" href="templates/system/css/system.css" type="text/css" />
<link href="templates/khepri/css/template.css" rel="stylesheet" type="text/css" />

		<h3><?php echo JText::_("COM_DOCMANPAYPAL_DMP_SELECTDOCUMENT"); ?>:</h3>
		<script>var editor = '<?php echo JRequest::getWord('e_name'); ?>';</script>
		<script>
		function dmpsfh_ok(item) {
			var tag = '<a href="index.php?option=com_docman&amp;task=doc_download&amp;gid=' + item.id + '&amp;Itemid=<?php $docmanItemId; ?>">' + item.value + '</a>';
	        window.parent.jInsertEditorText(tag, editor);
	        window.parent.document.getElementById('sbox-window').close();
		}
		</script>
		<?php
		?>
	<meta charset="UTF-8" />
	<style type="text/css">
	.ui-autocomplete-loading { background: white url('images/ui-anim_basic_16x16.gif') right center no-repeat; }
	</style>
	<script type="text/javascript">
	$(function() {
		function log(message) {
			$("<div/>").text(message).prependTo("#log");
			$("#log").attr("scrollTop", 0);
		}
		
		$("#documents").autocomplete({
			source: "<?php echo JURI::base() . 'index.php?&option=com_docmanpaypal&task=dmpsfhsearch&format=raw'; ?>",
			minLength: 2,
			select: function(event, ui) {
				//log(ui.item ? ("Selected: " + ui.item.value + " aka " + ui.item.id) : "Nothing selected, input was " + this.value);
				dmpsfh_ok(ui.item);
			}
		});
	});
	</script>


<div class="ui-widget">
	<label for="documents">Documents: </label>
	<input id="documents" />
</div>
<p><?php echo JText::_("COM_DOCMANPAYPAL_DMP_USEAJAXSEARCH"); ?></p>

<?php
		break;
case "dmpsfhsearch":
	$term = JRequest::getVar('term');
	$database->setQuery("select * from #__docman where dmname like '%$term%'");
	$result = $database->loadObjectList();
	foreach ($result as $doc) {
		$theArray[] = array('id' => $doc->id, 'label' => $doc->dmname, 'value' => $doc->dmname);
	}
	echo json_encode($theArray);
	break;
case "vendors":
	include_once('tasks/vendors.php');
	break;
case "editVendors":
	include_once('tasks/editVendors.php');
	break;
case "saveVendor":
	include_once('tasks/saveVendor.php');
	break;
case "deleteVendors":
	include_once('tasks/deleteVendors.php');
	break;
}
?>