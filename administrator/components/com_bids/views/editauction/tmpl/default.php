<?php

defined('_JEXEC') or die('Restricted access.');

$auction = $this->auction;
$lists = $this->lists;
$custom_fields_html = $this->custom_fields_html;
$custom_fields_with_cat = $this->custom_fields_with_cat;
$bidCfg = $this->bidCfg;

$required_image = JHtml::image(JUri::root().'components/com_bids/images/requiredfield.gif','required');

?>

<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">

    <input type="hidden" name="id" value="<?php echo $auction->id; ?>"/>
    <input type="hidden" name="option" value="com_bids" />
    <input type="hidden" name="task" value="saveauction" />
    <input type="hidden" name="has_custom_fields_with_cat" value="<?php echo $custom_fields_with_cat; ?>"/>

    <div class="auction_header">
        <?php echo $lists['editFormTitle'] ?>
    </div>

    <div class="div_spacer"></div>
    <div class="auction_info_legend"><?php echo JText::_('COM_BIDS_REQUIRED_FIELDS_INFO'); ?> <?php echo $required_image; ?></div>

    <div style="width: 100%; height: 35px;">
        <div style="float: left; width: 5px;">
            <img src="<?php echo JUri::root(); ?>components/com_bids/images/header_left.gif" border="0"/>
        </div>
        <div style="float: right; width: 5px;">
            <img src="<?php echo JUri::root(); ?>components/com_bids/images/header_right.gif" border="0"/>
        </div>
        <div class="auctionedit_section">
            <?php echo JText::_('COM_BIDS_TAB_OFFER_DETAILS'); ?>
        </div>
    </div>
    <div class="div_spacer"></div>

    <div>
        <div class="auction_edit_field_container">
            <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_TITLE'); ?>:</div>
            <div class="auction_edit_field_input"><?php echo $lists['title'] ?><?php echo $required_image; ?></div>
            <div style="clear: both;"></div>
        </div>
        <div class="auction_edit_field_container">
            <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_CATEGORY'); ?>:</div>
            <div class="auction_edit_field_input"><?php echo $lists['cats'] ?></div>
            <div style="clear: both;"></div>
        </div>
        <div class="auction_edit_field_container">
            <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_PUBLISHED'); ?>:</div>
            <div class="auction_edit_field_input"><?php echo $lists['published'] ?></div>
            <div style="clear: both;"></div>
        </div>
        <div class="auction_edit_field_container">
            <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_TAGS'); ?>:</div>
            <div class="auction_edit_field_input"><?php echo $lists['tags'] ?></div>
            <div style="clear: both;"></div>
        </div>
        <div class="auction_edit_field_container">
            <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_SHORT_DESCRIPTION'); ?>:</div>
            <div class="auction_edit_field_input"><?php echo $lists['shortDescription'] ?><?php echo $required_image; ?></div>
            <div style="clear: both;"></div>
        </div>
        <div class="auction_edit_field_container">
            <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_DESCRIPTION'); ?>:</div>
            <div class="auction_edit_field_input"><?php echo $lists['description'] ?></div>
            <div style="clear: both;"></div>
        </div>
    </div>

    <?php if($custom_fields_html): ?>
    <div class="auction_edit_subsection">
        <div><?php echo JText::_('COM_BIDS_OTHER_SETTINGS'); ?></div>
    </div>
    <div>
        <?php echo $custom_fields_html; ?>
    </div>
    <?php endif ?>

    <div class="div_spacer"></div>

    <div style="width: 100%; height: 35px;">
        <div style="float: left; width: 5px;">
            <img src="<?php echo JUri::root(); ?>/components/com_bids/images/header_left.gif" border="0"/>
        </div>
        <div style="float: right; width: 5px;">
            <img src="<?php echo JUri::root(); ?>/components/com_bids/images/header_right.gif" border="0"/>
        </div>
        <div class="auctionedit_section">
            <?php echo JText::_('COM_BIDS_TAB_PHOTOS_AND_PICTURES'); ?>
        </div>
    </div>
    <div class="div_spacer"></div>

    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_AUCTION_IMAGES'); ?>:</div>
        <div class="auction_edit_field_input"><?php echo $lists['uploadImages'] ?></div>
        <div style="clear: both;"></div>
    </div>

    <div class="div_spacer"></div>

    <div style="width: 100%; height: 35px;">
        <div style="float: left; width: 5px;">
            <img src="<?php echo JUri::root(); ?>/components/com_bids/images/header_left.gif" border="0"/>
        </div>
        <div style="float: right; width: 5px;">
            <img src="<?php echo JUri::root(); ?>/components/com_bids/images/header_right.gif" border="0"/>
        </div>
        <div class="auctionedit_section">
            <?php echo JText::_('COM_BIDS_TAB_OTHER_SETTINGS'); ?>
        </div>
    </div>
    <div class="div_spacer"></div>

    <?php
        $hide_prices = ($auction->auction_type == AUCTION_TYPE_BIN_ONLY ? 'style="display:none;"' : '');
        $hide_suggest = ($auction->auction_type == AUCTION_TYPE_BIN_ONLY ? '' : 'style="display:none;"');
        $hide_bin = ($auction->BIN_price <= 0 ? 'style="display:none;"' : '');
    ?>

    <div class="auction_edit_subsection">
        <div><?php echo JText::_('COM_BIDS_DATE_TIME_SETTINGS'); ?></div>
    </div>
    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_CURRENT_LOCAL_TIME'); ?>:</div>
        <div class="auction_edit_field_input"><?php echo $lists['currentLocalTime_field'] ?></div>
        <div style="clear: both;"></div>
    </div>
    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_START_DATE'); ?>:</div>
        <div class="auction_edit_field_input"><?php echo $lists['startDate_field'] ?></div>
        <div style="clear: both;"></div>
    </div>
    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_END_DATE'); ?>:</div>
        <div class="auction_edit_field_input"><?php echo $lists['endDate_field'] ?></div>
        <div style="clear: both;"></div>
    </div>

    <div class="auction_edit_subsection">
        <div><?php echo JText::_('COM_BIDS_AUCTION_TYPE_SETTINGS'); ?></div>
    </div>
    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_TYPE_OF_AUCTION'); ?>:</div>
        <div class="auction_edit_field_input"><?php echo $lists['auctiontype'] ?> <?php echo $required_image; ?></div>
        <div style="clear: both;"></div>
    </div>
    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_AUTOMATIC_AUCTION'); ?>:</div>
        <div class="auction_edit_field_input"><?php echo $lists['automatic'] ?></div>
        <div style="clear: both;"></div>
    </div>
    <!-- BIN SETTINGS -->
    <?php if ($bidCfg->bid_opt_global_enable_bin || $bidCfg->bid_opt_enable_bin_only): ?>
    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_BIN_OPTION'); ?>:</div>
        <div class="auction_edit_field_input"><?php echo $lists['binType'] ?></div>
        <div style="clear: both;"></div>
    </div>
    <div class="auction_edit_field_container" id="BIN_price_row" <?php echo $hide_bin; ?>>
    <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_BIN_PRICE'); ?>:</div>
    <div class="auction_edit_field_input"><?php echo $lists['binPrice'] ?></div>
    <div style="clear: both;"></div>
    </div>
    <div class="auction_edit_field_container" id="BIN_price_row2" <?php echo $hide_bin; ?>>
    <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_PARAM_ACCEPT_BIN_TEXT'); ?>:</div>
    <div class="auction_edit_field_input"><?php echo $lists['autoAcceptBIN'] ?></div>
    <div style="clear: both;"></div>
    </div>
    <?php if ($bidCfg->bid_opt_quantity_enabled): ?>
    <div class="auction_edit_field_container" id="bin_only_extra" <?php echo $hide_suggest; ?>>
    <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_BIN_QUANTITY'); ?>:</div>
    <div class="auction_edit_field_input"><?php echo $lists['quantity'] ?></div>
    <div style="clear: both;"></div>
    </div>
    <?php endif ?>
    <?php if($bidCfg->bin_opt_price_suggestion): ?>
    <div class="auction_edit_field_container" id="bid_price_suggest"
    <?php echo $hide_suggest; ?>>
    <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_PRICE_SUGGEST'); ?>:</div>
    <div class="auction_edit_field_input"><?php echo $lists['enableSuggestions'] ?></div>
    <div style="clear: both;"></div>
    </div>
    <div class="auction_edit_field_container" id="bid_price_suggest_min"
    <?php echo $hide_suggest; ?>>
    <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_PRICE_SUGGEST_MIN'); ?>:</div>
    <div class="auction_edit_field_input"><?php echo $lists['minNumberSuggestions'] ?></div>
    <div style="clear: both;"></div>
    </div>
        <?php endif ?>
    <?php endif ?>
    <!-- END BIN -->

    <div class="auction_edit_subsection">
        <div><?php echo JText::_('COM_BIDS_PRICE_SETTINGS'); ?></div>
    </div>
    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_TAB_CURRENY'); ?>:</div>
        <div class="auction_edit_field_input"><?php echo $lists['currency'] ?><?php echo $required_image; ?></div>
        <div style="clear: both;"></div>
    </div>
    <div class="auction_edit_field_container" id="initial_price_row" <?php echo $hide_prices; ?>>
    <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_INITIAL_PRICE'); ?>:</div>
    <div class="auction_edit_field_input"><?php echo $lists['initialPrice'] ?><?php echo $required_image; ?></div>
    <div style="clear: both;"></div>
    </div>
    <div class="auction_edit_field_container" id="reserve_maxprice_row" <?php echo $hide_prices; ?>>
    <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_PARAM_MAX_PRICE_TEXT'); ?>:</div>
    <div class="auction_edit_field_input"><?php echo $lists['showMaxPrice'] ?></div>
    <div style="clear: both;"></div>
    </div>
    <div class="auction_edit_field_container" id="reserve_nrbidder_row" <?php echo $hide_prices; ?>>
    <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_PARAM_COUNTS_TEXT'); ?>:</div>
    <div class="auction_edit_field_input"><?php echo $lists['showNumberBids'] ?></div>
    <div style="clear: both;"></div>
    </div>
    <?php if ($bidCfg->bid_opt_global_enable_reserve_price): ?>
    <div class="auction_edit_field_container" id="reserve_price_row" <?php echo $hide_prices; ?>>
    <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_RESERVE_PRICE'); ?>:</div>
    <div class="auction_edit_field_input"><?php echo $lists['reservePrice'] ?></div>
    <div style="clear: both;"></div>
    </div>
    <div class="auction_edit_field_container" id="reserve_price_row2" <?php echo $hide_prices; ?>>
    <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_PARAM_RESERVE_PRICE_TEXT'); ?>:</div>
    <div class="auction_edit_field_input"><?php echo $lists['showReservePrice'] ?></div>
    <div style="clear: both;"></div>
    </div>
    <?php endif ?>
    <?php if ($bidCfg->bid_opt_min_increase_select): ?>
    <div class="auction_edit_field_container" id="min_increase_select_row"
    <?php echo $hide_prices; ?>>
    <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_OPT_MIN_INCREASE_TITLE'); ?>:</div>
    <div class="auction_edit_field_input"><?php echo $lists['minIncrease'] ?></div>
    <div style="clear: both;"></div>
    </div>
    <?php endif ?>

    <div class="auction_edit_subsection">
        <div><?php echo JText::_('COM_BIDS_PAYMENT_SETTINGS'); ?></div>
    </div>
    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_ADDITIONAL_PAYMENT_INFO'); ?>:</div>
        <div class="auction_edit_field_input"><?php echo $lists['paymentInfo'] ?></div>
        <div style="clear: both;"></div>
    </div>

    <div class="auction_edit_subsection">
        <div><?php echo JText::_('COM_BIDS_SHIPPING_SETTINGS'); ?></div>
    </div>
    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_SHIPMENT_PRICE'); ?>:</div>
        <div class="auction_edit_field_input"><?php echo $lists['shippingPrice'] ?></div>
        <div style="clear: both;"></div>
    </div>
    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_SHIPMENT'); ?>:</div>
        <div class="auction_edit_field_input"><?php echo $lists['shipmentInfo'] ?></div>
        <div style="clear: both;"></div>
    </div>

    <div class="div_spacer"></div>

    <br clear="all"/>
</form>
