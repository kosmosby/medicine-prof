<?php
/**
* @version $Id: $
* @package CBSubs (TM) Community Builder Plugin for Paid Subscriptions (TM)
* @subpackage Template for Paid Subscriptions
* @copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/
/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

$tmplVersion	=	1;	// This is the template version that needs to match
?>

<form method="post" class="cbregUpgradePlanForm" action="<?php echo $this->htmlspecialcharedBaseUrl; ?>">
	<div class="contentheading" id="cbregUpgradePossibilities">
		<?php echo $this->htmlTitle; ?>
	</div>
	<?php echo $this->htmlUpgrades; ?>
	<div class="cbregUpgradeButtonDiv">
		<span class="cb_button_wrapper"><input type="submit" class="button" name="<?php echo htmlspecialchars( $this->buttonName ); ?>" value="<?php echo htmlspecialchars( $this->buttonText ); ?>" /></span>
	</div>
	<?php echo $this->hiddenFlds; ?>
</form>
