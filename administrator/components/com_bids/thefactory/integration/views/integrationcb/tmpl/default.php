<?php defined('_JEXEC') or die('Restricted access'); ?>
<h2><?php echo JText::_('FACTORY_CHOOSE_THE_FIELD_MAPPINGS');?></h2>

<form action="index.php" method="post" name="adminForm" >
  <input type="hidden" name="option" value="<?php echo APP_EXTENSION;?>">
  <input type="hidden" name="task" value="integrationcb.save">
<table>
<?php foreach($this->integrationFields as $k=>$field):?> 
<tr>
<td><?php echo defined(APP_PREFIX."_cbfield_".strtolower($field))?constant(APP_PREFIX."_cbfield_".strtolower($field)):$field;?>:
</td>
<td>
<?php    
    $selected = isset($this->integrationArray[$field])?($this->integrationArray[$field]) : null;
    $disabled= ($this->cb_detected)?"":"disabled";
	echo JHTML::_("select.genericlist",$this->cbfields,$field,"class='inputbox' $disabled",'value','text',$selected);
?>
</td>
</tr>
<?php endforeach;?>
</table>
</form>
