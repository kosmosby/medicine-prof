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

$cssclass		=	trim( $this->_model->cssclass );
if ( $cssclass ) {
	$cssclass	=	' ' .$cssclass;
}
?>
		<div class="cbregPlanStatus cbregPlanStatus_default<?php echo $cssclass?>">
			<div class='captionCell cbregName'><?php echo $this->_icon; ?>&nbsp;&nbsp;<span><?php echo $this->_model->get( 'name' ); ?></span></div>
			<div class='fieldCell cbregParagraph'>
				<div class='cbregDescription'><?php echo $this->_model->get( 'description' ); ?></div>
<?php
		if ( $this->_insertBeforeValidity ) {	?> 
				<div class='cbregAfterSubDescription'>
					<?php echo $this->_insertBeforeValidity; ?> 
				</div><?php
		}
?>
				<div class="cbregValExp">
					<span class='cbregValidity'><?php echo $this->_model->get( 'validity' ); ?></span><?php
		if ( $this->_model->get( 'stateText' ) ) { ?> 
					<span class='cbregExpiring'><?php echo $this->_model->get( 'stateText' ); ?></span><?php
		}	?> 
				</div><?php
		if ( $this->_insertAfterDescription ) { ?> 
				<div class='cbregSubPlanSelector cbclearboth'><?php echo $this->_insertAfterDescription; ?></div><?php
		}	?> 
			</div>
		</div>
