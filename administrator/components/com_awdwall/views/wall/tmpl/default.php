<?php
/**
 * @version 3.0
 * @package JomWALL -CB
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

//DEVNOTE: import html tooltips

//Ordering allowed ?
$ordering = ($this->lists['order'] == 'ordering');
Awdwalladminhelper::awdadmintoolbar('wall');
?>
<script language="javascript" type="text/javascript">
/**
* Submit the admin form
* 
* small hack: let task decides where it comes
*/
function submitform(pressbutton){
var form = document.adminForm;
   if (pressbutton)
    {form.task.value=pressbutton;}
    
	if ((pressbutton=='add')||(pressbutton=='edit')||(pressbutton=='publish')||(pressbutton=='unpublish')
	 ||(pressbutton=='remove'))
	 {
	  form.controller.value="wall_detail";
	 }
	try {
		form.onsubmit();
		}
	catch(e){}
	
	form.submit();
}


</script>
<div class="awdm">
<form action="<?php echo $this->request_url; ?>" method="post" name="adminForm" id="adminForm" >
<div id="editcell">
	<table class="table table-striped">
	<thead>
		<tr>
			<th width="20">
				<?php echo JText::_( 'NUM' ); ?>
			</th>
			<th width="20">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->items ); ?>);" />
			</th>
			<th class="nowrap">
				Messages
			</th>
			
			<th class="nowrap">
				Username 
			</th>
		</tr>
	</thead>	
	<?php
	//echo 
	$r = JURI::base();
	$root = str_replace('administrator/','',$r);
	$config 		= &JComponentHelper::getParams('com_awdwall');
	$displayName 	= (int)$config->get('display_name', 1);
	$k = 0;
	//print_r($this->items);
	for ($i=0, $n=count( $this->items ); $i < $n; $i++){
		
		$db =& JFactory::getDBO();
		$row = &$this->items[$i];
		$link 	= JRoute::_( 'index.php?option=com_awdwall&controller=wall_detail&task=edit&cid[]='. $row->id );
		$checked = JHTML::_('grid.id', $i, $row->id);
		
		$message = $row->message;
		if($row->type == 'video'){
			// get video information
			$query = 'SELECT * FROM #__awd_wall_videos WHERE wall_id = ' . (int)$row->id;
			$db->setQuery($query);
			$video = $db->loadObject();
			$message = $video->title . ' (video)';
		}
		
		if($row->type == 'image'){
			// get video information
			$query = 'SELECT * FROM #__awd_wall_images WHERE wall_id = ' . (int)$row->id;
			$db->setQuery($query);
			$image = $db->loadObject();
			$message = $image->name . ' (image)';
		}
		
		if($row->type == 'mp3'){
			// get video information
			$query = 'SELECT * FROM #__awd_wall_mp3s WHERE wall_id = ' . (int)$row->id;
			$db->setQuery($query);
			$mp3 = $db->loadObject();
			$message = $mp3->title . ' (song)';
		}
		
		if($row->type == 'link'){
			// get video information
			$query = 'SELECT * FROM #__awd_wall_links WHERE wall_id = ' . (int)$row->id;
			$db->setQuery($query);
			$link = $db->loadObject();
			$message = $link->title . ' (link)';
		}
		
		if($row->type == 'file'){
			// get video information
			$query = 'SELECT * FROM #__awd_wall_files WHERE wall_id = ' . (int)$row->id;
			$db->setQuery($query);
			$file = $db->loadObject();
			$message = $file->title . ' (file)';
		}
		
		$user 			= &JFactory::getUser($row->commenter_id);
		$userName = '';
		if($displayName == 1){
			$userName = $user->username;
		}else{
			$userName = $user->name;
		}
		if($row->type == 'group'){ 
			// get video information
			$query = 'SELECT * FROM #__awd_groups WHERE id = ' . (int)$row->group_id;
			$db->setQuery($query);
			$group = $db->loadObject();
			$message = JText::sprintf('JOIN GROUP NEWS FEED', $userName,  $group->title);
		}
		
		if($row->type == 'like'){
		$ruser = &JFactory::getUser($row->user_id);
		$rName = '';
		if($displayName == 1){
			$rName = $user->username;
		}else{
			$rName = $user->name;
		}
		$message = JText::sprintf('A LIKES B POST', $userName, JRoute::_('index.php?option=com_awdwall&view=awdwall&layout=mywall&wuid=' . $row->user_id, false), $rName);
		}
		
		if($row->type == 'friend'){ 
		$ruser = &JFactory::getUser($row->user_id);
		$rName = '';
		if($displayName == 1){
			$rName = $user->username;
		}else{
			$rName = $user->name;
		}
		$message = JText::sprintf('A AND B ARE FRIEND', $userName,  $rName);
	}
		
?>
		<tr class="<?php echo "row$k"; ?>">
			<td>
				<?php echo $this->pagination->getRowOffset( $i ); ?>
			</td>
			<td>
				<?php echo $checked; ?>
			</td>
			<td>
			<?php echo $message;?>
			</td>
		
			<td align="center">
			<?php
				$user =& JFactory::getUser($row->user_id);
				echo $user->username;
			?>
			</td>
			<!--
			<td align="center">
				<?php echo $published;?>
			</td>
			<td align="center">
				<?php echo $row->id; ?>
			</td>			
			-->
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
<tfoot>
		<td colspan="9">
			<?php echo $this->pagination->getListFooter(); ?>
		</td>
	</tfoot>
	</table>
</div>

<input type="hidden" name="controller" value="wall" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
</form>
</div>