<?php defined('_JEXEC') or die('Restricted access'); ?>
<form name="adminForm" method="post" action="index.php">
<input type="hidden" name="option" value="<?php echo APP_EXTENSION;?>" />
<input type="hidden" name="task" value="category.doMoveCategories" />
<input type="hidden" name="cid" value="<?php echo $this->cid_list;?>" />

<table class="paramlist admintable" width='100%'>
<tr>
  <td class="paramlist_key" style="width:40% !important;" ><?php echo JText::_("FACTORY_MOVE_CATEGORIES");?>:</td>
  <td>
	  <?php foreach ($this->cats as $cat){ 
		  echo $cat->catname."<br />";
	  }?>
  </td>
</tr>
<tr>
  <td class="paramlist_key"><?php echo JText::_("FACTORY_TO_CATEGORY");?>:</td>
  <td>
	<?php echo $this->parent;?>
  </td>
</tr>
<tr>
  <td colspan="2" align="center" class="paramlist_key">
  &nbsp;
  </td>
</tr>
<tr>
  <td colspan="2" align="center">
	<a href="#" onclick="document.adminForm.submit();"><img src="<?php echo JURI::root();?>/administrator/images/move_f2.png" style="vertical-align:middle !important;" /><?php echo JText::_("FACTORY_MOVE");?></a>
  </td>
</tr>
</table>

</form>
