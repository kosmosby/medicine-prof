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



// no direct access
defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.mootools');
JHTML::script(JURI::root().'modules/mod_bidrate/assets/js/bid_rate.js');
?>

<div class="bidsRatingsModule">

    <table style="width: 100%;">

<?php

    echo "<div style='padding-bottom: 5px;'>".JText::_(count($rows) ? "Click to rate the following auctions" : "No more auctions to rate")."</div>";


	foreach($rows as $row )
	{
        $alreadyDisplayed = array();
        foreach($row->toRateUserids as $k=>$userRated) {
            if(in_array($userRated,$row->ratedUsers) || in_array($userRated,$alreadyDisplayed)) {
                continue;
            }

            $alreadyDisplayed[] = $userRated;

            if (file_exists(AUCTION_PICTURES_PATH . DS . $row->picture) && $row->picture) {
                $image = AUCTION_PICTURES . $row->picture;
            } else {
                $image = AUCTION_PICTURES . "no_image.png";
            }

            $linkUser = JHtml::link( JRoute::_('index.php?option=com_bids&task=userdetails&id='. $row->toRateUserids[$k]), $row->toRateUsernames[$k] );
	    ?>

                <tr style="border: 0; border-bottom: 1px solid #ccc;">
                    <td>
                        <a href="<?php echo JHtml::_('auctiondetails.auctionDetailsURL', $row);?>">
                            <img src="<?php echo $image;?>" border="0" alt="" style="width: 30px;"/>
                        </a>
                    </td>
                    <td>
                        <div>
                            <?php echo $linkUser; ?>
                            (<?php echo $row->isMyAuction ? JText::_("Buyer") : JText::_("Seller"); ?>)

                        </div>
                        <div>
                            <?php echo JHTML::link(JHtml::_('auctiondetails.auctionDetailsURL', $row), $row->title); ?>
                        </div>
                        <div>
                            <form action="<?php echo JUri::base();?>index.php" method="post"
                                  name="auctionRateForm<?php echo $row->id . '-' . $userRated;?>">
                                <input type="hidden" name="option" value="com_bids"/>
                                <input type="hidden" name="task" value="rate"/>
                                <input type="hidden" name="id" value="<?php echo $row->id;?>"/>
                                <input type="hidden" name="user_rated_id" value="<?php echo $userRated; ?>"/>
                                <input type="hidden" name="rate" value="0"/>
                                <?php for ($i = 0; $i < 10; $i++) { ?>
                                <img src="<?php echo JUri::base();?>/components/com_bids/images/f_rateit_0.png"
                                     class="rate_star<?php echo $row->id . '-' . $userRated;?>" height="12"
                                     rate="<?php echo $i;?>"
                                     onclick="dorate_mod('<?php echo $i;?>','<?php echo $row->id . '-' . $userRated; ?>');"
                                     onmouseover="showrate_mod('<?php echo $i;?>','<?php echo $row->id . '-' . $userRated; ?>');"
                                     onmouseout="showrate_mod('<?php echo $i;?>','<?php echo $row->id . '-' . $userRated; ?>');"
                                     alt="star"/>
                                <?php } ?>
                            </form>
                        </div>
                    </td>
                </tr>

            <?php
        }
	}  
?>
    </table>
</div>