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

$lists = $this->lists;
$u = $this->u;
$user = $this->user;
$pane = $this->pane;
?>
<form action="index.php" method="post" name="adminForm">
    <table width="100%" >
        <tr>
            <td valign="top" style="width: 50%;">
                <fieldset class="adminform">
                    <legend>
                        <img src="<?php echo JURI::root(); ?>administrator/images/month.png" style='height:25px; vertical-align:middle;' />
                        <?php echo JText::_('COM_BIDS_DETAILS'); ?>
                    </legend>
                    <table class="paramlist admintable">
                        <tr>
                            <td class="paramlist_key" width="20%"><?php echo JText::_('COM_BIDS_USERNAME'); ?></td>
                            <td><?php echo $u->username; ?></td>
                        </tr>
                        <tr>
                            <td class="paramlist_key"><?php echo JText::_('COM_BIDS_NAME'); ?></td>
                            <td><?php echo $user->name; ?></td>
                        </tr>
                        <tr>
                            <td class="paramlist_key"><?php echo JText::_('COM_BIDS_SURNAME'); ?></td>
                            <td><?php echo $user->surname; ?></td>
                        </tr>
                        <tr>
                            <td class="paramlist_key"><?php echo JText::_('COM_BIDS_ADDRESS'); ?></td>
                            <td><?php echo isset($user->address) ? $user->address : ''; ?></td>
                        </tr>
                        <tr>
                            <td class="paramlist_key"><?php echo JText::_('COM_BIDS_EMAIL'); ?></td>
                            <td><a href="mailto:<?php echo $u->email; ?>"><?php echo $u->email; ?></a></td>
                        </tr>
                    </table>
                </fieldset>
                <fieldset class="adminform">
                    <legend>
                        <img src="<?php echo JURI::root(); ?>administrator/images/month.png" style='height:25px; vertical-align:middle;' />
<?php echo JText::_('COM_BIDS_STATISTIC'); ?>
                    </legend>
                    <table class="adminform">
                        <tr>
                            <td valign="top" colspan="2">
<?php echo JText::_('COM_BIDS_OFFERS'); ?>: <?php echo $lists['nr_auctions']; ?><br/>
<?php echo JText::_('COM_BIDS_WON_BIDS'); ?>: <?php echo $lists['nr_won_bids']; ?><br/>
<?php echo JText::_('COM_BIDS_NR_BIDS'); ?>: <?php echo $lists['nr_bids']; ?><br/>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo JText::_('COM_BIDS_LAST_BID_DATE'); ?></td>
                            <td><?php if ($lists['last_bid_placed'])
    echo $lists['last_bid_placed']; else
    echo '-'; ?></td>
                        </tr>
                        <tr>
                            <td><?php echo JText::_('COM_BIDS_LAST_AUCTION_DATE'); ?></td>
                            <td><?php if ($lists['last_auction_placed'])
    echo $lists['last_auction_placed']; else
    echo "-"; ?></td>
                        </tr>
                    </table>
                </fieldset>
            </td>
            <td valign="top">
                        <?php
                        echo $pane->startPane("ads-pane");
                        echo $pane->startPanel(JText::_('COM_BIDS_DETAILS'), "detail-page");
                        ?>
                <fieldset class="adminform">
                    <legend>
                        <img src="<?php echo JURI::root(); ?>components/<?php echo APP_EXTENSION;?>/image/payments.png" style='height:30px; vertical-align:middle;' />
                        <?php echo JText::_('COM_BIDS_PAYMENT_BALANCE'); ?>
                    </legend>
                    <table class="adminlist" width="30%" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td><?php echo JText::_('COM_BIDS_MY_BALANCE'); ?>: <?php echo $lists['balance']->balance.'&nbsp;'.$lists['balance']->currency; ?></td>
                        </tr>
                    </table>
                </fieldset>
                        <?php
                        echo $pane->endPanel();
                        echo $pane->startPanel(JText::_('COM_BIDS_RATING'), "detail-page");
                        ?>
                <fieldset class="adminform">
                    <legend>
                        <img src="<?php echo JURI::root(); ?>administrator/images/task_f2.png" style='height:25px; vertical-align:middle;' />
                        <?php echo JText::_('COM_BIDS_RATING'); ?>
                    </legend>
                    <table class="adminlist" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <th width="40%" class="title"><?php echo JText::_('COM_BIDS_USERNAME'); ?></th>
                            <th width="40%" align="left"><?php echo JText::_('COM_BIDS_AUCTION'); ?></th>
                            <th width="20%" align="left"><?php echo JText::_('COM_BIDS_RATE_TITLE'); ?></th>
                        </tr>
                        <?php
                        if (count($lists["ratings"]) == 0) {
                            ?>
                            <tr>
                                <td colpsan="3"><?php echo JText::_('COM_BIDS_NO_RATINGS'); ?></td>
                            </tr>
    <?php
} else {
    foreach ($lists['ratings'] as $k => $rating) {

        $link_user = 'index.php?option=com_bids&task=detailuser&hidemainmenu=0&id=' . $rating->voter_id;
        $link_auction = 'index.php?option=com_bids&task=editoffer&hidemainmenu=0&id=' . $rating->auction_id;
        ?>
                                <tr>
                                    <td><a href="<?php echo $link_user; ?>"><?php echo $rating->username; ?></a></td>
                                    <td><a href="<?php echo $link_auction; ?>"><?php echo $rating->auction; ?></a></td>
                                    <td><?php echo $rating->rating; ?></td>
                                </tr>
                    <?php }
                } ?>
                    </table>
                </fieldset>
                        <?php
                        echo $pane->endPanel();
                        echo $pane->startPanel(JText::_('COM_BIDS_MESSAGES'), "detail-page");
                        ?>
                <fieldset class="adminform">
                    <legend>
                        <img src="<?php echo JURI::root(); ?>administrator/images/task_f2.png" style='height:25px; vertical-align:middle;' />
