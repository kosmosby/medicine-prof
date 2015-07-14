<?php
/**
* @version $Id: coupon.php 1465 2012-07-10 17:37:13Z beat $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Template for Paid Subscriptions
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/
/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

$tmplVersion	=	1;	// This is the template version that needs to match
?>

<div class="cbregCoupon cbclearboth">
<?php if ( $this->couponsUsed && $this->couponRemoveButtonText ) {
	// Invisible first button as default to be posted when pressing enter (otherwise it sends first delete coupon button): For IE, it's not display:hidden style
?>
	<button type="submit" class="button" name="addcouponcode" value="1" style="position:absolute;left:-9999px;top:-9999px;height:0;width:0;">Add</button>
	<div class="cbregCouponsInputed">
		<ul class="cbregListOfCouponsInputed">
<?php 	foreach ( $this->couponsUsed as $coupon ) { ?>
			<li>
				<span class="cbregCouponCode"><?php echo $coupon->coupon_code; ?></span><span class="cbregCouponCodeSAeparator">:</span>
				<span class="cbregCouponName"><?php echo $coupon->name; ?></span>
				<span class="cb_button_wrapper_small cpay_button_couponcode_wrapper cbregCouponDelete"><button type="submit" class="button" name="deletecouponcode[<?php echo $coupon->id;?>]" value="1"><?php echo CBPTXT::Th( $this->couponRemoveButtonText ); ?></button></span>
			</li>
<?php	} ?>
		</ul>
	</div>
<?php } ?>
	<div class="cbregCouponInput">
		<label for="cbsubscouponcode"><span><?php echo CBPTXT::Th( $this->couponLabelText ); ?></span></label>
		<input name="couponcode" id="cbsubscouponcode" type="text" />
		<span class="cb_button_wrapper_small cpay_button_couponcode_wrapper"><button type="submit" class="button" name="addcouponcode" value="1"><?php echo CBPTXT::Th( $this->couponAddButtonText ); ?></button></span>
		<span class="cbsubscouponInstructions"><?php echo CBPTXT::Th( $this->couponDescription ); ?></span>
		<?php if ( count( $this->couponDescriptionHints ) > 0 ) { ?>
		<div class="cbsubscouponsHints">
			<?php foreach ( $this->couponDescriptionHints as $htmlDescription ) { ?>
			<div class="cbsubsCouponsHint">
				<?php echo $htmlDescription; ?>
			</div>
			<?php } ?>
		</div>
		<?php } ?>
	</div>
</div>
