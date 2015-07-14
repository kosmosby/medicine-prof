<?php
/**
* @version $Id: payradio.php 1465 2012-07-10 17:37:13Z beat $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Template for Paid Subscriptions
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/
/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

$tmplVersion	=	1;	// This is the template version that needs to match

$cssId		=	'paym' . $this->radioValue;

$images		=	array();
foreach ( $this->cardtypes as $cardtype ) {
	if ( $cardtype[0] == '/' ) {
		$url			=	cbpaidApp::getLiveSiteFilePath( substr( $cardtype, 1 ) );
	} elseif ( cbStartOfStringMatch( $cardtype, 'http' ) ) {
		$url			=	$cardtype;
	} else {
		$url			=	$this->getMediaUrl( 'icons/cards/cc_' . $cardtype . '.png' );
		if ( $url == null ) {
			$url		=	cbpaidApp::getLiveSiteFilePath( 'icons/cards/cc_' . $cardtype . '.gif' );
		}
	}
	if ( $url ) {
		$images[$cardtype]	=	$url;
	}
}

$cssClass	=	'cbregCCselInput';
if ( $this->payNameForCssClass ) {
	$cssClass			.=	' ' . $this->payNameForCssClass;
}
?>
	<div class="<?php echo htmlspecialchars( $cssClass ); ?>">
		<input type="radio" class="cbpaidCCpaymethod" name="payment_method" value="<?php echo htmlspecialchars( $this->radioValue ); ?>" id="<?php echo htmlspecialchars( $cssId ); ?>" <?php if ( $this->selected ) { ?>checked="checked"<?php } ?> />
		<label for="<?php echo htmlspecialchars( $cssId ); ?>" title="<?php echo htmlspecialchars( $this->altText ); ?>">
			<?php foreach ( $images as $cardtype => $imgsrc ) { ?>
				<img src="<?php echo htmlspecialchars( $imgsrc ); ?>" alt="<?php echo htmlspecialchars( CBPTXT::T( $cardtype ) ); ?>" class="cbregCCimgSel" />
			<?php } ?>
			<span>
				<?php echo $this->brandLabelHtml; ?>
			</span>
		</label>
	</div>
<?php	if ( $this->brandDescriptionHtml ) { ?>
	<div class="cbregCCselDescription">
		<?php echo $this->brandDescriptionHtml; ?>
	</div>
<?php	}	?>
