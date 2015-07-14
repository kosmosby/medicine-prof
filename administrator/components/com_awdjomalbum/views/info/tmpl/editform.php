<?php
/**
 * @version 3.0
 * @package Jomgallery
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/

defined('_JEXEC') or die('Restricted access');
JToolBarHelper::title(JText::_('awdjomalbum'), 'awdwallprogalleryextrafield');
JToolBarHelper::save();
$cid=$_REQUEST['cid'];

$db	=& JFactory::getDBO();
$sql="select *  from   #__awd_jomalbum_info_ques where id='$cid[0]'";
//echo $sql;
$db->setQuery( $sql );
$rows = $db->loadObjectList();
AwdjomalbumHelper::awdadmintoolbar('info');
?>
<div class="awdm">
<form name="adminForm" id="adminForm" action="" method="post">
	<table cellpadding="2" cellspacing="2" width="100%">
		<tr>
			<td valign="top" align="left"><?php echo JText::_( 'Question' );  ?></td>
			
			<td valign="top" align="left">
			<textarea name="value" rows="4" cols="50"><?php echo $rows[0]->value;?></textarea>
			</td>
		</tr>
	</table>
	<input type="hidden" name="option" value="com_awdjomalbum" />
	<input type="hidden" name="view" value="info" />
	<input type="hidden" name="layout" value="editform" />
	<input type="hidden" name="task" value="save" />
	<input type="hidden" name="id" value="<?php echo $rows[0]->id;?>" />
	<input type="hidden" name="boxchecked" value="0" />
</form>
</div>
