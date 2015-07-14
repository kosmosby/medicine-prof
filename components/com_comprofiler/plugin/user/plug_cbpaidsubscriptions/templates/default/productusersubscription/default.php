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
	<div class='cbregPlanSelector cbregPlanSelector_default cbclearboth<?php echo $this->cssclass; ?>' id='<?php echo $this->cssid; ?>'>
		<div class='fieldCell cbregTick'>
			<?php echo $this->_tick; ?>
		</div>
		<div class='fieldCell cbRegNameDesc'>
			<div class='captionCell cbregName<?php echo $this->cssclass; ?>'>
				<?php echo $this->_labelledName; ?>
			</div>
			<div class='fieldCell cbregParagraph<?php echo $this->cssclass; ?>'><?php
		if ( $this->description !== null ) {	?>
				<div class='cbregDescription'>
					<?php echo $this->description; ?>
				</div><?php
		}
		if ( $this->_insertBeforePrice ) {	?>
				<div class='cbregAfterSubDescription'>
					<?php echo $this->_insertBeforePrice; ?>
				</div><?php
		}
		if ( $this->periodPrice !== null ) {	?>
				<div class='cbregFee'>
					<?php echo $this->periodPrice; ?>
				</div><?php
		}
		if ( $this->_insertAfterDescription ) {	?>
				<div class='cbregSubPlanSelector cbclearboth'>
					<?php echo $this->_insertAfterDescription; ?>
				</div><?php
		}	?>
			</div>
		</div>
	</div>
