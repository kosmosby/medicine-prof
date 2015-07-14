<?php
$lang = JFactory::getLanguage();
$lang->load( 'com_bids' );

JHTML::stylesheet("mod_bids.css","modules/mod_bids/tmpl/");
JHTML::script('modules/mod_bids/assets/countdown.js');

JHTML::_('behavior.tooltip');
JHTML::addIncludePath(JPATH_ROOT.DS.'components'.DS.'com_bids'.DS.'helpers'.DS.'html');

JFactory::getLanguage()->load('com_bids');
jimport('joomla.html.parameter');

$jdoc = JFactory::getDocument();
$js = "
        var days='".JText::_('COM_BIDS_DAYS').",';
        var expired='".JText::_('COM_BIDS_EXPIRED')."';

	window.addEvent('domready', function() {
		if ((typeof moduleSetTimeLeft =='function'))
			moduleSetTimeLeft('modulebidstime{$mid}_',".count($rows).");
	});
";
$jdoc->addScriptDeclaration( $js );

$Itemid = BidsHelperTools::getMenuItemId( array("task" => "listauctions") , 1 );
?>

<table width="100%" class="mod_bids_table_vertical" cellpadding="0" cellspacing="0" border="0">
	<?php
        $i=1;
	foreach($rows as $row) {

		$overlib_str = $row->title.'::';
		$overlib_str .= JText::_("By")." ".$row->by_user."<br />";

		if($row->auction_type == 1){
			$overlib_str .= JText::_("Start Bid").": ".BidsHelperAuction::formatPrice($row->initial_price)." ".$row->currency;
		}

		if( $i%2 == 0 ){
			$alt_class="row_1";
		}else {
			$alt_class="row_2";
		}

 		$link_to_auction = JHtml::_('auctiondetails.auctionDetailsURL',$row,false);

 		if ($display_image) {

			if (file_exists(AUCTION_PICTURES_PATH.DS.$row->picture)&& $row->picture){
				$image = AUCTION_PICTURES.$row->picture;
			} else {
				$image = AUCTION_PICTURES."no_image.png";
			}

 		}
 		if ($display_counter) {
            BidsHelperAuction::renderAuctionTiming($row,true);
        }
?>

	<tr class="<?php echo $alt_class;?>" onmouseover="this.className='<?php echo $alt_class;?>_hover'" onmouseout="this.className='<?php echo $alt_class;?>'">
	  <?php if ($display_image) { ?>
	      <td width="<?php echo $image_width+10;?>" height="<?php echo $image_height+10;?>" align="center">
              <a href="<?php echo $link_to_auction; ?>"><img src="<?php echo $image;?>" border="0" width="<?php echo $image_width;?>" height="<?php echo $image_height;?>" alt="" /></a>
	      </td>
	  <?php } ?>
 		 <td>
 		 	<?php if (!$display_image) echo $i,'. ';?><a href="<?php echo $link_to_auction;?>" class="mod_bids_link hasTip" title="<?php echo htmlentities($overlib_str,ENT_QUOTES,'UTF-8');?>"><?php echo $row->title;?></a>
 		 </td>
 		 <?php if ($display_counter) { ?>
 		 <td>
			<span id="modulebidstime<?php echo $mid;?>_<?php echo $i;?>" >
                        <?php echo $row->countdown; ?>
                        </span>
 		 </td>
 		 <?php
 		 }
 		 ?>
 		 <td>
 		 	<?php
            $row->params = new JParameter($row->params);
 		 	if( $row->auction_type != AUCTION_TYPE_PRIVATE && $row->params->get('max_price') ){
 		 		if(is_null($row->maxBid)) {
 		 			$max_bid=JText::_("No bids");
 		 		} else {
 		 			$max_bid = BidsHelperAuction::formatPrice($row->maxBid)."&nbsp;".$row->currency;
 		 		}
 		 	}else {
 		 		$max_bid = JText::_("Private");
 		 	}
 		 	echo '<span class="hasTip" title="'. JText::_('COM_BIDS_HIGHEST_BID') . '">'. $max_bid . '</span>';;
 		 	?>
 		 </td>
 		</tr>
 		<?php
                $i++;
	}
?>
</table>