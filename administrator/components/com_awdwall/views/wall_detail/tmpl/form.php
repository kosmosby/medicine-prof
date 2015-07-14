<?php 
/**
 * @version 3.0
 * @package JomWALL -CB
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/
defined('_JEXEC') or die('Restricted access'); 

	$r = JURI::base();
	$root = str_replace('administrator/','',$r);
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="col100">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'Details' ); ?></legend>

	<table class="admintable">
		<tr>
			<td width="100" align="right" class="key">
				<label for="greeting">
					<?php echo JText::_( 'Owner Username' ); ?>:
				</label>
			</td>
			<td>
				<?php echo $this->items->wall_name;?>
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="greeting">
					<?php echo JText::_( 'Image Name' ); ?>:
				</label>
			</td>
			<td>
				<input class="text_area" type="text" name="title" id="title" size="32" maxlength="250" value="<?php echo $this->items->wall_name;?>" />
			</td>
		</tr>
		<!--user_id 	 	buy_date 	sell_date 	des 	lead_img 	add_date 	last_edit_date 	hits 	cid 	published-->
		<tr>
			<td width="100" align="right" valign="top" class="key">
				<label for="greeting">
					<?php echo JText::_( 'Image' ); ?>:
				</label>
			</td>
			<td>
				<img src="<?php echo $root;?>components/com_awdwall/Contest_Images/thumb_img/small_<?php echo $this->items->lead_img; ?>" />
			</td>
		</tr>
		
		<tr>
			<td width="100" align="right" valign="top" class="key">
				<label for="greeting">
					<?php echo JText::_( 'Edit Image' ); ?>:
				</label>
			</td>
			<td>
				<!--<input type="file"  accept="text/plain"  name="lead_img" id="lead_img" size="50"/>-->
			</td>
		</tr>
		<!--
		<tr>
			<td width="100" align="right" class="key">
				<label for="greeting">
					<?php echo JText::_( 'Parent wall' ); ?>:
				</label>
			</td>
			<?php
				// Get data from the walls table
				$db =& JFactory::getDBO();
				$query = 'SELECT id,title FROM #__awdwall_wall WHERE published=1 AND parent=0';
				$db->setQuery( $query );
				$result = $db->loadObjectList();			
			?>
			<td>
				<select name="parent" id="parent" class="inputbox" size="1" >
					<option value="0" >- Top Level -</option>	
				<?php
				for ($i=0, $n=count( $result ); $i < $n; $i++){
					if($this->items->parent == $result[$i]->id){
						echo '<option value="'.$result[$i]->id.'" selected="selected" >'.$result[$i]->title.'</option>';
					}
					else {
						echo '<option value="'.$result[$i]->id.'" >'.$result[$i]->title.'</option>';
					}
				}
				?>				
				</select>
			</td>
		</tr>-->
	</table>
	</fieldset>
</div>
<div class="clr"></div>
<input type="hidden" name="wall_name" id="wall_name" value="<?php echo $this->items->wall_name;?>" />
<input type="hidden" name="option" value="com_awdwall" />
<input type="hidden" name="id" value="<?php echo $this->items->id; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="wall_detail" />
</form>
