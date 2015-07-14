<?php
$lang = JFactory::getLanguage();
$lang->load('com_bids');

JHTML::stylesheet("modules/mod_bids/tmpl/mod_bids.css");
JHTML::script('modules/mod_bids/assets/countdown.js');
JHTML::script('components/com_bids/js/jquery/jquery.js');
JHTML::script('components/com_bids/js/jquery/jquery.jcarousel.js');
JHTML::stylesheet('components/com_bids/js/jquery/jquery.jcarousel.css');

JHTML::_('behavior.tooltip');
JHTML::addIncludePath(JPATH_ROOT . DS . 'components' . DS . 'com_bids' . DS . 'helpers' . DS . 'html');

JFactory::getLanguage()->load('com_bids');
jimport('joomla.html.parameter');

if (!count($rows))
{
    return;
}

$jdoc = JFactory::getDocument();
$js = "
        var days='" . JText::_('COM_BIDS_DAYS') . ",';
        var expired='" . JText::_('COM_BIDS_EXPIRED') . "';

	window.addEvent('domready', function() {
		if ((typeof moduleSetTimeLeft =='function'))
			moduleSetTimeLeft('modulebidstime{$mid}_'," . count($rows) . ");
	});

	jQuery(document).ready(function() {
        jQuery('#mod_bids_carousel_".$module->id."').jcarousel({
            scroll: 1,
            visible: 1
        });
    });
";
$jdoc->addScriptDeclaration($js);

$Itemid = BidsHelperTools::getMenuItemId(array("task" => "listauctions"), 1);
?>

<ul id="mod_bids_carousel_<?php echo $module->id; ?>" class="jcarousel-skin-tango">

    <?php
    $i = 1;
    foreach ($rows as $row) {

        $overlib_str = $row->title . '::';
        $overlib_str .= JText::_("By") . " " . $row->by_user . "<br />";

        if ($row->auction_type == 1) {
            $overlib_str .= JText::_("Start Bid") . ": " . BidsHelperAuction::formatPrice($row->initial_price) . " " . $row->currency;
        }

        if ($i % 2 == 0) {
            $alt_class = "row_1";
        } else {
            $alt_class = "row_2";
        }

        $link_to_auction = JHtml::_('auctiondetails.auctionDetailsURL', $row, false);

        if ($display_image) {

            if (file_exists(AUCTION_PICTURES_PATH . DS . $row->picture) && $row->picture) {
                $image = AUCTION_PICTURES . $row->picture;
            } else {
                $image = AUCTION_PICTURES . "no_image.png";
            }

        }
        if ($display_counter) {
            BidsHelperAuction::renderAuctionTiming($row, true);
        }

        $row->params = new JParameter($row->params);
        switch ($row->auction_type) {
            case AUCTION_TYPE_PUBLIC:
                if ($row->params->get('max_price')) {
                    if (is_null($row->maxBid)) {
                        $max_bid = JText::_("No bids");
                    } else {
                        $max_bid = BidsHelperAuction::formatPrice($row->maxBid) . '&nbsp;' . $row->currency;
                    }
                } else {
                    $max_bid = JText::_("Private");
                }
                break;
            case AUCTION_TYPE_PRIVATE:
                $max_bid = JText::_("Private");
                break;
            case AUCTION_TYPE_BIN_ONLY:
                $max_bid = $row->BIN_price . '&nbsp;' . $row->currency;
                break;
        }
    ?>

        <li style="text-align: center;">
            <table class="mod_bids_table">
                <tr>
                    <td style="vertical-align: top; padding-top: 8px;">
                        <?php if ($display_image) { ?>
                        <div width="<?php echo $image_width + 10;?>" height="<?php echo $image_height + 10;?>"
                             align="center">
                            <a href="<?php echo $link_to_auction; ?>"><img src="<?php echo $image;?>" border="0"
                                                                           width="<?php echo $image_width;?>"
                                                                           height="<?php echo $image_height;?>" alt=""/></a>
                        </div>
                        <?php } ?>
                        <div style="padding-top: 5px;">
                            <a href="<?php echo $link_to_auction;?>" class="mod_bids_link hasTip"
                               title="<?php echo htmlentities($overlib_str, ENT_QUOTES, 'UTF-8');?>"><?php echo $row->title;?></a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: bottom;">
                        <div class="hasTip modBidsPrice" title="<?php echo JText::_('COM_BIDS_HIGHEST_BID'); ?>">
                            <?php echo $max_bid; ?>
                        </div>
                        <?php if ($display_counter) { ?>
                        <div style="padding-bottom: 8px;">
                            <div id="modulebidstime<?php echo $mid;?>_<?php echo $i;?>">
                                <?php echo $row->countdown; ?>
                            </div>
                        </div>
                        <?php
                    }
                        ?>
                    </td>
                </tr>
            </table>
        </li>
    <?php
        $i++;
    }
    ?>
</ul>