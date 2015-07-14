<?php
/**
 * @version 2.4
 * @package JomWALL -Joomla
 * @author   AWDsolution.com
 * @link http://www.AWDsolution.com
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @copyright Copyright (C) 2009 AWDsolution.com. All rights reserved.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
Awdwalladminhelper::awdadmintoolbar('config');
?>
<style>

.hasTip{

	background-color: #F6F6F6;

    border-bottom: 1px solid #E9E9E9;

    border-right: 1px solid #E9E9E9;

    color: #666666;

    font-weight: normal;

    text-align: left;

    width: 180px;

	float:left;

	padding:8px;

}

input, select {

	float: left;    

    margin: 8px 80% 5px 0px;

}


.link{

	float:left;

	width:100%;

}

.link a{

	float: left;

    text-indent: 10px;

}

#label-link{

	height: 14px;
}

</style>
<script type="text/javascript">
function uploadtheme()
{
document.adminForm.task.value='upload_theme';

return true;
}
</script>
<div class="awdm">
<form action="index.php" method="post" name="adminForm" enctype="multipart/form-data" style="min-height:100px;">
<input type="file" name="zip_file" width="150" /><br /><br /><input type="submit" name="uploadsubmit" value="<?php echo JText::_('Upload theme');?>" onclick="return uploadtheme();" style=" padding:3px; cursor:pointer; display:block;" />

	<input type="hidden" name="option" value="com_awdwall" />
	<input type="hidden" name="task" value="saveconfig" />		
	<input type="hidden" name="boxchecked" value="0" />
</form>
<?php 
// Awdwalladminhelper::awdfooter();
?>

</div>