<?php echo JText::_('COM_BIDS_MESSAGES'); ?>
                    </legend>
                    <table class="adminlist" style="border: 1px dashed silver; padding: 5px; margin-bottom: 10px;">
                        <tr>
                            <th align="center">#</th>
                            <th><?php echo JText::_('COM_BIDS_FROM'); ?></th>
                            <th><?php echo JText::_('COM_BIDS_TO'); ?></th>
                            <th><?php echo JText::_('COM_BIDS_MESSAGE'); ?></th>
                            <th><?php echo JText::_('COM_BIDS_DATE'); ?></th>
                        </tr>
<?php
if (isset($lists['messages']) && count($lists['messages']))
    foreach ($lists['messages'] as $k => $m) {
        ?>
                                <tr>
                                    <td style="text-align: center;">
                                        <a href="index.php?option=com_bids&task=messages.toggle&cid=<?php echo $m->id; ?>">
                                            <img src="<?php echo JURI::root(); ?>components/com_bids/images/<?php echo ($m->published == 1) ? 'apply_f2.png' : 'publish_f2.png'; ?>" style="width:12px;" border="0" />
                                        </a>
                                        <a href="#" onclick="if(confirm('Are you sure you want to remove coment?')=='1') location.href='index.php?option=com_bids&task=messages.delete&cid=<?php echo $m->id; ?>';">
                                            <img src="<?php echo JURI::root(); ?>components/com_bids/images/cancel_f2.png" style="width:12px;" border="0" />
                                        </a>
                                    </td>
                                    <td><?php echo $m->fromuser; ?></td>
                                    <td><?php echo $m->touser; ?></td>
                                    <td><span class="editlinktip hasTip" title="<?php echo $m->message; ?>"><?php echo substr($m->message, 0, 20); ?></span></td>
                                    <td><?php echo $m->modified; ?></td>
                                </tr>
                            <?php } ?>
                    </table>
                </fieldset>
<?php
echo $pane->endPanel();
echo $pane->startPanel(JTEXT::_('COM_BIDS_BIDS_LIST'), "detail-page");
?>
                <table class="adminlist" width="100%" style="border: 1px dashed silver; padding: 5px; margin-bottom: 10px;">
                    <tr>
                        <th><?php echo JText::_('COM_BIDS_AUCTION') ?></th>
                        <th><?php echo JText::_('COM_BIDS_PRICE') ?></th>
                        <th><?php echo JText::_('COM_BIDS_INFO') ?></th>
                        <th><?php echo JText::_('COM_BIDS_DATE') ?></th>
                        <th><?php echo JText::_('COM_BIDS_ACCEPTED') ?></th>
                    </tr>
<?php
if (isset($lists["bids"]) && count($lists["bids"]))
foreach ($lists["bids"] as $k => $m) {
    ?>
                            <tr>
                                <td><?php echo $m->title; ?></td>
                                <td><?php echo $m->bid_price . " " . $m->currency; ?></td>
                                <td>
                                    <?php echo JText::_('COM_BIDS_PROXY') ?>:<strong><?php if ($m->id_proxy)
        echo "Yes"; else
        echo "No"; ?></strong><br />
                                    <?php echo JText::_('COM_BIDS_QUANTITY') ?>:<strong><?php echo $m->quantity; ?></strong><br />
                                    <?php echo JText::_('COM_BIDS_BIN') ?>:<strong><?php if ((float)$m->BIN_price == (float)$m->bid_price)
                echo "Yes"; else
                echo "No"; ?></strong>
                                </td>
                                <td><?php echo $m->modified; ?></td>
                                <td align="center"><?php if ($m->accept) { ?><img src="<?php echo JURI::root(); ?>administrator/images/tick.png" /><?php } ?></td>
                            </tr>
<?php } ?>
                </table>
<?php
echo $pane->endPanel();
echo $pane->endPane();
?>
            </td>
        </tr>
    </table>
    <input type="hidden" name="option" value="com_bids">
    <input type="hidden" name="task" value="detailuser">
    <input type="hidden" name="id" value="<?php echo $user->id; ?>">
</form>
