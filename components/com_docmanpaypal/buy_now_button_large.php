<?php

$doc = JFactory::getDocument();
JHtml::_('jquery.framework');

$doc->addStylesheet(JURI::base() . 'components/com_docmanpaypal/css/style.css');
$doc->addScript(JURI::base() . 'components/com_docmanpaypal/js/script.js');

$lang = JFactory::getLanguage();
$extension = 'com_docmanpaypal';
$lang->load($extension, JPATH_SITE . '/components/com_docmanpaypal');

include_once(JPATH_SITE . '/administrator/components/com_docmanpaypal/docmanpaypal.class.php');

$dm = new docmanpaypal();
$item = $dm->getItem($document->id);
$price = $item->price;

if ($price > 0 && $dm->canDownload($document->id,'',false) == false) {
	//$showAddToCart = true;
	$showBuyNow = true;
	$currencySymbol = $dm->currencies[$dm->cfg['currency']]['ASCII'];
	if ($dm->cfg['pdfPreviewPages'] > 0 && pathinfo($item->storage_path, PATHINFO_EXTENSION) == 'pdf') {
		$showPreviewButton = true;
	}
}

$show_download = true;
$priceFormat = money_format($price, 2);
if ($showBuyNow) {
	$show_download = false;
}
if ($showBuyNow) {
	
?>
<div class="docman_download<?php if ($document->description != '') echo " docman_download--right"; ?>">
        <a class="btn btn-large btn-primary docman-download docman-btn-download btn-block" style="width:100%;" data-title="<?php echo $document->title; ?>" data-id="<?php echo $document->id; ?>" href="<?php echo JRoute::_('index.php?option=com_docmanpaypal&task=submit_order&mode=single&id=' . $document->id . '&Itemid=' . (int)JRequest::getVar('Itemid')); ?>">
	        	<i class="icon-paypal-white"></i> 
	        	<?php echo JText::_('BUY_NOW') . " ($currencySymbol $priceFormat)"; ?>	        </a>

        
    </div>

	  <?php
		  }
if ($showAddToCart) {
?>
<div class="btn-group docman-btn-group-add-to-cart" style="width:100%; margin-left:0;">
	        <a class="btn btn-large btn-primary docman-download docman-btn-download btn-block add-to-cart" style="width:100%;" data-title="<?php echo $document->title; ?>" data-id="<?php echo $document->id; ?>" href="javascript:void(0);">
	        	<i class="icon-shopping-cart-white	"></i> 
	        	<?php echo JText::_('ADD_TO_CART');?>	        </a>
	    </div>

<?php	
}		  
if ($showPreviewButton) {
?>
<div class="btn-group docman-btn-group-preview" style="width:100%; margin-left:0;">
	        <a class="btn btn-large btn-primary docman-download docman-btn-download btn-block" data-title="<?php echo $document->title; ?>" data-id="<?php echo $document->id; ?>" href="<?php echo JRoute::_('index.php?option=com_docmanpaypal&task=pdfPreview&format=raw&id=' . $document->id . '&Itemid=' . (int)JRequest::getVar('Itemid')); ?>">
	        
	        	<i class="icon-download"></i> 
	        	<?php echo JText::_('COM_DOCMANPAYPAL_PREVIEW');?>	        </a>
	    </div>
<?php	
}