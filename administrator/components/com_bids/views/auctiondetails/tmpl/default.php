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

$auction = $this->auction;
$bids = $this->bids;
$lists = $this->lists;
$adminMessages = $this->adminMessages;
$photos = $this->photos;

$custom_fields_html = $this->custom_fields_html;
$custom_fields_with_cat = $this->custom_fields_with_cat;

$user = $this->user;

$required_image = JHtml::image(JUri::root() . 'components/com_bids/images/requiredfield.gif', 'required');

$bidCfg = BidsHelperTools::getConfig();

$editor = JFactory::getEditor();
if('none'!= $editor->get('_editor')->get('_name')) {
    $fixEditorInTab =
        <<<JS
                window.addEvent('domready', function(){
        $('description').style.visibility="hidden";
        $('description').style.height=0;
    });
JS;

    $document = JFactory::getDocument();
    $document->addScriptDeclaration($fixEditorInTab);
}

?>

<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">

    <input type="hidden" name="option" value="com_bids" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="id" value="<?php echo $auction->id; ?>" />
    <input type="hidden" name="featured" value="" />
    <input type="hidden" name="has_custom_fields_with_cat" value="<?php echo $custom_fields_with_cat; ?>" />

    <table width="100%">
        <tr>
            <td width="50%" valign="top">
    <?php
        echo JHtml::_('tabs.start','auction');
        echo JHtml::_('tabs.panel', JText::_('COM_BIDS_AUCTION_DETAILS'), 'auction1');
    ?>
    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_AUCTION_REF_NR'); ?></div>
        <div class="auction_edit_field_input"><?php echo $auction->auction_nr ? $auction->auction_nr : '-'; ?></div>
        <div style="clear: both;"></div>
    </div>
    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_CATEGORY'); ?></div>
        <div class="auction_edit_field_input"><?php echo $lists['cats']; ?></div>
        <div style="clear: both;"></div>
    </div>
    <div class="auction_edit_field_container">
                    <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_TITLE'); ?></div>
                    <div class="auction_edit_field_input"><?php echo $lists['title'] ?><?php echo $required_image; ?></div>
                    <div style="clear: both;"></div>
                </div>
    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_USERNAME'); ?></div>
        <div class="auction_edit_field_input">
            <a href="index.php?option=com_bids&task=detailuser&id=<?php echo $auction->userid; ?>">
            <?php echo $user->username ?>
            </a>
        </div>
        <div style="clear: both;"></div>
    </div>
    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_PUBLISHED'); ?>:</div>
        <div class="auction_edit_field_input"><?php echo $lists['published'] ?></div>
        <div style="clear: both;"></div>
    </div>

    <?php
        $hide_prices = ($auction->auction_type == AUCTION_TYPE_BIN_ONLY ? 'style="display:none;"' : '');
        $hide_suggest = ($auction->auction_type == AUCTION_TYPE_BIN_ONLY ? '' : 'style="display:none;"');
        $hide_bin = ($auction->BIN_price <= 0 ? 'style="display:none;"' : '');
    ?>

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
    <?php if ($bidCfg->bid_opt_global_enable_bin || $bidCfg->bid_opt_enable_bin_only): ?>
    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_BIN_OPTION'); ?>:</div>
        <div class="auction_edit_field_input"><?php echo $lists['binType'] ?></div>
        <div style="clear: both;"></div>
    </div>
    <?php endif; ?>

    <div class="auction_edit_subsection">
        <div><?php echo JText::_('COM_BIDS_PUBLISHING_SETTINGS'); ?></div>
    </div>

    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_CURRENT_LOCAL_TIME'); ?>
            :
        </div>
        <div class="auction_edit_field_input"><?php echo $lists['currentLocalTime_field'] ?></div>
        <div style="clear: both;"></div>
    </div>

    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_START_DATE'); ?>:</div>
        <div class="auction_edit_field_input"><?php echo $lists['startDate_field']; ?></div>
        <div style="clear: both;"></div>
    </div>

    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_END_DATE'); ?>:</div>
        <div class="auction_edit_field_input"><?php echo $lists['endDate_field']; ?></div>
        <div style="clear: both;"></div>
    </div>

    <?php if ($custom_fields_html): ?>
    <div class="auction_edit_subsection">
        <div><?php echo JText::_('COM_BIDS_OTHER_SETTINGS'); ?></div>
    </div>
    <div>
        <?php echo $custom_fields_html; ?>
    </div>
        <?php endif; ?>

    <?php
        echo JHtml::_('tabs.panel', JText::_('COM_BIDS_PRICE_SHIPMENT'), 'auction2');
    ?>

    <div class="auction_edit_subsection">
        <div><?php echo JText::_('COM_BIDS_PRICE_SETTINGS'); ?></div>
    </div>
    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_TAB_CURRENCY'); ?>:</div>
        <div class="auction_edit_field_input"><?php echo $lists['currency'] . $required_image; ?></div>
        <div style="clear: both;"></div>
    </div>
    <div class="auction_edit_field_container" id="initial_price_row" <?php echo $hide_prices; ?>>
        <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_INITIAL_PRICE'); ?>:</div>
        <div class="auction_edit_field_input"><?php echo $lists['initialPrice'] . $required_image; ?></div>
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
    <div class="auction_edit_field_container" id="min_increase_select_row" <?php echo $hide_prices; ?>>
        <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_OPT_MIN_INCREASE_TITLE'); ?>:</div>
        <div class="auction_edit_field_input"><?php echo $lists['minIncrease'] ?></div>
        <div style="clear: both;"></div>
    </div>
    <?php endif ?>

    <?php if ($bidCfg->bid_opt_global_enable_bin || $bidCfg->bid_opt_enable_bin_only): ?>
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
    <?php if ($bidCfg->bin_opt_price_suggestion): ?>
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

    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_MAX_BID'); ?></div>
        <div class="auction_edit_field_input"><?php echo $auction->highestBid ? (BidsHelperAuction::formatPrice
        ($auction->highestBid->bid_price) . ' ' .
                $auction->currency) : '-'; ?></div>
        <div style="clear: both;"></div>
    </div>
    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label"></div>
        <div class="auction_edit_field_input"></div>
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

    <div class="auction_edit_subsection">
        <div><?php echo JText::_('COM_BIDS_PAYMENT_SETTINGS'); ?></div>
    </div>
    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_ADDITIONAL_PAYMENT_INFO'); ?>:</div>
        <div class="auction_edit_field_input"><?php echo $lists['paymentInfo'] ?></div>
        <div style="clear: both;"></div>
    </div>

    <?php
    echo JHtml::_('tabs.panel', JText::_('COM_BIDS_DESCRIPTION'), 'auction3');
    ?>
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
    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_TAGS'); ?></div>
        <div class="auction_edit_field_input"><?php echo $lists['tags'] ?></div>
        <div style="clear: both;"></div>
    </div>
    <table class="paramlist admintable">
        <tr>
            <td colspan="99">
                <div class="auction_edit_field_container">
                    <div class="auction_edit_field_label"><?php echo JText::_('COM_BIDS_AUCTION_IMAGES'); ?>:
                    </div>
                    <div class="auction_edit_field_input"><?php echo $lists['uploadImages'] ?></div>
                    <div style="clear: both;"></div>
                </div>
            </td>
        </tr>
    </table>
    <?php echo JHtml::_('tabs.end'); ?>
