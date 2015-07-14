<?php
/**
* Joomla Community Builder User Plugin: plug_cbhelloworld
* @version $Id$
* @package plug_helloworld
* @subpackage helloworld.php
* @author Nant, JoomlaJoe and Beat
* @copyright (C) Nant, JoomlaJoe and Beat, www.joomlapolis.com
* @license Limited  http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @final 1.0
*/

/** ensure this file is being included by a parent file */
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }


/**
 * Basic tab extender. Any plugin that needs to display a tab in the user profile
 * needs to have such a class. Also, currently, even plugins that do not display tabs (e.g., auto-welcome plugin)
 * need to have such a class if they are to access plugin parameters (see $this->params statement).
 */
class getauctioncustomformTab extends cbTabHandler {
	/**
	 * Construnctor
	 */
	function getauctioncustomTab() {
		$this->cbTabHandler();
	}
	
	/**
	* Generates the HTML to display the user profile tab
	* @param object tab reflecting the tab database entry
	* @param object mosUser reflecting the user being displayed
	* @param int 1 for front-end, 2 for back-end
	* @returns mixed : either string HTML for tab content, or false if ErrorMSG generated
	*/
	function getDisplayTab($tab,$user,$ui) {
		$return = '
<div id="submit-bid-panel" style="float:right;color:white;padding-right:30px;display:none;">
        Стоимость: <strong id="max_bid_show" style="font-size:18px;">-</strong>
		    <br/>
        Ваша цена (USD):
		    <input id="bid-amount" class="inputbox" style="color:black;padding:0" type="text" value="" size="3" alt="bid">

            <input type="submit" name="send" value="Купить" style="padding:2px"
               onclick="sendBid(jQuery(\'#bid-amount\').val());return false;" class="btn btn-success"/>
</div>
<script>
jQuery(function(){
    if(jQuery(\'#bidform\').length  && jQuery("#consultation_active").val()=="1"){
            if(jQuery(\'#max_bid\').val() != \'\'){
              jQuery(\'#max_bid_show\').html(\'$\'+jQuery(\'#max_bid\').val());
            }
            jQuery(\'#submit-bid-panel\').show();
        }

});
</script>
		';
		return $return;
	}
}
?>
