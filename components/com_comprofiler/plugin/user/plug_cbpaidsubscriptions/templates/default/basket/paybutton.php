<?php
/**
* @version $Id: paybutton.php 1465 2012-07-10 17:37:13Z beat $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Template for Paid Subscriptions
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/
/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

$tmplVersion	=	1;	// This is the template version that needs to match
?>

<div class="cbpaidCCbutton cbpaidButton_<?php echo htmlspecialchars( $this->payNameForCssClass ); ?>" id="<?php echo htmlspecialchars( $this->butId ); ?>">
	<form action="<?php echo htmlspecialchars( $this->formTargetUrl ); ?>" method="post">
<?php
	if ( preg_match( "/(\\.jpg|\\.png|\\.gif)\$/i", $this->buttonImageOrText ) ) {
?>
		<input type="image" src="<?php echo htmlspecialchars( $this->buttonImageOrText ); ?>" class="cbpaidCCimageInput" name="BPay" alt="<?php echo htmlspecialchars( $this->altText ); ?>" title="<?php echo htmlspecialchars( $this->titleText ); ?>" />
<?php
	} else {
?>
		<input type="button" class="button cbpaidjsSubmit cbpaidCCbuttonInput" name="BPay" value="<?php echo htmlspecialchars( $this->buttonImageOrText ); ?>" alt="<?php echo htmlspecialchars( $this->altText ); ?>" title="<?php echo htmlspecialchars( $this->titleText ); ?>" />
		
<?php
	}
		echo $this->txtHiddenInputs;
?>
	</form>
</div>