</td>
<td valign="top">
                <?php echo JHtml::_('tabs.start','publishing'); ?>
                <?php echo JHtml::_('tabs.panel',JText::_('COM_BIDS_PUBLISHING_INFO'), 'publishing' ); ?>

                <table class="adminform">
                    <tr class="row1">
                        <td>
    <?php echo JText::_('COM_BIDS_CLOSED_BY_ADMIN'); ?></td>
                        <td>
                            <table>
                                <tr>
                                    <td width="100">
    <?php echo ($auction->id && $auction->close_by_admin == 1) ? JText::_('COM_BIDS_YES') : JText::_('COM_BIDS_NO'); ?>
                                    </td>
                                    <td>
                                        <?php if ($auction->close_by_admin) { ?>
                                            <input type="button" class="button" onclick="Joomla.submitbutton('opened')"
                                                   value="<?php echo JText::_('COM_BIDS_REOPEN_BY_ADMIN'); ?>">
                                        <?php } else { ?>
                                            <input type="button" class="button" onclick="Joomla.submitbutton('closed')" value="<?php echo JText::_('COM_BIDS_CLOSE_BY_ADMIN'); ?>">
                                        <?php } ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="row0">
    <?php echo $lists['featured']; ?>
                        </td>
                        <td>
                            <table>
                                <tr>
                                    <td width="100">
    <?php echo ucfirst($auction->featured); ?>
                                    </td>
                                    <td>
                                        <input type="button" class="button" onclick="document.adminForm.featured.value=document.getElementById('featured').value;Joomla.submitbutton('set_featured')" value="<?php echo JText::_('COM_BIDS_PAYMENT_SET_FEATURED'); ?>"></td>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td  class="row1">
                            Hits
                        </td>
                        <td>
                            <table>
                                <tr>
                                    <td width="100">
    <?php echo intval($auction->hits); ?>
                                    </td>
                                    <td>
                                        <input type="button" class="button" onclick="Joomla.submitbutton('resethits')" value="Reset Hits"></td>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>
        <?php if ($auction->id) : ?>
        <?php echo JHtml::_('tabs.panel', JText::_('COM_BIDS_LIST'), 'bidslist'); ?>
            <fieldset>
                <legend><?php echo JText::_('COM_BIDS_LIST'); ?></legend>
                <table class="adminlist" width="100%"
                       style="border: 1px dashed silver; padding: 5px; margin-bottom: 10px;">
                    <tr>
                        <th><?php echo JText::_('COM_BIDS_FROM'); ?></th>
                        <th><?php echo JText::_('COM_BIDS_INITIAL_PRICE'); ?></th>
                        <th><?php echo JText::_('COM_BIDS_PRICE'); ?></th>
                        <th><?php echo JText::_('COM_BIDS_DATE'); ?></th>
                    </tr>
                    <?php
                    foreach ($bids as $k => $bid) {
                        ?>
                        <tr class="row0">
                            <td><?php echo $bid->username; ?></td>
                            <td align="center"><?php echo number_format($bid->initial_bid, 2); ?></td>
                            <td align="center"><?php echo number_format($bid->bid_price, 2); ?>
                                <?php if ($bid->id_proxy > 0 && $bid->max_proxy_price > 0) { ?>Proxy:
                                    <strong><?php echo $bid->max_proxy_price; ?></strong> <?php } ?>
                            </td>
                            <td><?php echo $bid->modified; ?></td>
                        </tr>
                        <tr class="row1">
                            <td colspan="4">
                                <?php echo JText::_('COM_BIDS_QUANTIY') ?>
                                :<strong><?php echo $bid->quantity; ?></strong><br/>
                            </td>
                        </tr>
                        <?php } ?>
                </table>
            </fieldset>
        <?php endif; ?>
        <?php if ($auction->id && $this->cfg->bid_opt_allow_messages) : ?>
            <?php echo JHtml::_('tabs.panel', JText::_('COM_BIDS_MESSAGES'), 'userdetails'); ?>

            <table class="adminlist" width="100%"
                   style="border: 1px dashed silver; padding: 5px; margin-bottom: 10px;">
                <tr>
                    <th align="center">#</th>
                    <th><?php echo JText::_('COM_BIDS_FROM'); ?></th>
                    <th><?php echo JText::_('COM_BIDS_TO'); ?></th>
                    <th><?php echo JText::_('COM_BIDS_MESSAGE'); ?></th>
                    <th><?php echo JText::_('COM_BIDS_DATE'); ?></th>
                </tr>
                <?php
                foreach ($adminMessages as $k => $m) {
                    ?>
                    <tr>
                        <td align="center">
                            <a href="index.php?option=com_bids&task=messages.toggle&cid=<?php echo $m->id; ?>">
                                <img src="<?php echo JURI::root(); ?>components/com_bids/images/<?php echo $m->published ? 'apply_f2.png' : 'publish_f2.png'; ?>"
                                     style="width:12px;" border="0"/>
                            </a>
                            <a href="#"
                               onclick="if(confirm('Are you sure you want to remove this comment?')=='1') location.href='index.php?option=com_bids&task=messages.delete&cid=<?php echo $m->id; ?>';"><img
                                    src="<?php echo JURI::root(); ?>components/com_bids/images/cancel_f2.png"
                                    style="width:12px;" border="0"/></a>
                        </td>
                        <td><?php echo $m->fromuser; ?></td>
                        <td><?php echo $m->touser; ?></td>
                        <td><span class="editlinktip hasTip"
                                  title="<?php echo $m->message, ".."; ?>"><?php echo substr($m->message, 0, 20); ?></span>
                        </td>
                        <td><?php echo $m->modified; ?></td>
                    </tr>
                    <?php } ?>
                <tr>
                    <td colspan="5" align="center">
                        <?php echo JHTML::link('index.php?option=com_bids&task=write_admin_message&tmpl=component&auction_id=' . $auction->id, JText::_('COM_BIDS_SEND_MESSAGE'), 'class="modal" rel="{handler: \'url\', size: {x: 700, y: 500}}"'); ?>
                    </td>
                </tr>
            </table>
        <?php endif; ?>
        <?php echo JHtml::_('tabs.panel', JText::_('COM_BIDS_USER_DETAILS'), 'userdetails'); ?>
            <table width="100%" style="border: 1px dashed silver; padding: 5px; margin-bottom: 10px;">
                <tr>
                    <th colspan="2"><?php echo JText::_('COM_BIDS_USER_DETAILS'); ?></th>
                </tr>
                <tr>
                    <td><?php echo JText::_('COM_BIDS_USERNAME'); ?></td>
                    <td><?php echo $auction->userdetails->username; ?></td>
                </tr>
                <tr>
                    <td><?php echo JText::_('COM_BIDS_NAME'); ?></td>
                    <td><?php echo $auction->userdetails->name, ' ', @$auction->userdetails->surname; ?></td>
                </tr>
                <?php if (isset($auction->userdetails->phone)) { ?>
                <tr>
                    <td><?php echo JText::_('COM_BIDS_USER_PHONE'); ?></td>
                    <td><?php echo $auction->userdetails->phone; ?></td>
                </tr>
                <?php } ?>
                <?php if (isset($auction->userdetails->email)) { ?>
                <tr>
                    <td><?php echo JText::_('COM_BIDS_USER_EMAIL'); ?></td>
                    <td><?php echo $auction->userdetails->email; ?></td>
                </tr>
                <?php } ?>
                <?php if (isset($auction->userdetails->address)) { ?>
                <tr>
                    <td><?php echo JText::_('COM_BIDS_USER_ADDRESS'); ?></td>
                    <td><?php echo $auction->userdetails->address; ?></td>
                </tr>
                <?php } ?>
                <tr>
                    <td><?php echo JText::_('COM_BIDS_USER_CITY'); ?></td>
                    <td><?php echo @$auction->userdetails->city; ?></td>
                </tr>
                <tr>
                    <td><?php echo JText::_('COM_BIDS_USER_COUNTRY'); ?></td>
                    <td><?php echo @$auction->userdetails->country; ?></td>
                </tr>
                <?php
                if (isset($lists["user_fields"]))
                    foreach ($lists["user_fields"] as $li => $field) {
                        ?>
                        <tr>
                            <td><?php echo $field['name']; ?></td>
                            <td><?php echo $field['value']; ?></td>
                        </tr>
                        <?php } ?>
            </table>
    <?php echo JHtml::_('tabs.end'); ?>

    <?php
    echo JHtml::_('sliders.start','details');
    ?>

