<?php defined('_JEXEC') or die('Restricted access'); ?>
<h2><?php echo JText::_("FACTORY_ASSIGN_FIELDS_TO_SPECIFIC_POSITION");?> </h2>
<h3><?php echo JText::_("FACTORY_PAGE"),": ",$this->page;?> </h3>
<h3><?php echo JText::_("FACTORY_POSITION"),": ",$this->position;?> </h3>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<input type="hidden" name="option" value="<?php echo APP_EXTENSION;?>" />
<input type="hidden" name="task" value="positions.listfields" />
<input type="hidden" name="page" value="<?php echo $this->page;?>" />
<input type="hidden" name="position" value="<?php echo $this->position;?>" />
<table>
<tr>
<td>
    <?php echo JText::_("FACTORY_AVAILABLE_FIELDS");?>:<br />
    <?php echo $this->htmlfields_all;?>
</td>
<td width="40" align="center"> 
    <a href="#" id="add_fields"> >> </a> <br /> 
    <a href="#" id="remove_fields"> << </a> 
</td>
<td>
    <?php echo JText::_("FACTORY_ASSIGNED_FIELDS");?>:<br />
    <?php echo $this->htmlfields;?>
</td>
</tr>
</table>
</form>
