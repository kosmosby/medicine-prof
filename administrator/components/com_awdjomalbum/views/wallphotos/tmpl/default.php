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
JToolBarHelper::title(JText::_('awdjomalbum'), 'awdwallprogallerywallphoto');
JToolBarHelper::deleteList('Delete','deletewallphotos','Delete'); // Will call the task/function "remove" in your controller

AwdjomalbumHelper::awdadmintoolbar('wallphotos');
?>
<div class="awdm">
<form name="adminForm" id="adminForm" action="" method="post" >

	<table class="adminlist" cellpadding="0" cellspacing="0">
		<tbody>
			<tr>
				<td align="left"><?php echo $this->lists['userlist'];?></td>
			</tr>
		</tbody>		
		<tbody>
			<tr>
				<td>
		<?php
			$k = 0;
			$db				=& JFactory::getDBO();
			for ($i=0, $n=count( $this->photorows ); $i < $n; $i++)
			{
				$row 	=& $this->photorows[$i];
//				print_r($row);
//				exit;
				$link 	='index.php?option=com_awdjomalbum&amp;view=wallphotos&amp;layout=wallcomments&amp;photoid='. $row->id. '&wallid='.$row->wall_id.'';
				$userid=$row->commenter_id ;
			?>
				<div align="center" style="width:170px; height:150px; float:left;">		
				
					<?php 
					$path=JURI::base();			
					$path=substr(" $path", 0, -14); 
					//$imgpath=$path."images/awd_photo/awd_thumb_photo/".$row->path;
					$imgpath=$path."images/".$userid."/original/".$row->path;
			//	echo $imgpath;?>
					<table align="center">
					<tr>
					<td>
					<?php echo JHTML::_('grid.id', $i, $row->id ); ?>
					<br /><br />
					<?php
					echo '<a href="javascript:void(0);" onclick="return listItemTask(\'cb'.$i.'\',\'deletewallphotos\')" title="Delete Image">
		<img src="components/com_awdjomalbum/images/delete.png"  border="0"></a>';
					?>
					
					</td>
					
					<td colspan="2" align="center">
					<img src="<?php echo $imgpath; ?>"  border="0" align="absmiddle"  width="120" height="100"/>
					<a href="<?php echo $link;?>">View Comments</a>
					</td></tr>
					</table>
				</div>
			<?php
				$k = 1 - $k;
				}
			?>
			</td>
				
			</tr>
		</tbody>
		<tbody>
        <tr>
          <td width="100%" colspan="12"><?php echo $this->pageNav->getListFooter('pages'); ?> </td></tr>
      </tbody>
	</table>
	<input type="hidden" name="option" value="com_awdjomalbum" />
	<input type="hidden" name="view" value="wallphotos" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="imgid" value="<?php echo $row->id;?>" />
	<input type="hidden" name="boxchecked" value="0" />
</form>
</div>
