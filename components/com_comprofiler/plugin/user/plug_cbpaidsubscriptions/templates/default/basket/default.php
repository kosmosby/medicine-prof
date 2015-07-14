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

<div class="cbregBasket cbclearboth">
	<table>
<?php
		if ( $this->captionText ) {
?>
		<caption><?php echo CBPTXT::Th( $this->captionText ); ?></caption>
<?php
		}
?>
		<thead>
			<tr>
<?php
		foreach ( $this->allColumns as $variable => $header ) {
?>
			    <th scope="col" class="cbregBaIt<?php echo $variable; ?>">
					<?php echo CBPTXT::Th( $header ); ?>
				</th>
<?php
		}
?>
			</tr>
		</thead>
		<tfoot>
<?php
		foreach ( $this->totalizerLinesCols as $key => $line ) {
?>
			<tr class="cbregBasketTotalizerLine cbregBaItFLine<?php echo htmlspecialchars( $line['totalizer_type'] ); ?>">
<?php
			foreach ( $line as $column => $htmlCell ) {
				if ( $column === 'totalizer_type' ) {
					continue;
				}
				$lineFormat		=	$this->footerFormats[$column];
?>
				<td class="cbregBaItFCol<?php echo $lineFormat[0]; ?>" title="<?php echo htmlspecialchars( $lineFormat[1] ); ?>"<?php if ( $lineFormat[2] > 1 ) echo ' colspan="'. (int) $lineFormat[2] . '"'; ?>>
					<?php echo $htmlCell; ?>
				</td>
<?php
			}
?>
			</tr>
<?php
		}
?>
		</tfoot>
		<tbody>
<?php
		foreach ( $this->itemsLinesCols as $key => $line ) {
?>
			<tr class="cbregBasketItemLine<?php echo ( $line['plan_cssclass'] ? ' ' . htmlspecialchars( $line['plan_cssclass'] ) : '' ); ?>">
<?php
			foreach ( $line as $column => $htmlCell ) {
				if ( $column === 'plan_cssclass' ) {
					continue;
				}
				$lineFormat		=	$this->columnsFormats[$column];
?>
			    <td class="cbregBasketItemLine cbregBaIt<?php echo $lineFormat[0]; ?>" title="<?php echo htmlspecialchars( $lineFormat[1] ); ?>"<?php if ( $lineFormat[2] > 1 ) echo ' colspan="'. (int) $lineFormat[2] . '"'; ?>>
					<?php echo $htmlCell ?>
				</td>
<?php
			}
?>
			</tr>
<?php
		}
?>
		</tbody>
	</table>
</div>