<?php if ($this->cfg->bid_opt_allow_messages) { ?>

    <table class="adminlist" width="100%" style="border: 1px dashed silver; padding: 5px; margin-bottom: 10px;">
        <tr>
            <th align="center">#</th>
            <th><?php echo JText::_('COM_BIDS_FROM'); ?></th>
            <th><?php echo JText::_('COM_BIDS_TO'); ?></th>
            <th><?php echo JText::_('COM_BIDS_MESSAGE'); ?></th>
            <th><?php echo JText::_('COM_BIDS_DATE'); ?></th>
        </tr>
        <?php
        foreach ($adminMessages as $k => $m) {
            ?>
                            <tr>
                                <td align="center">
                                    <a href="index.php?option=com_bids&task=messages.toggle&cid=<?php echo $m->id; ?>">
                                        <img src="<?php echo JURI::root(); ?>components/com_bids/images/<?php echo $m->published ? 'apply_f2.png' : 'publish_f2.png'; ?>" style="width:12px;" border="0" />
                                    </a>
                                    <a href="#" onclick="if(confirm('Are you sure you want to remove this comment?')=='1') location.href='index.php?option=com_bids&task=messages.delete&cid=<?php echo $m->id; ?>';"><img src="<?php echo JURI::root(); ?>components/com_bids/images/cancel_f2.png" style="width:12px;" border="0" /></a>
                                </td>
                                <td><?php echo $m->fromuser; ?></td>
                                <td><?php echo $m->touser; ?></td>
                                <td><span class="editlinktip hasTip" title="<?php echo $m->message, ".."; ?>"><?php echo substr($m->message, 0, 20); ?></span></td>
                                <td><?php echo $m->modified; ?></td>
                            </tr>
        <?php } ?>
                        <tr>
                            <td colspan="5" align="center">
                                <?php echo JHTML::link('index.php?option=com_bids&task=write_admin_message&tmpl=component&auction_id='.$auction->id, JText::_('COM_BIDS_SEND_MESSAGE'), 'class="modal" rel="{handler: \'url\', size: {x: 700, y: 500}}"' ); ?>

                            </td>
                        </tr>
                    </table>

    <?php }
                echo JHtml::_('sliders.end');
                ?>
            </td>
        </tr>
    </table>
</form>
